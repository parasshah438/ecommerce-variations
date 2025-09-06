<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index()
    {
        return view('checkout.index');
    }

    public function placeOrder(Request $request)
    {
        // placeholder for placing order (COD)
        return redirect()->route('orders.index');
    }
}
