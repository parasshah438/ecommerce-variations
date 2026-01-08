<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Helpers\ImageOptimizer;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ProductImage;
use App\Models\ProductVariationImage;
use App\Models\VariationStock;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Attribute;
use App\Models\AttributeValue;

class ProductController extends Controller
{
    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $attributes = Attribute::with('values')->orderBy('name')->get();
        $product->load(['images', 'variations.images', 'variations.stock']);
        return view('admin.products.edit', compact('product', 'categories', 'brands', 'attributes'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        // Filter out empty variations before validation
        $variations = $request->input('variations', []);
        $variations = array_filter($variations, function($variation) {
            return !empty($variation) && !empty($variation['attributes']);
        });
        
        // Update request with filtered variations
        $request->merge(['variations' => $variations]);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'price' => 'required|numeric|min:0',
            'mrp' => 'nullable|numeric|min:0',
            'weight' => 'required|numeric|min:1|max:50000',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'stock_quantity' => 'nullable|integer|min:0',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
            'video' => 'nullable|file|mimes:mp4,webm,ogg,avi,mov|max:51200', // 50MB max
            'variations' => 'nullable|array',
            'variations.*.id' => 'nullable|exists:product_variations,id',
            'variations.*.attributes' => 'required|array|min:1',
            'variations.*.attributes.*' => 'required|exists:attribute_values,id',
            'variations.*.price' => 'nullable|numeric|min:0',
            'variations.*.sku' => 'nullable|string|max:100',
            'variations.*.weight' => 'nullable|numeric|min:1|max:50000',
            'variations.*.stock' => 'required|integer|min:0',
            'variations.*.min_qty' => 'nullable|integer|min:1',
            'variation_images.*.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
        ]);

