<?php

namespace App\Jobs;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductImportBatch;
use App\Models\ProductVariation;
use App\Models\VariationStock;
use App\Services\ProductImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Imports one validated CSV row into the products tables.
 * Dispatched in chunks (50 rows per job) by the controller.
 */
class ProcessProductImportJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;
    public int $tries = 1;

    /**
     * @param int $batchId ProductImportBatch id
     * @param array<int, array<string, mixed>> $rows Validated rows to import (max 50)
     */
    public function __construct(public int $batchId, public array $rows)
    {
    }

    public function handle(ProductImportService $service): void
    {
        $batch = ProductImportBatch::find($this->batchId);
        if (!$batch) {
            Log::error('Product import batch not found during processing.', ['batch_id' => $this->batchId]);
            return;
        }

        // Cache lookups for the whole chunk to avoid repeated SQL
        $categoryCache = [];
        $brandCache = [];
        $attributeCache = [];
        $affectedCategoryIds = [];

        foreach ($this->rows as $row) {
            $rowNumber = $row['_row'] ?? 0;

            try {
                DB::beginTransaction();

                // 1. Resolve category (by name) and create if missing? -> match only, else null
                $categoryId = $this->resolveCategoryId($row, $categoryCache);
                if ($categoryId !== null) {
                    $affectedCategoryIds[$categoryId] = true;
                }

                // 2. Resolve brand (by name) and create if missing? -> match only, else null
                $brandId = $this->resolveBrandId($row, $brandCache);

                // 3. Create product
                $productData = $service->buildProductData($row);
                $productData['category_id'] = $categoryId;
                $productData['brand_id'] = $brandId;

                $product = Product::create($productData);

                // 4. Handle cover image URL (optional)
                if (!empty($row['cover_image_url'])) {
                    $coverPath = $this->downloadImage($row['cover_image_url'], 'products/covers');
                    if ($coverPath) {
                        $product->update(['cover_image' => $coverPath]);
                    }
                }

                // 5. Handle gallery image URLs (optional)
                $this->attachGalleryImages($product, $row);

                // 6. Handle variations
                $hasVariations = $service->hasVariations($row);
                if ($hasVariations) {
                    $this->createVariations($product, $row, $service, $attributeCache);
                } else {
                    $this->createSimpleVariation($product, $row, $service);
                }

                DB::commit();

                $batch->increment('processed_rows');
            } catch (\Throwable $e) {
                DB::rollBack();

                $batch->addImportError($rowNumber, $e->getMessage());
                $batch->increment('failed_rows');
                $batch->increment('processed_rows');

                Log::error('Failed to import product row.', [
                    'batch_id' => $this->batchId,
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Clear cache once per chunk
        Cache::forget('featured_products');
        Cache::forget('latest_products');
        foreach (array_keys($affectedCategoryIds) as $categoryId) {
            Cache::forget('category_products_' . $categoryId);
        }

        // Mark complete if everything processed
        if ($batch->fresh()->processed_rows >= $batch->total_rows) {
            $batch->update(['status' => ProductImportBatch::STATUS_COMPLETED]);
        }
    }

    private function resolveCategoryId(array $row, array &$categoryCache): ?int
    {
        $categoryName = trim((string) ($row['category_name'] ?? $row['category'] ?? ''));
        if ($categoryName === '') {
            return null;
        }

        if (isset($categoryCache[$categoryName])) {
            return $categoryCache[$categoryName];
        }

        $category = Category::where('name', $categoryName)->orWhere('slug', \Illuminate\Support\Str::slug($categoryName))->first();
        $categoryCache[$categoryName] = $category?->id;

        return $categoryCache[$categoryName];
    }

    private function resolveBrandId(array $row, array &$brandCache): ?int
    {
        $brandName = trim((string) ($row['brand_name'] ?? $row['brand'] ?? ''));
        if ($brandName === '') {
            return null;
        }

        if (isset($brandCache[$brandName])) {
            return $brandCache[$brandName];
        }

        $brand = Brand::where('name', $brandName)->orWhere('slug', \Illuminate\Support\Str::slug($brandName))->first();
        $brandCache[$brandName] = $brand?->id;

        return $brandCache[$brandName];
    }

    private function createSimpleVariation(Product $product, array $row, ProductImportService $service): void
    {
        $sku = trim((string) ($row['sku'] ?? ''));
        if ($sku === '') {
            $sku = $this->generateSimpleSku($product);
        }

        $stockQuantity = !empty($row['stock_quantity']) ? (int) $row['stock_quantity'] : 0;

        $variation = ProductVariation::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'price' => $product->price,
            'weight' => $product->weight,
            'length' => $product->length,
            'width' => $product->width,
            'height' => $product->height,
            'min_qty' => 1,
            'attribute_value_ids' => [],
        ]);

        VariationStock::create([
            'product_variation_id' => $variation->id,
            'quantity' => $stockQuantity,
            'in_stock' => $stockQuantity > 0,
        ]);
    }

    private function createVariations(Product $product, array $row, ProductImportService $service, array &$attributeCache): void
    {
        $groups = $service->parseVariationGroups($row);
        if (empty($groups)) {
            $this->createSimpleVariation($product, $row, $service);
            return;
        }

        $variationPrices = $service->parseSemicolonList($row, 'variation_prices');
        $variationStocks = $service->parseSemicolonList($row, 'variation_stock');
        $variationSkus = $service->parseSemicolonList($row, 'variation_sku');
        $variationWeights = $service->parseSemicolonList($row, 'variation_weight');

        foreach ($groups as $index => $attributes) {
            $attributeValueIds = [];

            foreach ($attributes as $attributeName => $attributeValue) {
                $attributeId = $this->resolveAttributeId($attributeName, $attributeCache);
                if ($attributeId === null) {
                    continue;
                }

                $valueId = $this->resolveAttributeValueId($attributeId, $attributeValue, $attributeCache);
                if ($valueId !== null) {
                    $attributeValueIds[] = $valueId;
                }
            }

            if (empty($attributeValueIds)) {
                continue;
            }

            $sku = $variationSkus[$index] ?? $this->generateSku($product, $attributeValueIds, (string) ($row['sku'] ?? ''));
            $price = isset($variationPrices[$index]) && is_numeric($variationPrices[$index])
                ? (float) $variationPrices[$index]
                : $product->price;
            $weight = isset($variationWeights[$index]) && is_numeric($variationWeights[$index])
                ? (float) $variationWeights[$index]
                : $product->weight;
            $stock = isset($variationStocks[$index]) && is_numeric($variationStocks[$index])
                ? (int) $variationStocks[$index]
                : 0;

            $variation = ProductVariation::create([
                'product_id' => $product->id,
                'sku' => $sku,
                'price' => $price,
                'weight' => $weight,
                'length' => $product->length,
                'width' => $product->width,
                'height' => $product->height,
                'min_qty' => 1,
                'attribute_value_ids' => $attributeValueIds,
            ]);

            VariationStock::create([
                'product_variation_id' => $variation->id,
                'quantity' => $stock,
                'in_stock' => $stock > 0,
            ]);
        }
    }

    private function resolveAttributeId(string $attributeName, array &$attributeCache): ?int
    {
        $key = 'attr:' . strtolower(trim($attributeName));
        if (isset($attributeCache[$key])) {
            return $attributeCache[$key] === false ? null : $attributeCache[$key];
        }

        $attribute = Attribute::where('name', $attributeName)->orWhere('slug', \Illuminate\Support\Str::slug($attributeName))->first();
        $attributeCache[$key] = $attribute?->id ?? false;

        return $attributeCache[$key] === false ? null : $attributeCache[$key];
    }

    private function resolveAttributeValueId(int $attributeId, string $value, array &$attributeCache): ?int
    {
        $key = 'val:' . $attributeId . ':' . strtolower(trim($value));
        if (isset($attributeCache[$key])) {
            return $attributeCache[$key] === false ? null : $attributeCache[$key];
        }

        $attributeValue = AttributeValue::where('attribute_id', $attributeId)
            ->where(function ($q) use ($value) {
                $q->where('value', $value)
                  ->orWhere('code', strtolower(\Illuminate\Support\Str::slug($value)));
            })
            ->first();

        $attributeCache[$key] = $attributeValue?->id ?? false;

        return $attributeCache[$key] === false ? null : $attributeCache[$key];
    }

    private function attachGalleryImages(Product $product, array $row): void
    {
        $rawImages = trim((string) ($row['image_urls'] ?? ''));
        if ($rawImages === '') {
            return;
        }

        // Support both | and ; as URL separators
        $urls = preg_split('/[|;]/', $rawImages);
        $position = 0;

        foreach ($urls as $url) {
            $url = trim($url);
            if ($url === '') {
                continue;
            }

            $path = $this->downloadImage($url, 'products');
            if ($path) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'alt' => $product->name . ' - Image ' . ($position + 1),
                    'position' => $position,
                ]);
                $position++;
            }
        }
    }

    /**
     * Download a remote image into public storage. Returns stored path or null on failure.
     */
    private function downloadImage(string $url, string $subdirectory): ?string
    {
        try {
            if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
                return null;
            }

            $context = stream_context_create([
                'http' => ['timeout' => 15, 'follow_location' => true],
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            ]);

            $contents = @file_get_contents($url, false, $context);
            if ($contents === false) {
                Log::warning('Failed to download image.', ['url' => $url]);
                return null;
            }

            $extension = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION);
            $extension = strtolower($extension);
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                $extension = 'jpg';
            }

            $filename = \Illuminate\Support\Str::random(20) . '.' . $extension;
            $path = $subdirectory . '/' . $filename;

            $stored = \Illuminate\Support\Facades\Storage::disk('public')->put($path, $contents);
            if (!$stored) {
                Log::warning('Failed to store downloaded image.', ['url' => $url]);
                return null;
            }

            return $path;
        } catch (\Throwable $e) {
            Log::warning('Image download failed: ' . $e->getMessage(), ['url' => $url]);
            return null;
        }
    }

    private function generateSku(Product $product, array $attributeValueIds, string $productSku = ''): string
    {
        $attributeValues = AttributeValue::whereIn('id', $attributeValueIds)->get();

        // Prefer the product-level SKU as the base, then name, then "V"
        $productSku = trim($productSku);
        if ($productSku !== '') {
            $base = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($productSku));
        } else {
            $base = strtoupper(\Illuminate\Support\Str::slug(substr($product->name, 0, 10), ''));
        }
        if ($base === '') {
            $base = 'V';
        }

        $skuParts = [$base];

        foreach ($attributeValues as $value) {
            $skuParts[] = strtoupper(substr($value->code ?: $value->value, 0, 3));
        }

        $skuParts[] = strtoupper(\Illuminate\Support\Str::random(4));

        return implode('-', $skuParts);
    }

    private function generateSimpleSku(Product $product): string
    {
        $skuParts = [
            strtoupper(\Illuminate\Support\Str::slug(substr($product->name, 0, 15), '')),
            'SIMPLE',
            strtoupper(\Illuminate\Support\Str::random(6)),
        ];

        return implode('-', $skuParts);
    }
}
