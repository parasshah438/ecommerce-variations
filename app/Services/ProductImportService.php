<?php

namespace App\Services;

use App\Models\ProductImportBatch;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Shared parsing + normalization logic for CSV/Excel product imports.
 * Used by both ProductImportController (preview) and the queued import jobs.
 */
class ProductImportService
{
    /** Columns we accept in the CSV header (lowercase, used as canonical keys). */
    public const REQUIRED_COLUMNS = ['name', 'description', 'price', 'weight'];

    public const HEADER_ALIASES = [
        'product name' => 'name',
        'productname' => 'name',
        'title' => 'name',
        'product title' => 'name',
        'description' => 'description',
        'product description' => 'description',
        'short description' => 'short_description',
        'shortdescription' => 'short_description',
        'long description' => 'long_description',
        'longdescription' => 'long_description',
        'country of origin' => 'country_of_origin',
        'manufacturer' => 'manufacturer',
        'meta keywords' => 'meta_keywords',
        'keywords' => 'meta_keywords',
        'category' => 'category_name',
        'category name' => 'category_name',
        'brand' => 'brand_name',
        'brand name' => 'brand_name',
        'price' => 'price',
        'selling price' => 'price',
        'mrp' => 'mrp',
        'maximum retail price' => 'mrp',
        'original price' => 'mrp',
        'weight' => 'weight',
        'weight (g)' => 'weight',
        'weight g' => 'weight',
        'grams' => 'weight',
        'length' => 'length',
        'width' => 'width',
        'height' => 'height',
        'sku' => 'sku',
        'stock' => 'stock_quantity',
        'stock quantity' => 'stock_quantity',
        'quantity' => 'stock_quantity',
        'hsn code' => 'hsn_code',
        'hsn' => 'hsn_code',
        'featured' => 'is_featured',
        'is featured' => 'is_featured',
        'status' => 'active',
        'active' => 'active',
        'variations' => 'variations',
        'variation attributes' => 'variations',
        'variation prices' => 'variation_prices',
        'variation stock' => 'variation_stock',
        'variation sku' => 'variation_sku',
        'variation weight' => 'variation_weight',
        'image urls' => 'image_urls',
        'images' => 'image_urls',
        'image url' => 'image_urls',
        'cover image' => 'cover_image_url',
        'cover image url' => 'cover_image_url',
        'tags' => 'tags',
        'video url' => 'video_url',
        'meta title' => 'meta_title',
        'meta description' => 'meta_description',
        'seo title' => 'meta_title',
        'seo description' => 'meta_description',
    ];

    /**
     * Read a CSV/XLSX file from the given storage path and return
     * an associative array of rows keyed by normalized column names.
     *
     * Supports .csv, .xlsx (via PhpSpreadsheet if installed), and .tsv.
     *
     * @return array{headers: string[], rows: array<int, array<string, mixed>>}
     */
    public function readFile(string $storagePath): array
    {
        $fullPath = Storage::disk('local')->path($storagePath);
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        if ($extension === 'xlsx' || $extension === 'xls') {
            return $this->readSpreadsheet($fullPath, $extension);
        }

        return $this->readCsv($fullPath, $this->detectDelimiter($fullPath));
    }

    /**
     * Detect whether a delimited-text file uses tabs or commas.
     * Counts separators on the first non-empty line. Falls back to comma.
     */
    protected function detectDelimiter(string $fullPath): string
    {
        $handle = fopen($fullPath, 'r');
        if ($handle === false) {
            return ',';
        }

        $firstLine = '';
        while (($line = fgets($handle)) !== false) {
            $line = ltrim($line, "\xEF\xBB\xBF");
            if (trim($line) !== '') {
                $firstLine = $line;
                break;
            }
        }
        fclose($handle);

        if ($firstLine === '') {
            return ',';
        }

        $tabs = substr_count($firstLine, "\t");
        $commas = substr_count($firstLine, ',');

        return $tabs > $commas ? "\t" : ',';
    }

    /**
     * Parse an uploaded CSV/TSV file.
     */
    protected function readCsv(string $fullPath, string $delimiter): array
    {
        $handle = fopen($fullPath, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Unable to open file: {$fullPath}");
        }

        $headers = [];
        $rows = [];
        $rowNumber = 0;

        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            // Trim BOM from the first header cell
            if ($rowNumber === 0) {
                if (!empty($line)) {
                    $line[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) ($line[0] ?? ''));
                }
                $headers = $this->normalizeHeaders($line);
                $rowNumber++;
                continue;
            }

            // Skip completely empty rows
            if (count(array_filter($line, fn($v) => trim((string) $v) !== '')) === 0) {
                $rowNumber++;
                continue;
            }

