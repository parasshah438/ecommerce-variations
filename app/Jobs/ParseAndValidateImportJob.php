<?php

namespace App\Jobs;

use App\Models\ProductImportBatch;
use App\Services\ProductImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Reads the uploaded file, validates every row, stores per-row errors,
 * and dispatches ProcessProductImportJob chunks (50 rows each) for the
 * valid rows. Invalid rows are skipped and recorded for download.
 */
class ParseAndValidateImportJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;
    public int $tries = 1;

    public const CHUNK_SIZE = 50;

    public function __construct(public int $batchId)
    {
    }

    public function handle(ProductImportService $service): void
    {
        $batch = ProductImportBatch::find($this->batchId);
        if (!$batch) {
            Log::error('Product import batch not found during validation.', ['batch_id' => $this->batchId]);
            return;
        }

        $batch->update(['status' => ProductImportBatch::STATUS_VALIDATING]);

        try {
            $parsed = $service->readFile($batch->file_path);
            $rows = $parsed['rows'];

            // Verify required columns exist
            $headers = $parsed['headers'];
            $missing = array_diff(ProductImportService::REQUIRED_COLUMNS, $headers);
            if (!empty($missing)) {
                $batch->update([
                    'status' => ProductImportBatch::STATUS_FAILED,
                    'validation_errors' => [[
                        'row' => 'HEADER',
                        'message' => 'Missing required columns: ' . implode(', ', $missing),
                    ]],
                    'total_rows' => count($rows),
                ]);
                return;
            }

            $validationErrors = [];
            $validRows = [];

            foreach ($rows as $index => $row) {
                $rowNumber = $row['_row'] ?? ($index + 2);
                $errors = $service->validateRow($row);

                if (!empty($errors)) {
                    foreach ($errors as $error) {
                        $validationErrors[] = [
                            'row' => $rowNumber,
                            'message' => $error,
                        ];
                    }
                } else {
                    $validRows[] = $row;
                }
            }

            $batch->update([
                'status' => ProductImportBatch::STATUS_IMPORTING,
                // total_rows tracks the rows that will actually be processed
                // (valid rows). processed_rows in the process job counts these.
                'total_rows' => count($validRows),
                'valid_rows' => count($validRows),
                'invalid_rows' => count($rows) - count($validRows),
                'validation_errors' => $validationErrors,
            ]);

            Log::info('Product import validation complete.', [
                'batch_id' => $batch->id,
                'total' => count($rows),
                'valid' => count($validRows),
                'errors' => count($validationErrors),
            ]);

            // Dispatch import jobs in chunks of 50 for the valid rows
            if (!empty($validRows)) {
                foreach (array_chunk($validRows, self::CHUNK_SIZE) as $chunk) {
                    ProcessProductImportJob::dispatch($batch->id, $chunk);
                }

                Log::info('Product import processing dispatched.', [
                    'batch_id' => $batch->id,
                    'jobs' => (int) ceil(count($validRows) / self::CHUNK_SIZE),
                    'rows' => count($validRows),
                ]);
            } else {
                $batch->update(['status' => ProductImportBatch::STATUS_COMPLETED]);
            }
        } catch (\Throwable $e) {
            Log::error('Product import validation failed.', [
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);

            $batch->update([
                'status' => ProductImportBatch::STATUS_FAILED,
                'validation_errors' => [[
                    'row' => 'FILE',
                    'message' => 'Failed to parse file: ' . $e->getMessage(),
                ]],
            ]);
        }
    }
}
