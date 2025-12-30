<?php

namespace App\Http\Controllers;

use App\Models\RecentView;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class RecentViewController extends Controller
{
    /**
     * Display the user's recently viewed products.
     */
    public function index()
    {
        try {
            // Check if recent_views table exists
            if (!Schema::hasTable('recent_views')) {
                $recentViews = new LengthAwarePaginator(
                    collect([]), // Empty collection
                    0, // Total items
                    12, // Items per page
                    1, // Current page
                    ['path' => request()->url()]
                );

             
                
                return view('recent-views.index', compact('recentViews'))
                    ->with('error', 'Recent views table does not exist. Please run: php artisan migrate');
            }

            $userId = Auth::id();
            
            if (!$userId) {
                return redirect()->route('login')
                    ->with('error', 'Please login to view your recent views.');
            }

            $recentViews = RecentView::with(['product.images', 'product.category'])
                ->forUser($userId)
                ->recent()
                ->take(50) // Limit to 50 most recent views
                ->paginate(12);

              //  dd( $recentViews);

            Log::info('Recent views loaded', [
                'user_id' => $userId,
                'items_count' => $recentViews->count()
            ]);

            return view('recent-views.index', compact('recentViews'));
            
        } catch (\Exception $e) {
           
         //   dd( $e->getMessage());
            Log::error('Recent views index error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback data
            $recentViews = new LengthAwarePaginator(
                collect([]), // Empty collection
                0, // Total items
                12, // Items per page
                1, // Current page
                ['path' => request()->url()]
            );
            
            return view('recent-views.index', compact('recentViews'))
                ->with('error', 'Error loading recent views: ' . $e->getMessage());
        }
    }

    /**
     * Remove a product from recent views.
     */
    public function destroy($id)
    {
        try {
            // Check if recent_views table exists
            if (!Schema::hasTable('recent_views')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recent views table does not exist. Please run migrations.'
                ], 400);
            }

            $userId = Auth::id();
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.'
                ], 401);
            }

            $recentView = RecentView::where('user_id', $userId)
                ->where('product_id', $id)
                ->first();

            if ($recentView) {
                $productName = optional($recentView->product)->name ?? 'Unknown Product';
                $recentView->delete();
                
                Log::info('Product removed from recent views', [
                    'user_id' => $userId,
                    'product_id' => $id,
                    'product_name' => $productName
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Product removed from recent views successfully.',
                    'product_name' => $productName
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Product not found in recent views.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to remove product from recent views: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'product_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove product from recent views: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all recent views for the authenticated user.
     */
    public function clear()
    {
        try {
            RecentView::forUser(Auth::id())->delete();
            
            return redirect()->route('recent-views.index')
                ->with('success', 'All recent views cleared successfully.');
        } catch (\Exception $e) {
            return redirect()->route('recent-views.index')
                ->with('error', 'Failed to clear recent views.');
        }
    }

    /**
     * Add a product to recent views (called when viewing product details).
     */
    public static function addToRecentViews($productId, $userId = null)
    {
        if (!$userId) {
            $userId = Auth::id();
        }

        if (!$userId) {
            return false;
        }

        try {
            // Check if recent_views table exists
            if (!Schema::hasTable('recent_views')) {
                return false;
            }

            // Update or create recent view record
            RecentView::updateOrCreate(
                [
                    'user_id' => $userId,
                    'product_id' => $productId
                ],
                [
                    'created_at' => now()
                ]
            );

            // Keep only the 50 most recent views per user
            $recentViewIds = RecentView::forUser($userId)
                ->recent()
                ->take(50)
                ->pluck('id');

            RecentView::forUser($userId)
                ->whereNotIn('id', $recentViewIds)
                ->delete();

            Log::info('Product added to recent views', [
                'user_id' => $userId,
                'product_id' => $productId
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to add product to recent views', [
                'user_id' => $userId,
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}