<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('images')->paginate(12);
        return view('products.index', compact('products'));
    }

    public function loadMore(Request $request)
    {
        $products = Product::with('images')->paginate(12);
        return view('products._list', compact('products'));
    }

    public function show($slug)
    {
        $product = Product::with(['images', 'variations.stock'])->where('slug', $slug)->firstOrFail();
        return view('products.show', compact('product'));
    }

    public function search(Request $request)
    {
        $q = $request->query('q');
        $results = Product::where('name', 'like', "%{$q}%")->limit(10)->get();
        return response()->json($results);
    }
}
