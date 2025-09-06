<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
    public function index()
    {
        $products = Product::with(['category', 'brand', 'variations', 'images'])
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'price' => 'required|numeric|min:0',
            'mrp' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'stock_quantity' => 'nullable|integer|min:0',
            
            // Main product images
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            
            // Variations (optional - some products don't have variations)
            'variations' => 'nullable|array',
            'variations.*.attributes' => 'required_with:variations|array',
            'variations.*.attributes.*' => 'required_with:variations|exists:attribute_values,id',
            'variations.*.price' => 'nullable|numeric|min:0',
            'variations.*.sku' => 'nullable|string|max:100',
            'variations.*.stock' => 'required_with:variations|integer|min:0',
            'variations.*.min_qty' => 'nullable|integer|min:1',
            
            // Variation images
            'variation_images.*.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // Create the product
            $product = Product::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']) . '-' . time(),
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'brand_id' => $validated['brand_id'],
                'price' => $validated['price'],
                'mrp' => $validated['mrp'] ?? $validated['price'],
                'active' => $request->boolean('active', true), // Handle boolean properly
            ]);

            // Handle main product images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('products', 'public');
                    
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $path,
                        'alt' => $product->name . ' - Image ' . ($index + 1),
                        'position' => $index,
                    ]);
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
                            $path = $file->store('variations', 'public');
                            
                            ProductVariationImage::create([
                                'product_id' => $product->id,
                                'product_variation_id' => $variation->id,
                                'path' => $path,
                                'alt' => $this->getVariationImageAlt($product, $variation, $imageIndex),
                                'position' => $imageIndex,
                            ]);
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
}