        try {
            DB::beginTransaction();

            // Calculate volumetric weight if dimensions provided
            $volumetricWeight = null;
            if ($validated['length'] && $validated['width'] && $validated['height']) {
                $volumetricWeight = ($validated['length'] * $validated['width'] * $validated['height']) / 5000;
            }

            // Handle video upload
            $updateData = [
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']) . '-' . $product->id,
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'],
                'price' => $validated['price'],
                'mrp' => $validated['mrp'] ?? $validated['price'],
                'weight' => $validated['weight'],
                'length' => $validated['length'],
                'width' => $validated['width'],
                'height' => $validated['height'],
                'volumetric_weight' => $volumetricWeight,
                'active' => $request->boolean('active', true),
            ];

            if ($request->hasFile('video')) {
                // Delete old video if exists
                if ($product->video && Storage::disk('public')->exists($product->video)) {
                    Storage::disk('public')->delete($product->video);
                }
                // Store new video
                $updateData['video'] = $request->file('video')->store('products/videos', 'public');
            }

            // Update product
            $product->update($updateData);

            // Handle new product images (add only, not delete)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    try {
                        // Use new ImageOptimizer handleUpload method
                        $optimizationResult = ImageOptimizer::handleUpload(
                            $file,
                            'products',
                            [
                                'quality' => 85,
                                'max_width' => 1600,
                                'max_height' => 1600,
                                'force_resize' => false,
                                'generate_webp' => true,
                                'thumbnails' => [150, 300, 600]
                            ]
                        );
                        
                        $imagePath = null;
                        if (isset($optimizationResult['queued'])) {
                            // Image was queued for processing
                            $imagePath = $optimizationResult['path'];
                            \Log::info('Product image queued for optimization', ['path' => $imagePath]);
                        } else {
                            // Image was processed immediately
                            $imagePath = $optimizationResult['path'];
                            \Log::info('Product image optimized successfully', [
                                'path' => $imagePath,
                                'webp_generated' => isset($optimizationResult['webp_path'])
                            ]);
                        }
                        
                        ProductImage::create([
                            'product_id' => $product->id,
                            'path' => $imagePath,
                            'alt' => $product->name . ' - Image ' . ($index + 1),
                            'position' => $index,
                        ]);
                    } catch (\Exception $e) {
                        // Final fallback to basic storage
                        \Log::error('All image processing methods failed, using basic storage: ' . $e->getMessage());
                        try {
                            $path = $file->store('products', 'public');
                            ProductImage::create([
                                'product_id' => $product->id,
                                'path' => $path,
                                'alt' => $product->name . ' - Image ' . ($index + 1),
                                'position' => $index,
                            ]);
                        } catch (\Exception $basicError) {
                            \Log::error('Even basic storage failed: ' . $basicError->getMessage());
                            // Continue with other images instead of failing completely
                        }
                    }
                }
            }

            // Update or create variations
            $existingVariationIds = $product->variations()->pluck('id')->toArray();
            $submittedVariationIds = [];
            $variationsCreated = 0;
            if (!empty($validated['variations'])) {
                foreach ($validated['variations'] as $variationIndex => $variationData) {
                    $variationId = $variationData['id'] ?? null;
                    $sku = $variationData['sku'] ?? $this->generateSku($product, $variationData['attributes']);
                    if ($variationId) {
                        // Update existing variation
                        $variation = ProductVariation::find($variationId);
                        if ($variation) {
                            $variation->update([
                                'sku' => $sku,
                                'price' => $variationData['price'] ?? $product->price,
                                'weight' => $variationData['weight'] ?? $product->weight,
                                'min_qty' => $variationData['min_qty'] ?? 1,
                                'attribute_value_ids' => $variationData['attributes'],
                            ]);
                            // Update stock
                            $variation->stock()->updateOrCreate(
                                ['product_variation_id' => $variation->id],
                                [
                                    'quantity' => $variationData['stock'],
                                    'in_stock' => $variationData['stock'] > 0,
                                ]
                            );
                            $submittedVariationIds[] = $variation->id;
                        }
                    } else {
                        // Create new variation
                        $variation = ProductVariation::create([
                            'product_id' => $product->id,
                            'sku' => $sku,
                            'price' => $variationData['price'] ?? $product->price,
                            'weight' => $variationData['weight'] ?? $product->weight,
                            'min_qty' => $variationData['min_qty'] ?? 1,
                            'attribute_value_ids' => $variationData['attributes'],
                        ]);
                        VariationStock::create([
                            'product_variation_id' => $variation->id,
                            'quantity' => $variationData['stock'],
                            'in_stock' => $variationData['stock'] > 0,
                        ]);
                        $submittedVariationIds[] = $variation->id;
                        $variationsCreated++;
                    }

                    // Handle variation images (add only, not delete)
                    $variationImageKey = "variation_images.{$variationIndex}";
                    if ($request->hasFile($variationImageKey)) {
                        foreach ($request->file($variationImageKey) as $imageIndex => $file) {
                            try {
                                // Use new ImageOptimizer handleUpload method
                                $optimizationResult = ImageOptimizer::handleUpload(
                                    $file,
                                    'variations',
                                    [
                                        'quality' => 85,
                                        'max_width' => 800,
                                        'max_height' => 800,
                                        'force_resize' => false,
                                        'generate_webp' => true,
                                        'thumbnails' => [150, 300]
                                    ]
                                );
                                
                                $imagePath = null;
                                if (isset($optimizationResult['queued'])) {
                                    // Image was queued for processing
                                    $imagePath = $optimizationResult['path'];
                                    \Log::info('Variation image queued for optimization', ['path' => $imagePath]);
                                } else {
                                    // Image was processed immediately
                                    $imagePath = $optimizationResult['path'];
                                    \Log::info('Variation image optimized successfully', [
                                        'path' => $imagePath,
                                        'webp_generated' => isset($optimizationResult['webp_path'])
                                    ]);
                                }
                                
                                ProductVariationImage::create([
                                    'product_id' => $product->id,
                                    'product_variation_id' => $variation->id,
                                    'path' => $imagePath,
                                    'alt' => $this->getVariationImageAlt($product, $variation, $imageIndex),
                                    'position' => $imageIndex,
                                ]);
                            } catch (\Exception $e) {
                                // Final fallback to basic storage
                                \Log::error('All variation image processing methods failed, using basic storage: ' . $e->getMessage());
                                try {
                                    $path = $file->store('variations', 'public');
                                    ProductVariationImage::create([
                                        'product_id' => $product->id,
                                        'product_variation_id' => $variation->id,
                                        'path' => $path,
                                        'alt' => $this->getVariationImageAlt($product, $variation, $imageIndex),
                                        'position' => $imageIndex,
                                    ]);
                                } catch (\Exception $basicError) {
                                    \Log::error('Even basic variation image storage failed: ' . $basicError->getMessage());
                                }
                            }
                        }
                    }
                }
                // Delete removed variations
                $toDelete = array_diff($existingVariationIds, $submittedVariationIds);
                if (!empty($toDelete)) {
                    ProductVariation::whereIn('id', $toDelete)->delete();
                }
            } else {
                // Simple product (no variations)
                $sku = $validated['sku'] ?? $this->generateSimpleSku($product);
                $stockQuantity = $validated['stock_quantity'] ?? 10;
                $variation = $product->variations()->first();
                if ($variation) {
                    $variation->update([
                        'sku' => $sku,
                        'price' => $product->price,
                        'min_qty' => 1,
                        'attribute_value_ids' => [],
                    ]);
                    $variation->stock()->updateOrCreate(
                        ['product_variation_id' => $variation->id],
                        [
                            'quantity' => $stockQuantity,
                            'in_stock' => $stockQuantity > 0,
                        ]
                    );
                } else {
                    $variation = ProductVariation::create([
                        'product_id' => $product->id,
                        'sku' => $sku,
                        'price' => $product->price,
                        'min_qty' => 1,
                        'attribute_value_ids' => [],
                    ]);
                    VariationStock::create([
                        'product_variation_id' => $variation->id,
                        'quantity' => $stockQuantity,
                        'in_stock' => $stockQuantity > 0,
                    ]);
                }
            }

            DB::commit();
            
            // Clear relevant caches
            Cache::forget('featured_products');
            Cache::forget('latest_products');
            Cache::forget('category_products_' . $product->category_id);
            
            return redirect()->route('admin.products.show', $product)->with('success', 'Product updated successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()]);
        }
    }
    public function index()
    {
        $products = Product::with(['category', 'brand', 'variations', 'images', 'variationImages'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $attributes = Attribute::with('values')->orderBy('name')->get();
        
        return view('admin.products.create', compact('categories', 'brands', 'attributes'));
    }

    public function store(Request $request)
    {
        // Filter out empty variations before validation
        $variations = $request->input('variations', []);
        $variations = array_filter($variations, function($variation) {
            return !empty($variation) && !empty($variation['attributes']);
        });
        
        // Update request with filtered variations
        $request->merge(['variations' => $variations]);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'price' => 'required|numeric|min:0',
            'mrp' => 'nullable|numeric|min:0',
            'weight' => 'required|numeric|min:1|max:50000',
            'length' => 'nullable|numeric|min:0|max:1000',
            'width' => 'nullable|numeric|min:0|max:1000',
            'height' => 'nullable|numeric|min:0|max:1000',
            'sku' => 'nullable|string|max:100',
            'stock_quantity' => 'nullable|integer|min:0',
            
            // Main product images
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
            
            // Product video
            'video' => 'nullable|file|mimes:mp4,webm,ogg,avi,mov|max:51200', // 50MB max
            
            // Variations (optional - some products don't have variations)
            'variations' => 'nullable|array',
            'variations.*.attributes' => 'required|array|min:1',
            'variations.*.attributes.*' => 'required|exists:attribute_values,id',
            'variations.*.price' => 'nullable|numeric|min:0',
            'variations.*.sku' => 'nullable|string|max:100',
            'variations.*.weight' => 'nullable|numeric|min:1|max:50000',
            'variations.*.stock' => 'required|integer|min:0',
            'variations.*.min_qty' => 'nullable|integer|min:1',
            
            // Variation images
            'variation_images.*.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
        ]);

        try {
            DB::beginTransaction();

            // Handle video upload
            $videoPath = null;
            if ($request->hasFile('video')) {
                $videoPath = $request->file('video')->store('products/videos', 'public');
            }

            // Calculate volumetric weight if dimensions provided
            $volumetricWeight = null;
            if ($request->length && $request->width && $request->height) {
                $volumetricWeight = ($request->length * $request->width * $request->height) / 5000;
            }

            // Create the product
            $product = Product::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']) . '-' . time(),
                'description' => $validated['description'],
                'video' => $videoPath,
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'],
                'price' => $validated['price'],
                'mrp' => $validated['mrp'] ?? $validated['price'],
                'weight' => $validated['weight'],
                'length' => $validated['length'],
                'width' => $validated['width'],
                'height' => $validated['height'],
                'volumetric_weight' => $volumetricWeight,
                'active' => $request->boolean('active', true), // Handle boolean properly
            ]);

            // Handle main product images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    try {
                        // Use new ImageOptimizer handleUpload method
                        $optimizationResult = ImageOptimizer::handleUpload(
                            $file,
                            'products',
                            [
                                'quality' => 85,
                                'max_width' => 1600,
                                'max_height' => 1600,
                                'force_resize' => false,
                                'generate_webp' => true,
                                'thumbnails' => [150, 300, 600]
                            ]
                        );
                        
                        $imagePath = null;
                        if (isset($optimizationResult['queued'])) {
                            // Image was queued for processing
                            $imagePath = $optimizationResult['path'];
                            \Log::info('Product image queued for optimization', ['path' => $imagePath]);
                        } else {
                            // Image was processed immediately
                            $imagePath = $optimizationResult['path'];
                            \Log::info('Product image optimized successfully', [
                                'path' => $imagePath,
                                'webp_generated' => isset($optimizationResult['webp_path'])
                            ]);
                        }
                        
                        ProductImage::create([
                            'product_id' => $product->id,
                            'path' => $imagePath,
                            'alt' => $product->name . ' - Image ' . ($index + 1),
                            'position' => $index,
                        ]);
                    } catch (\Exception $e) {
                        // Final fallback to basic storage
                        \Log::error('All image processing methods failed, using basic storage: ' . $e->getMessage());
                        try {
                            $path = $file->store('products', 'public');
                            ProductImage::create([
                                'product_id' => $product->id,
                                'path' => $path,
                                'alt' => $product->name . ' - Image ' . ($index + 1),
                                'position' => $index,
                            ]);
                        } catch (\Exception $basicError) {
                            \Log::error('Even basic storage failed: ' . $basicError->getMessage());
                        }
                    }
                }
            }

            // Create variations (only if variations are provided)
            $variationsCreated = 0;
            if (!empty($validated['variations'])) {
                // Product has variations
                foreach ($validated['variations'] as $variationIndex => $variationData) {
                    // Generate SKU if not provided
                    $sku = $variationData['sku'] ?? $this->generateSku($product, $variationData['attributes']);
                    
                    $variation = ProductVariation::create([
                        'product_id' => $product->id,
                        'sku' => $sku,
                        'price' => $variationData['price'] ?? $product->price,
                        'weight' => $variationData['weight'] ?? $product->weight,
                        'min_qty' => $variationData['min_qty'] ?? 1,
                        'attribute_value_ids' => $variationData['attributes'],
                    ]);

                    // Create stock
                    VariationStock::create([
                        'product_variation_id' => $variation->id,
                        'quantity' => $variationData['stock'],
                        'in_stock' => $variationData['stock'] > 0,
                    ]);

                    // Handle variation-specific images
                    $variationImageKey = "variation_images.{$variationIndex}";
                    if ($request->hasFile($variationImageKey)) {
                        foreach ($request->file($variationImageKey) as $imageIndex => $file) {
                            try {
                                // Use new ImageOptimizer handleUpload method
                                $optimizationResult = ImageOptimizer::handleUpload(
                                    $file,
                                    'variations',
                                    [
                                        'quality' => 85,
                                        'max_width' => 800,
                                        'max_height' => 800,
                                        'force_resize' => false,
                                        'generate_webp' => true,
                                        'thumbnails' => [150, 300]
                                    ]
                                );
                                
                                $imagePath = null;
                                if (isset($optimizationResult['queued'])) {
                                    // Image was queued for processing
                                    $imagePath = $optimizationResult['path'];
                                    \Log::info('Variation image queued for optimization', ['path' => $imagePath]);
                                } else {
                                    // Image was processed immediately
                                    $imagePath = $optimizationResult['path'];
                                    \Log::info('Variation image optimized successfully', [
                                        'path' => $imagePath,
                                        'webp_generated' => isset($optimizationResult['webp_path'])
                                    ]);
                                }
                                
                                ProductVariationImage::create([
                                    'product_id' => $product->id,
                                    'product_variation_id' => $variation->id,
                                    'path' => $imagePath,
                                    'alt' => $this->getVariationImageAlt($product, $variation, $imageIndex),
                                    'position' => $imageIndex,
                                ]);
                            } catch (\Exception $e) {
                                // Final fallback to basic storage
                                \Log::error('All variation image processing methods failed, using basic storage: ' . $e->getMessage());
                                try {
                                    $path = $file->store('variations', 'public');
                                    ProductVariationImage::create([
                                        'product_id' => $product->id,
                                        'product_variation_id' => $variation->id,
                                        'path' => $path,
                                        'alt' => $this->getVariationImageAlt($product, $variation, $imageIndex),
                                        'position' => $imageIndex,
                                    ]);
                                } catch (\Exception $basicError) {
                                    \Log::error('Even basic variation image storage failed: ' . $basicError->getMessage());
                                }
                            }
                        }
                    }
                    
                    $variationsCreated++;
                }
            } else {
                // Simple product without variations - create a default variation for stock management
                $sku = $validated['sku'] ?? $this->generateSimpleSku($product);
                $stockQuantity = $validated['stock_quantity'] ?? 10;
                
                $variation = ProductVariation::create([
                    'product_id' => $product->id,
                    'sku' => $sku,
                    'price' => $product->price,
                    'min_qty' => 1,
                    'attribute_value_ids' => [], // No attributes for simple products
                ]);

                // Create stock for simple product
                VariationStock::create([
                    'product_variation_id' => $variation->id,
                    'quantity' => $stockQuantity,
                    'in_stock' => $stockQuantity > 0,
                ]);
            }

            DB::commit();

            // Clear relevant caches
            Cache::forget('featured_products');
            Cache::forget('latest_products');
            Cache::forget('category_products_' . $product->category_id);

            if ($variationsCreated > 0) {
                $message = "Product created successfully with {$variationsCreated} variations!";
            } else {
                $message = "Simple product created successfully! (No variations)";
            }

            return redirect()
                ->route('admin.products.show', $product)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create product: ' . $e->getMessage()]);
        }
    }

    public function show(Product $product)
    {
        $product->load([
            'category',
            'brand',
            'images',
            'variations.stock',
            'variations.images'
        ]);
        
        return view('admin.products.show', compact('product'));
    }

    public function getAttributeValues(Attribute $attribute)
    {
        return response()->json([
            'values' => $attribute->values->map(function ($value) {
                return [
                    'id' => $value->id,
                    'value' => $value->value,
                    'code' => $value->code,
                ];
            })
        ]);
    }

    public function previewVariations(Request $request)
    {
        $attributes = $request->input('attributes', []);
        $combinations = $this->generateVariationCombinations($attributes);
        
        return response()->json([
            'combinations' => $combinations,
            'count' => count($combinations)
        ]);
    }

    private function generateSku(Product $product, array $attributeValueIds)
    {
        $attributeValues = AttributeValue::whereIn('id', $attributeValueIds)->get();
        
        $skuParts = [
            strtoupper(Str::slug(substr($product->name, 0, 10), '')),
        ];
        
        foreach ($attributeValues as $value) {
            $skuParts[] = strtoupper(substr($value->code ?: $value->value, 0, 3));
        }
        
        $skuParts[] = strtoupper(Str::random(4));
        
        return implode('-', $skuParts);
    }

    private function generateSimpleSku(Product $product)
    {
        $skuParts = [
            strtoupper(Str::slug(substr($product->name, 0, 15), '')),
            'SIMPLE',
            strtoupper(Str::random(6))
        ];
        
        return implode('-', $skuParts);
    }

    private function getVariationImageAlt(Product $product, ProductVariation $variation, int $index)
    {
        $attributeValues = $variation->attributeValues;
        $attributeNames = $attributeValues->pluck('value')->implode(' ');
        
        return $product->name . ' - ' . $attributeNames . ' - Image ' . ($index + 1);
    }

    private function generateVariationCombinations(array $attributes)
    {
        if (empty($attributes)) {
            return [];
        }

        $combinations = [[]];
        
        foreach ($attributes as $attributeId => $valueIds) {
            $newCombinations = [];
            $values = AttributeValue::whereIn('id', $valueIds)->get();
            
            foreach ($combinations as $combination) {
                foreach ($values as $value) {
                    $newCombination = $combination;
                    $newCombination[$attributeId] = [
                        'id' => $value->id,
                        'value' => $value->value,
                        'attribute_name' => $value->attribute->name ?? 'Unknown'
                    ];
                    $newCombinations[] = $newCombination;
                }
            }
            
            $combinations = $newCombinations;
        }
        
        return $combinations;
    }

    /**
     * Duplicate the specified product and its variations/images.
     */
    public function duplicate(Product $product)
    {
        DB::beginTransaction();
        try {
            // Duplicate product
            $newProduct = $product->replicate();
            $newProduct->name = $product->name . ' (Copy)';
            $newProduct->slug = Str::slug($newProduct->name) . '-' . time();
            $newProduct->push();

            // Duplicate images
            foreach ($product->images as $image) {
                $newImage = $image->replicate();
                $newImage->product_id = $newProduct->id;
                $newImage->push();
            }

            // Duplicate variations
            foreach ($product->variations as $variation) {
                $newVariation = $variation->replicate();
                $newVariation->product_id = $newProduct->id;
                // Generate unique SKU for the duplicated variation
                $newVariation->sku = $variation->sku . '-' . strtoupper(Str::random(4));
                $newVariation->push();

                // Duplicate variation stock
                if ($variation->stock) {
                    $newStock = $variation->stock->replicate();
                    $newStock->product_variation_id = $newVariation->id;
                    $newStock->push();
                }

                // Duplicate variation images
                foreach ($variation->images as $vImage) {
                    $newVImage = $vImage->replicate();
                    $newVImage->product_id = $newProduct->id;
                    $newVImage->product_variation_id = $newVariation->id;
                    $newVImage->push();
                }

                // Sync attribute values (assign directly to attribute_value_ids)
                $newVariation->attribute_value_ids = $variation->attribute_value_ids;
                $newVariation->save();
            }

            DB::commit();
            return redirect()->route('admin.products.edit', $newProduct)->with('success', 'Product duplicated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to duplicate product: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified product from storage.
     * Deletes all related data including variations, images, and stock.
     */
    public function destroy(Product $product)
    {
        try {
            DB::beginTransaction();

            // Delete all product images (with automatic file cleanup)
            foreach ($product->images as $image) {
                $image->delete(); // Uses model's delete method which handles file cleanup
            }

            // Delete all variation-related data
            foreach ($product->variations as $variation) {
                // Delete variation images (with automatic file cleanup)
                foreach ($variation->images as $image) {
                    $image->delete(); // Uses model's delete method which handles file cleanup
                }

                // Delete variation stock
                $variation->stock()->delete();
                
                // Delete the variation itself
                $variation->delete();
            }

            // Delete the product itself
            $product->delete();

            // Clear any cached data
            Cache::tags(['products'])->flush();

            DB::commit();

            return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully along with all related data!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to delete product: ' . $e->getMessage(), ['product_id' => $product->id]);
            return back()->withErrors(['error' => 'Failed to delete product: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified product image from storage.
     */
    public function destroyImage(ProductImage $image)
    {
        try {
            // Delete the image file and all its variants
            $image->delete(); // Uses model's delete method which handles file cleanup

            return response()->json(['success' => true, 'message' => 'Image deleted successfully!']);
        } catch (\Exception $e) {
            \Log::error('Failed to delete product image: ' . $e->getMessage(), ['image_id' => $image->id]);
            return response()->json(['success' => false, 'message' => 'Failed to delete image: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified product variation from storage.
     */
    public function destroyVariation(ProductVariation $variation)
    {
        try {
            DB::beginTransaction();

            // Delete variation images (with automatic file cleanup)
            foreach ($variation->images as $image) {
                $image->delete(); // Uses model's delete method which handles file cleanup
            }

            // Delete variation stock
            $variation->stock()->delete();
            
            // Delete the variation itself
            $variation->delete();

            // Clear any cached data
            Cache::tags(['products'])->flush();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Variation deleted successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to delete variation: ' . $e->getMessage(), ['variation_id' => $variation->id]);
            return response()->json(['success' => false, 'message' => 'Failed to delete variation: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store new product images.
     */
    public function storeImage(Request $request, Product $product)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        try {
            $uploadedImages = [];

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    try {
                        // Use ImageOptimizer for consistent image processing
                        $optimizationResult = ImageOptimizer::handleUpload(
                            $file,
                            'products',
                            [
                                'quality' => 85,
                                'max_width' => 1200,
                                'max_height' => 1200,
                                'force_resize' => false,
                                'generate_webp' => true,
                                'thumbnails' => [150, 300, 600]
                            ]
                        );

                        $imagePath = $optimizationResult['path'];

                        // Create product image record
                        $image = ProductImage::create([
                            'product_id' => $product->id,
                            'path' => $imagePath,
                            'alt' => $product->name . ' - Image ' . ($product->images()->count() + $index + 1),
                            'position' => $product->images()->count() + $index,
                        ]);

                        $uploadedImages[] = $image;

                    } catch (\Exception $e) {
                        \Log::error('Failed to process product image: ' . $e->getMessage());
                        // Continue with other images instead of failing completely
                    }
                }
            }

            // Clear any cached data
            Cache::tags(['products'])->flush();

            return response()->json([
                'success' => true, 
                'message' => count($uploadedImages) . ' image(s) uploaded successfully!',
                'images' => $uploadedImages
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to store product images: ' . $e->getMessage(), ['product_id' => $product->id]);
            return response()->json(['success' => false, 'message' => 'Failed to upload images: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a new product variation.
     */
    public function storeVariation(Request $request, Product $product)
    {
        $request->validate([
            'attributes' => 'required|array',
            'sku' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_qty' => 'nullable|integer|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        try {
            DB::beginTransaction();

            // Generate SKU if not provided
            $sku = $request->input('sku') ?: $this->generateSku($product, $request->input('attributes'));

            // Create the variation
            $variation = ProductVariation::create([
                'product_id' => $product->id,
                'sku' => $sku,
                'price' => $request->input('price'),
                'min_qty' => $request->input('min_qty', 1),
                'attribute_value_ids' => $request->input('attributes'),
            ]);

            // Create stock record
            VariationStock::create([
                'product_variation_id' => $variation->id,
                'quantity' => $request->input('stock'),
                'in_stock' => $request->input('stock') > 0,
            ]);

            // Handle variation images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    try {
                        // Use ImageOptimizer for consistent image processing
                        $optimizationResult = ImageOptimizer::handleUpload(
                            $file,
                            'variations',
                            [
                                'quality' => 85,
                                'max_width' => 800,
                                'max_height' => 800,
                                'force_resize' => false,
                                'generate_webp' => true,
                                'thumbnails' => [150, 300]
                            ]
                        );

                        $imagePath = $optimizationResult['path'];

                        // Create variation image record
                        ProductVariationImage::create([
                            'product_id' => $product->id,
                            'product_variation_id' => $variation->id,
                            'path' => $imagePath,
                            'alt' => $this->getVariationImageAlt($product, $variation, $index),
                            'position' => $index,
                        ]);

                    } catch (\Exception $e) {
                        \Log::error('Failed to process variation image: ' . $e->getMessage());
                        // Continue with other images instead of failing completely
                    }
                }
            }

            // Clear any cached data
            Cache::tags(['products'])->flush();

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Variation created successfully!',
                'variation' => $variation->load(['stock', 'images'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to store variation: ' . $e->getMessage(), ['product_id' => $product->id]);
            return response()->json(['success' => false, 'message' => 'Failed to create variation: ' . $e->getMessage()], 500);
        }
    }
}
