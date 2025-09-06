<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_products' => \App\Models\Product::count(),
            'total_variations' => \App\Models\ProductVariation::count(),
            'total_orders' => \App\Models\Order::count(),
            'total_customers' => \App\Models\User::count(),
        ];
        
        return view('admin.dashboard', compact('stats'));
    }
}
