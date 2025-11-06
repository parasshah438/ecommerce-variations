<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardTestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Simple test data
        $stats = [
            'total_orders' => 5,
            'total_spent' => 1250.50,
            'cart_items' => 3,
            'wishlist_items' => 7,
            'pending_orders' => 2,
            'orders_this_month' => 3,
        ];
        
        $recentOrders = collect();
        $recommendedProducts = collect();
        $recentWishlist = collect();

        return view('dashboard', compact(
            'stats', 
            'recentOrders', 
            'recommendedProducts',
            'recentWishlist'
        ));
    }
}