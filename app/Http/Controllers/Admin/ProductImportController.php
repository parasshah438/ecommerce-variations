<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ParseAndValidateImportJob;
use App\Jobs\ProcessProductImportJob;
use App\Models\ProductImportBatch;
use App\Services\ProductImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImportController extends Controller
{
    private const BATCH_SIZE = 500;
    private const CHUNK_SIZE = 50;

    public function showImportForm()
    {
        $recentBatches = ProductImportBatch::orderBy('created_at', 'desc')->take(10)->get();

        return view('admin.products.import', compact('recentBatches'));
    }

    public function preview(Request $request, ProductImportService $service)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:20480', // 20MB
        ]);

        try {
            $file = $request->file('import_file');

            $storagePath = $file->store('product-imports', 'local');

            $parsed = $service->readFile($storagePath);
            $headers = $parsed['headers'];
            $rows = $parsed['rows'];

            $missing = array_diff(ProductImportService::REQUIRED_COLUMNS, $headers);

            if (!empty($missing)) {
                if (Storage::disk('local')->exists($storagePath)) {
                    Storage::disk('local')->delete($storagePath);
                }
                return back()
                    ->withInput()
                    ->withErrors(['import_file' => 'File is missing required columns: ' . implode(', ', $missing) . '. Your file headers must include: name, description, price, weight.']);
            }

            if (empty($rows)) {
                if (Storage::disk('local')->exists($storagePath)) {
                    Storage::disk('local')->delete($storagePath);
                }
                return back()->withInput()->withErrors(['import_file' => 'The file contains no data rows.']);
            }

            // Validate rows for preview
            $previewRows = [];
            $invalidCount = 0;

            foreach ($rows as $index => $row) {
                $rowNumber = $row['_row'] ?? ($index + 2);
                $rowErrors = $service->validateRow($row);

                $previewRows[] = [
                    'number' => $rowNumber,
                    'name' => $row['name'] ?? '',
                    'price' => $row['price'] ?? '',
                    'weight' => $row['weight'] ?? '',
                    'category' => $row['category_name'] ?? $row['category'] ?? '',
                    'brand' => $row['brand_name'] ?? $row['brand'] ?? '',
                    'variations' => $row['variations'] ?? '',
                    'errors' => $rowErrors,
                ];

                if (!empty($rowErrors)) {
                    $invalidCount++;
                }
            }

            return view('admin.products.import-preview', [
                'storagePath' => $storagePath,
                'filename' => $file->getClientOriginalName(),
                'totalRows' => count($rows),
                'validRows' => count($rows) - $invalidCount,
                'invalidRows' => $invalidCount,
                'previewRows' => array_slice($previewRows, 0, 200),
                'allHeaders' => $headers,
                'headers' => $this->getDisplayHeaders($headers),
            ]);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['import_file' => 'Failed to parse file: ' . $e->getMessage()]);
        }
    }

    public function import(Request $request, ProductImportService $service)
    {
        $request->validate([
            'storage_path' => 'required|string',
        ]);

        $storagePath = $request->input('storage_path');

        if (!Storage::disk('local')->exists($storagePath)) {
            return back()->withErrors(['error' => 'The uploaded file no longer exists. Please upload again.']);
        }

        try {
            $parsed = $service->readFile($storagePath);
            $rows = $parsed['rows'];

            // Write segmented CSV files (500 rows per batch) so each
            // ParseAndValidateImportJob only reads its own segment. All rows are
            // written — the validation job records per-row errors and only
            // dispatches valid rows to ProcessProductImportJob.
            $segments = array_chunk($rows, self::BATCH_SIZE);
            $segmentPaths = [];
            $headers = $parsed['headers'];

            foreach ($segments as $segmentIndex => $segmentRows) {
                $segmentPath = 'product-imports/segments/' . Str::uuid() . '.csv';
                $this->writeSegmentCsv($segmentPath, $headers, $segmentRows);
                $segmentPaths[] = $segmentPath;
            }

            // Create one batch record per 500-row segment and dispatch
            // ParseAndValidateImportJob, which validates, records errors,
            // then fans out ProcessProductImportJob in 50-row chunks.
            $totalQueued = 0;
            foreach ($segments as $segmentIndex => $segmentRows) {
                $batch = ProductImportBatch::create([
                    'batch_uuid' => (string) Str::uuid(),
                    'original_filename' => $request->input('filename') ?? basename($storagePath),
                    'file_path' => $segmentPaths[$segmentIndex],
                    'status' => ProductImportBatch::STATUS_UPLOADED,
                    'total_rows' => count($segmentRows),
                    'valid_rows' => 0,
                    'invalid_rows' => 0,
                    'processed_rows' => 0,
                    'failed_rows' => 0,
                    'created_by' => Auth::id(),
                ]);

                ParseAndValidateImportJob::dispatch($batch->id);
                $totalQueued += count($segmentRows);
            }

            return redirect()
                ->route('admin.products.import.form')
                ->with('success', "Import queued! {$totalQueued} rows in " . count($segments) . " batch(es) of up to " . self::BATCH_SIZE . " rows. Validation runs first — invalid rows are skipped and recorded for download. Refresh this page to check progress.");
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Failed to start import: ' . $e->getMessage()]);
        }
    }

    /**
     * Write a segmented CSV file to local storage containing only the given rows.
     */
    private function writeSegmentCsv(string $segmentPath, array $headers, array $rows): void
    {
        $fullPath = Storage::disk('local')->path($segmentPath);
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $handle = fopen($fullPath, 'w');
        if ($handle === false) {
            throw new \RuntimeException("Unable to create segment file: {$segmentPath}");
        }

        // Write header row (skip internal _row key)
        $headerRow = array_values(array_filter($headers, fn($h) => $h !== '_row'));
        fputcsv($handle, $headerRow);

        foreach ($rows as $row) {
            $csvRow = [];
            foreach ($headerRow as $column) {
                $csvRow[] = $row[$column] ?? '';
            }
            fputcsv($handle, $csvRow);
        }

        fclose($handle);
    }

    /**
     * Download a ready-to-use sample file (CSV, TSV, XLSX or XLS).
     * Includes examples of a simple product, a product with variations,
     * a minimal row, and one intentionally invalid row to demonstrate
     * the error report.
     */
    public function downloadSample(string $format)
    {
        $format = strtolower($format);
        if (!in_array($format, ['csv', 'txt', 'xlsx', 'xls'], true)) {
            abort(404);
        }

        [$headers, $rows] = $this->sampleData();

        if ($format === 'xlsx' || $format === 'xls') {
            return $this->downloadSpreadsheetSample($format, $headers, $rows);
        }

        $delimiter = $format === 'txt' ? "\t" : ',';
        $extension = $format === 'txt' ? 'txt' : 'csv';

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers, $delimiter);
        foreach ($rows as $row) {
            fputcsv($handle, $row, $delimiter);
        }
        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        return ResponseFacade::make($contents, 200, [
            'Content-Type' => $format === 'txt' ? 'text/tab-separated-values' : 'text/csv',
            'Content-Disposition' => 'attachment; filename="product-import-sample.' . $extension . '"',
        ]);
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, array<int, string>>}
     */
    private function sampleData(): array
    {
        $headers = [
            'name', 'description', 'price', 'weight', 'mrp', 'sku', 'stock',
            'category', 'brand', 'short_description', 'long_description',
            'length', 'width', 'height', 'variations', 'variation_prices',
            'variation_stock', 'variation_sku', 'variation_weight',
            'image_urls', 'cover_image', 'hsn_code', 'featured', 'active',
            'meta_title', 'meta_description', 'meta_keywords', 'video_url',
            'country_of_origin', 'manufacturer',
        ];

        $rows = [
            // 1) Simple product with most optional fields filled
            [
                "Men's Cotton T-Shirt",
                'Premium 100% combed cotton t-shirt with a comfortable regular fit.',
                '999', '200', '1299', 'TS-BLU-M-001', '50',
                'Fashion', 'Nike', 'Soft breathable cotton tee',
                'Made from premium combed cotton. Machine wash cold, do not bleach.',
                '30', '20', '2', '', '', '', '', '',
                '', '', '61091000', 'yes', 'yes',
                "Men's Cotton T-Shirt - Shop Online",
                'Buy premium cotton t-shirt online at the best price.',
                'cotton tshirt, men tshirt, tees', '',
                'India', 'NS Kurti Pvt Ltd',
            ],
            // 2) Product with variations
            [
                "Women's Printed Kurti",
                'Stylish printed kurti with elegant ethnic design.',
                '649', '250', '999', '', '',
                'Fashion', '', 'Trendy printed kurti',
                'Lightweight fabric. Shrink-resistant. See care label inside.',
                '28', '20', '2', 'Color:Red,Size:M;Color:Blue,Size:L',
                '699;749', '10;15', 'KUR-RED-M;KUR-BLU-L', '250;260',
                '', '', '61044200', 'no', 'yes',
                "Women's Printed Kurti - Buy Online", '', '', '',
                'India', '',
            ],
            // 3) Minimal row (only required columns)
            [
                'Denim Jacket',
                'Classic blue denim jacket for all seasons.',
                '2499', '800', '2999', '', '',
                '', '', '', '', '', '', '', '', '', '', '', '',
                '', '', '', '', '',
                '', '', '', '', '', '',
            ],
            // 4) Intentionally invalid row (no name, bad price, weight < 1)
            //    Import this file as-is to see it flagged in preview + error report.
            [
                '', '', 'free', '0', '', '', '',
                '', '', '', '', '', '', '', '', '', '', '', '',
                '', '', '', '', '',
                '', '', '', '', '', '',
            ],
        ];

        return [$headers, $rows];
    }

    private function downloadSpreadsheetSample(string $format, array $headers, array $rows)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($rows, null, 'A2');
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(45);

        if ($format === 'xls') {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
            $contentType = 'application/vnd.ms-excel';
            $filename = 'product-import-sample.xls';
        } else {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            $filename = 'product-import-sample.xlsx';
        }

        ob_start();
        $writer->save('php://output');
        $contents = ob_get_clean();

        return ResponseFacade::make($contents, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function downloadErrors(ProductImportBatch $batch)
    {
        $errors = [];

        if (!empty($batch->validation_errors)) {
            foreach ($batch->validation_errors as $error) {
                $errors[] = [
                    'row' => $error['row'] ?? '',
                    'type' => 'Validation',
                    'message' => $error['message'] ?? '',
                ];
            }
        }

        if (!empty($batch->import_errors)) {
            foreach ($batch->import_errors as $error) {
                $errors[] = [
                    'row' => $error['row'] ?? '',
                    'type' => 'Import',
                    'message' => $error['message'] ?? '',
                ];
            }
        }

        if (empty($errors)) {
            return back()->with('info', 'No errors recorded for this batch.');
        }

        $csv = "Row,Type,Message\n";
        foreach ($errors as $error) {
            $row = str_replace('"', '""', (string) $error['row']);
            $type = str_replace('"', '""', $error['type']);
            $message = str_replace('"', '""', $error['message']);
            $csv .= "\"{$row}\",\"{$type}\",\"{$message}\"\n";
        }

        $filename = 'product-import-errors-' . $batch->id . '-' . date('Ymd-His') . '.csv';

        return ResponseFacade::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function getDisplayHeaders(array $headers): array
    {
        return array_values(array_filter($headers, fn($h) => $h !== '_row'));
    }
}
