<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    /**
     * Display all active sales
     */
    public function index()
    {
        $activeSales = Sale::where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->latest()
            ->paginate(12);

        return view('frontend.sales.index', compact('activeSales'));
    }

    /**
     * Display a specific sale and its products
     */
    public function show(Sale $sale)
    {
        if (!$sale->isActive()) {
            abort(404, 'Sale not found or expired');
        }

        $products = $sale->products()
            ->with(['images', 'brand', 'category'])
            ->where('active', true)
            ->paginate(20);

        return view('frontend.sales.show', compact('sale', 'products'));
    }

    /**
     * Get sale products with filtering
     */
    public function products(Sale $sale, Request $request)
    {
        if (!$sale->isActive()) {
            abort(404, 'Sale not found or expired');
        }

        $query = $sale->products()
            ->with(['images', 'brand', 'category'])
            ->where('active', true);

        // Filter by category if provided
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by brand if provided
        if ($request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }

        // Filter by price range
        if ($request->min_price || $request->max_price) {
            if ($request->min_price) {
                $query->where('price', '>=', $request->min_price);
            }
            if ($request->max_price) {
                $query->where('price', '<=', $request->max_price);
            }
        }

        // Sort products
        $sort = $request->sort ?? 'name';
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'rating':
                $query->orderBy('average_rating', 'desc');
                break;
            default:
                $query->orderBy('name', 'asc');
        }

        $products = $query->paginate(20);

        if ($request->ajax()) {
            return response()->json([
                'products' => view('frontend.sales.product-list', compact('products', 'sale'))->render(),
                'pagination' => $products->links()->render()
            ]);
        }

        return view('frontend.sales.products', compact('sale', 'products'));
    }

    /**
     * Get active sales for homepage
     */
    public function getActiveSales()
    {
        return Sale::where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();
    }
}