            $rows[] = $this->mapRowToHeaders($headers, $line, $rowNumber);
            $rowNumber++;
        }

        fclose($handle);

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * Parse an Excel file using PhpSpreadsheet (workbook reader + SimpleXLSX fallback).
     */
    protected function readSpreadsheet(string $fullPath, string $extension): array
    {
        if (class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullPath);
            $worksheet = $spreadsheet->getActiveSheet();
            $allRows = $worksheet->toArray(null, true, true, false);

            return $this->rowsFromMatrix($allRows);
        }

        if ($extension === 'xlsx' && class_exists(\Shuchkin\SimpleXLSX::class)) {
            $xlsx = \Shuchkin\SimpleXLSX::parse($fullPath);
            if ($xlsx === false) {
                throw new \RuntimeException('Unable to parse XLSX file.');
            }
            return $this->rowsFromMatrix($xlsx->rows());
        }

        throw new \RuntimeException(
            'Excel support requires phpoffice/phpspreadsheet. Please run: composer require phpoffice/phpspreadsheet'
        );
    }

    protected function rowsFromMatrix(array $matrix): array
    {
        if (empty($matrix)) {
            return ['headers' => [], 'rows' => []];
        }

        $headers = $this->normalizeHeaders(array_shift($matrix));
        $rows = [];
        $rowNumber = 1;

        foreach ($matrix as $line) {
            if (count(array_filter($line, fn($v) => trim((string) $v) !== '')) === 0) {
                $rowNumber++;
                continue;
            }
            $rows[] = $this->mapRowToHeaders($headers, $line, $rowNumber);
            $rowNumber++;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    protected function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header) {
            $key = strtolower(trim((string) $header));
            return self::HEADER_ALIASES[$key] ?? Str::slug($key, '_');
        }, $headers);
    }

    protected function mapRowToHeaders(array $headers, array $line, int $rowNumber): array
    {
        $mapped = ['_row' => $rowNumber];

        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }
            $mapped[$header] = $line[$index] ?? null;
        }

        return $mapped;
    }

    /**
     * Validate a single parsed row.
     *
     * @return string[] list of error messages (empty = valid)
     */
    public function validateRow(array $row): array
    {
        $errors = [];

        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '') {
            $errors[] = 'Product name is required.';
        } elseif (mb_strlen($name) > 255) {
            $errors[] = 'Product name must not exceed 255 characters.';
        }

        $description = trim((string) ($row['description'] ?? ''));
        if ($description === '') {
            $errors[] = 'Description is required.';
        }

        $price = $row['price'] ?? null;
        if ($price === null || trim((string) $price) === '' || !is_numeric($price) || (float) $price < 0) {
            $errors[] = 'Price must be a valid non-negative number.';
        }

        $weight = $row['weight'] ?? null;
        if ($weight === null || trim((string) $weight) === '' || !is_numeric($weight) || (float) $weight < 1) {
            $errors[] = 'Weight must be a valid number >= 1 (grams).';
        }

        if (!empty($row['mrp']) && !is_numeric($row['mrp'])) {
            $errors[] = 'MRP must be a number.';
        }

        foreach (['length', 'width', 'height'] as $dim) {
            if (!empty($row[$dim]) && !is_numeric($row[$dim])) {
                $errors[] = ucfirst($dim) . ' must be a number (cm).';
            }
        }

        if (!empty($row['stock_quantity']) && (!is_numeric($row['stock_quantity']) || (int) $row['stock_quantity'] < 0)) {
            $errors[] = 'Stock must be a non-negative integer.';
        }

        if (!empty($row['variations'])) {
            $variationErrors = $this->validateVariationString((string) $row['variations']);
            if (!empty($variationErrors)) {
                $errors = array_merge($errors, $variationErrors);
            }
        }

        return $errors;
    }

    /**
     * Validate the "variations" string format.
     *
     * Accepted syntaxes (must match parseVariationGroups):
     *   Color:Red,Size:S;Color:Blue,Size:M   (semicolon = variation, comma = attribute pair)
     *   Color:Red|Size:XL                    (pipe-separated single variation)
     *
     * @return string[]
     */
    public function validateVariationString(string $variations): array
    {
        $errors = [];
        $hasContent = false;

        // Split into variation groups by ';'
        foreach (explode(';', $variations) as $groupString) {
            $groupString = trim($groupString);
            if ($groupString === '') {
                continue;
            }
            $hasContent = true;

            // Within a group, pairs are separated by ',' or '|'
            $separator = str_contains($groupString, ',') ? ',' : '|';
            $pairs = explode($separator, $groupString);

            foreach ($pairs as $pair) {
                $pair = trim($pair);
                if ($pair === '') {
                    continue;
                }
                if (!str_contains($pair, ':')) {
                    $errors[] = "Invalid variation segment '{$pair}'. Expected format: Attribute:Value (e.g. Color:Red).";
                }
            }
        }

        if (!$hasContent) {
            $errors[] = 'Variations field is empty.';
        }

        return $errors;
    }

    /**
     * Build a normalized array ready for Product::create() from a validated row.
     */
    public function buildProductData(array $row): array
    {
        $price = (float) $row['price'];
        $mrp = !empty($row['mrp']) ? (float) $row['mrp'] : $price;

        // Default to active; only an explicit "no/false/0" disables the product.
        $activeValue = trim((string) ($row['active'] ?? ''));
        $active = $activeValue === '' || !in_array(strtolower($activeValue), ['0', 'false', 'no', 'inactive', 'n'], true);

        $featuredValue = trim((string) ($row['is_featured'] ?? ''));
        $isFeatured = $featuredValue !== '' && in_array(strtolower($featuredValue), ['1', 'true', 'yes', 'y'], true);

        return [
            'name' => trim((string) $row['name']),
            'slug' => Str::slug(trim((string) $row['name'])) . '-' . Str::random(8),
            'description' => trim((string) $row['description']),
            'short_description' => !empty($row['short_description']) ? trim((string) $row['short_description']) : null,
            'long_description' => !empty($row['long_description']) ? trim((string) $row['long_description']) : null,
            'hsn_code' => !empty($row['hsn_code']) ? trim((string) $row['hsn_code']) : null,
            'is_featured' => $isFeatured,
            'category_id' => $row['category_id'] ?? null,
            'brand_id' => $row['brand_id'] ?? null,
            'price' => $price,
            'mrp' => $mrp,
            'weight' => (float) $row['weight'],
            'length' => !empty($row['length']) ? (float) $row['length'] : null,
            'width' => !empty($row['width']) ? (float) $row['width'] : null,
            'height' => !empty($row['height']) ? (float) $row['height'] : null,
            'active' => $active,
            'meta_title' => !empty($row['meta_title']) ? trim((string) $row['meta_title']) : null,
            'meta_description' => !empty($row['meta_description']) ? trim((string) $row['meta_description']) : null,
            'meta_keywords' => !empty($row['meta_keywords']) ? trim((string) $row['meta_keywords']) : null,
            'video_url' => !empty($row['video_url']) ? trim((string) $row['video_url']) : null,
            'country_of_origin' => !empty($row['country_of_origin']) ? trim((string) $row['country_of_origin']) : null,
            'manufacturer' => !empty($row['manufacturer']) ? trim((string) $row['manufacturer']) : null,
        ];
    }

    /**
     * Determine whether a validated row declares variation data.
     */
    public function hasVariations(array $row): bool
    {
        $variations = trim((string) ($row['variations'] ?? ''));
        return $variations !== '';
    }

    /**
     * Parse the "variations" column into structured combination groups.
     *
     * Format:
     *   Color:Red,Size:S;Color:Blue,Size:M
     *   => [
     *        ['Color' => 'Red', 'Size' => 'S'],
     *        ['Color' => 'Blue', 'Size' => 'M'],
     *      ]
     *
     * @return array<int, array<string, string>>
     */
    public function parseVariationGroups(array $row): array
    {
        $raw = trim((string) ($row['variations'] ?? ''));
        if ($raw === '') {
            return [];
        }

        $groups = [];
        foreach (explode(';', $raw) as $groupString) {
            $groupString = trim($groupString);
            if ($groupString === '') {
                continue;
            }

            $attributes = [];
            foreach (explode(',', $groupString) as $pairString) {
                $pairString = trim($pairString);
                if ($pairString === '' || !str_contains($pairString, ':')) {
                    continue;
                }
                [$attribute, $value] = explode(':', $pairString, 2);
                $attributes[trim($attribute)] = trim($value);
            }

            if (!empty($attributes)) {
                $groups[] = $attributes;
            }
        }

        // Fallback: allow single-combination syntax without explicit grouping
        if (empty($groups) && str_contains($raw, '|')) {
            $attributes = [];
            foreach (explode('|', $raw) as $pairString) {
                $pairString = trim($pairString);
                if ($pairString === '' || !str_contains($pairString, ':')) {
                    continue;
                }
                [$attribute, $value] = explode(':', $pairString, 2);
                $attributes[trim($attribute)] = trim($value);
            }
            if (!empty($attributes)) {
                $groups[] = $attributes;
            }
        }

        return $groups;
    }

    /**
     * Parse a semicolon-separated list (e.g. variation_prices, variation_stock)
     * into an indexed array.
     */
    public function parseSemicolonList(array $row, string $key): array
    {
        $raw = trim((string) ($row[$key] ?? ''));
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            fn($item) => trim((string) $item),
            explode(';', $raw)
        ), fn($item) => $item !== ''));
    }
}
