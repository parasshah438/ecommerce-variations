<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Wishlist;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Initialize all variables with defaults
        $stats = [
            'total_orders' => 0,
            'total_spent' => 0,
            'cart_items' => 0,
            'wishlist_items' => 0,
            'pending_orders' => 0,
            'orders_this_month' => 0,
        ];
        
        $recentOrders = collect();
        $recommendedProducts = collect();
        $recentWishlist = collect();

        try {
            // Get user-specific statistics
            $stats = $this->getUserStats($user);
            
            // Get recent orders (last 5)
            $recentOrders = Order::where('user_id', $user->id)
                ->with(['items.productVariation.product'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            // Get recommended products
            $recommendedProducts = $this->getRecommendedProducts($user);
            
            // Get recent wishlist items
            $recentWishlist = Wishlist::where('user_id', $user->id)
                ->with(['product.images'])
                ->orderBy('created_at', 'desc')
                ->limit(4)
                ->get();

        } catch (\Exception $e) {
            // Log the error but don't break the page
            Log::error('Dashboard error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
        }

        return view('dashboard', compact(
            'stats', 
            'recentOrders', 
            'recommendedProducts',
            'recentWishlist'
        ));
    }

    private function getUserStats($user)
    {
        try {
            // Total orders count
            $totalOrders = Order::where('user_id', $user->id)->count();
            
            // Total amount spent
            $totalSpent = Order::where('user_id', $user->id)
                ->where('status', '!=', 'cancelled')
                ->sum('total') ?? 0;
            
            // Cart items count
            $cartItemsCount = 0;
            $cart = Cart::where('user_id', $user->id)->first();
            if ($cart) {
                $cartItemsCount = $cart->items()->sum('quantity') ?? 0;
            }
            
            // Wishlist items count
            $wishlistCount = Wishlist::where('user_id', $user->id)->count();
            
            // Pending orders count
            $pendingOrders = Order::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'confirmed', 'processing', 'shipped'])
                ->count();
            
            // Recent orders this month
            $ordersThisMonth = Order::where('user_id', $user->id)
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();

            return [
                'total_orders' => $totalOrders,
                'total_spent' => $totalSpent,
                'cart_items' => $cartItemsCount,
                'wishlist_items' => $wishlistCount,
                'pending_orders' => $pendingOrders,
                'orders_this_month' => $ordersThisMonth,
            ];
        } catch (\Exception $e) {
            \Log::error('getUserStats error: ' . $e->getMessage());
            return [
                'total_orders' => 0,
                'total_spent' => 0,
                'cart_items' => 0,
                'wishlist_items' => 0,
                'pending_orders' => 0,
                'orders_this_month' => 0,
            ];
        }
    }

    private function getRecommendedProducts($user, $limit = 4)
    {
        try {
            // Get products the user hasn't ordered (simplified query)
            $orderedProductIds = [];
            
            $userOrders = Order::where('user_id', $user->id)
                ->with('items.productVariation')
                ->get();
                
            foreach ($userOrders as $order) {
                foreach ($order->items as $item) {
                    if ($item->productVariation && $item->productVariation->product_id) {
                        $orderedProductIds[] = $item->productVariation->product_id;
                    }
                }
            }
            
            $orderedProductIds = array_unique($orderedProductIds);

            // Get popular products that user hasn't bought
            $recommendedProducts = Product::with(['images', 'variations'])
                ->whereNotIn('id', $orderedProductIds)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return $recommendedProducts;
        } catch (\Exception $e) {
            \Log::error('Recommended products error: ' . $e->getMessage());
            return collect();
        }
    }
}