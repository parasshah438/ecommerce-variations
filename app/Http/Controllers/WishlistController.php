<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class WishlistController extends Controller
{
    /**
     * Display the user's wishlist.
     */
    public function index()
    {
        try {
            // Check if wishlist table exists
            if (!\Schema::hasTable('wishlists')) {
                $wishlistItems = new LengthAwarePaginator(
                    collect([]), // Empty collection
                    0, // Total items
                    12, // Items per page
                    1, // Current page
                    ['path' => request()->url()]
                );
                $totalItems = 0;
                
                return view('wishlist.index', compact('wishlistItems', 'totalItems'))
                    ->with('error', 'Wishlist table does not exist. Please run: php artisan migrate');
            }

            $userId = Auth::id();
            
            if (!$userId) {
                return redirect()->route('login')
                    ->with('error', 'Please login to view your wishlist.');
            }

            $wishlistItems = Wishlist::with(['product.images', 'product.category'])
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->paginate(12);

            $totalItems = Wishlist::where('user_id', $userId)->count();

            // Debug log
            \Log::info('Wishlist loaded', [
                'user_id' => $userId,
                'items_count' => $wishlistItems->count(),
                'total_items' => $totalItems
            ]);

            return view('wishlist.index', compact('wishlistItems', 'totalItems'));
            
        } catch (\Exception $e) {
            \Log::error('Wishlist index error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback data
            $wishlistItems = new LengthAwarePaginator(
                collect([]), // Empty collection
                0, // Total items
                12, // Items per page
                1, // Current page
                ['path' => request()->url()]
            );
            $totalItems = 0;
            
            return view('wishlist.index', compact('wishlistItems', 'totalItems'))
                ->with('error', 'Error loading wishlist: ' . $e->getMessage());
        }
    }

    /**
     * Add a product to the wishlist.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        try {
            $existingItem = Wishlist::where('user_id', Auth::id())
                ->where('product_id', $request->product_id)
                ->first();

            if ($existingItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product is already in your wishlist.'
                ]);
            }

            Wishlist::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id
            ]);

            $wishlistCount = Wishlist::where('user_id', Auth::id())->count();

            return response()->json([
                'success' => true,
                'message' => 'Product added to wishlist successfully.',
                'wishlist_count' => $wishlistCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add product to wishlist: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a product from the wishlist.
     */
    public function destroy($id)
    {
        try {
            $wishlistItem = Wishlist::where('user_id', Auth::id())
                ->where('product_id', $id)
                ->first();

            if ($wishlistItem) {
                $wishlistItem->delete();
                $wishlistCount = Wishlist::where('user_id', Auth::id())->count();

                return response()->json([
                    'success' => true,
                    'message' => 'Product removed from wishlist successfully.',
                    'wishlist_count' => $wishlistCount
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Product not found in wishlist.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove product from wishlist: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all items from the wishlist.
     */
    public function clear()
    {
        try {
            Wishlist::where('user_id', Auth::id())->delete();
            
            return redirect()->route('wishlist.index')
                ->with('success', 'Wishlist cleared successfully.');
        } catch (\Exception $e) {
            return redirect()->route('wishlist.index')
                ->with('error', 'Failed to clear wishlist: ' . $e->getMessage());
        }
    }

    /**
     * Get wishlist count for the authenticated user.
     */
    public function getCount()
    {
        try {
            $count = Wishlist::where('user_id', Auth::id())->count();
            
            return response()->json([
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'count' => 0,
                'error' => 'Wishlist not available'
            ]);
        }
    }

    /**
     * Toggle wishlist item (for compatibility with existing frontend)
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        try {
            $existingItem = Wishlist::where('user_id', Auth::id())
                ->where('product_id', $request->product_id)
                ->first();

            if ($existingItem) {
                // Remove from wishlist
                $existingItem->delete();
                $wishlistCount = Wishlist::where('user_id', Auth::id())->count();
                
                return response()->json([
                    'success' => true,
                    'action' => 'removed',
                    'message' => 'Product removed from wishlist.',
                    'wishlist_count' => $wishlistCount
                ]);
            } else {
                // Add to wishlist
                Wishlist::create([
                    'user_id' => Auth::id(),
                    'product_id' => $request->product_id
                ]);
                
                $wishlistCount = Wishlist::where('user_id', Auth::id())->count();
                
                return response()->json([
                    'success' => true,
                    'action' => 'added',
                    'message' => 'Product added to wishlist.',
                    'wishlist_count' => $wishlistCount
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle wishlist: ' . $e->getMessage()
            ], 500);
        }
    }
}
