<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['images', 'variations'])->paginate(12);
        
        // Add price range calculation for each product
        $products->getCollection()->transform(function ($product) {
            if ($product->variations->count() > 0) {
                $prices = $product->variations->pluck('price')->filter();
                if ($prices->count() > 0) {
                    $product->min_price = $prices->min();
                    $product->max_price = $prices->max();
                    $product->has_variations = true;
                } else {
                    $product->min_price = $product->price;
                    $product->max_price = $product->price;
                    $product->has_variations = false;
                }
            } else {
                // Simple product (no variations)
                $product->min_price = $product->price;
                $product->max_price = $product->price;
                $product->has_variations = false;
            }
            return $product;
        });
        
        return view('products.index', compact('products'));
    }

    public function show($slug)
    {
        $product = Product::with(['images', 'variations.stock'])->where('slug', $slug)->firstOrFail();

        // Prepare JSON-friendly variations
        $variations = $product->variations->map(function ($v) {
            return [
                'id' => $v->id,
                'sku' => $v->sku,
                'price' => (float)$v->price,
                'values' => $v->attribute_value_ids,
                'in_stock' => optional($v->stock)->quantity > 0,
                'quantity' => optional($v->stock)->quantity ?? 0,
            ];
        })->values();

        // Variation images (product_variation_images) grouped by variation id, with full asset URLs
        $rawVariationImages = \App\Models\ProductVariationImage::where('product_id', $product->id)->get();
        $variationImages = $rawVariationImages->groupBy('product_variation_id')->map(function ($group) {
            return $group->map(function ($img) {
                return [
                    'id' => $img->id,
                    'path' => \Illuminate\Support\Facades\Storage::url($img->path),
                    'position' => $img->position,
                ];
            })->values();
        })->toArray();

        // Product-level images with asset URLs
        $productImages = $product->images->map(function ($i) {
            return ['id' => $i->id, 'path' => \Illuminate\Support\Facades\Storage::url($i->path), 'position' => $i->position];
        })->values();

        // Prepare attribute groups (attribute -> options) used by this product's variations
        $allValueIds = collect($variations)->flatMap(function ($v) { return $v['values']; })->unique()->values()->all();
        $attributeGroups = [];
        if (!empty($allValueIds)) {
            $values = \App\Models\AttributeValue::whereIn('id', $allValueIds)->with('attribute')->get();
            foreach ($values as $val) {
                $attrName = $val->attribute->name ?? 'Other';
                if (!isset($attributeGroups[$attrName])) {
                    $attributeGroups[$attrName] = [];
                }
                $attributeGroups[$attrName][] = [
                    'id' => $val->id,
                    'value' => $val->value,
                    'attribute_id' => $val->attribute_id,
                ];
            }
        }

        // Debug logging
        \Log::info('Product variations debug', [
            'product_id' => $product->id,
            'variations_count' => count($variations),
            'attribute_groups' => array_keys($attributeGroups),
            'all_value_ids' => $allValueIds
        ]);

        return view('products.show', compact('product', 'variations', 'variationImages', 'productImages', 'attributeGroups'));
    }

    public function loadMore(Request $request)
    {
        $products = Product::with(['images', 'variations'])->paginate(12);
        
        // Add price range calculation for each product
        $products->getCollection()->transform(function ($product) {
            if ($product->variations->count() > 0) {
                $prices = $product->variations->pluck('price')->filter();
                if ($prices->count() > 0) {
                    $product->min_price = $prices->min();
                    $product->max_price = $prices->max();
                    $product->has_variations = true;
                } else {
                    $product->min_price = $product->price;
                    $product->max_price = $product->price;
                    $product->has_variations = false;
                }
            } else {
                // Simple product (no variations)
                $product->min_price = $product->price;
                $product->max_price = $product->price;
                $product->has_variations = false;
            }
            return $product;
        });
        
        return view('products._list', compact('products'));
    }
}
