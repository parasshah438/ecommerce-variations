<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariation;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        // For now show simple view
        return view('cart.index');
    }

    public function add(Request $request)
    {
        // placeholder for ajax add to cart
        return response()->json(['success' => true]);
    }

    public function update(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function remove(Request $request)
    {
        return response()->json(['success' => true]);
    }
}
