<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('message', 'Please login to view your wishlist');
        }

        $perPage = 10;
        $wishlistItems = Wishlist::where('user_id', Auth::id())
            ->with([
                'product.images',
                'product.brand',
                'product.category',
                'product.variations.stock'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Add stock information for each product
        $wishlistItems->getCollection()->transform(function ($item) {
            $product = $item->product;
            
            // Get the best available variation (in stock if possible)
            $bestVariation = $product->variations()
                ->with('stock')
                ->orderBy('price', 'asc')
                ->get()
                ->sortByDesc(function ($variation) {
                    return optional($variation->stock)->quantity > 0 ? 1 : 0;
                })
                ->first();

            $item->best_variation = $bestVariation;
            $item->is_in_stock = $bestVariation && optional($bestVariation->stock)->quantity > 0;
            $item->available_stock = $bestVariation ? optional($bestVariation->stock)->quantity : 0;
            $item->best_price = $bestVariation ? $bestVariation->price : $product->price;

            return $item;
        });

        $totalCount = Wishlist::where('user_id', Auth::id())->count();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('wishlist._items', compact('wishlistItems'))->render(),
                'has_more' => $wishlistItems->hasMorePages(),
                'current_page' => $wishlistItems->currentPage(),
                'total' => $totalCount
            ]);
        }

        return view('wishlist.index', compact('wishlistItems', 'totalCount'));
    }

    public function loadMore(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $page = $request->get('page', 2);
        $perPage = 10;

        $wishlistItems = Wishlist::where('user_id', Auth::id())
            ->with([
                'product.images',
                'product.brand',
                'product.category',
                'product.variations.stock'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Add stock information
        $wishlistItems->getCollection()->transform(function ($item) {
            $product = $item->product;
            
            $bestVariation = $product->variations()
                ->with('stock')
                ->orderBy('price', 'asc')
                ->get()
                ->sortByDesc(function ($variation) {
                    return optional($variation->stock)->quantity > 0 ? 1 : 0;
                })
                ->first();

            $item->best_variation = $bestVariation;
            $item->is_in_stock = $bestVariation && optional($bestVariation->stock)->quantity > 0;
            $item->available_stock = $bestVariation ? optional($bestVariation->stock)->quantity : 0;
            $item->best_price = $bestVariation ? $bestVariation->price : $product->price;

            return $item;
        });

        return response()->json([
            'success' => true,
            'html' => view('wishlist._items', compact('wishlistItems'))->render(),
            'has_more' => $wishlistItems->hasMorePages(),
            'current_page' => $wishlistItems->currentPage()
        ]);
    }

    public function toggle(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        try {
            $userId = Auth::id();
            $productId = $request->product_id;

            $wishlistItem = Wishlist::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();

            if ($wishlistItem) {
                $wishlistItem->delete();
                $added = false;
                $message = 'Removed from wishlist';
            } else {
                Wishlist::create([
                    'user_id' => $userId,
                    'product_id' => $productId
                ]);
                $added = true;
                $message = 'Added to wishlist';
            }

            $totalCount = Wishlist::where('user_id', $userId)->count();

            return response()->json([
                'success' => true,
                'added' => $added,
                'message' => $message,
                'wishlist_count' => $totalCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Wishlist toggle error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update wishlist. Please try again.'
            ], 500);
        }
    }

    public function remove(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $request->validate([
            'wishlist_id' => 'required|integer'
        ]);

        $wishlistItem = Wishlist::where('user_id', Auth::id())
            ->where('id', $request->wishlist_id)
            ->first();

        if (!$wishlistItem) {
            return response()->json(['success' => false, 'message' => 'Wishlist item not found'], 404);
        }

        $productName = $wishlistItem->product->name ?? 'Product';
        $wishlistItem->delete();

        $totalCount = Wishlist::where('user_id', Auth::id())->count();

        return response()->json([
            'success' => true,
            'message' => "{$productName} removed from wishlist",
            'wishlist_count' => $totalCount
        ]);
    }

    public function removeMultiple(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $request->validate([
            'wishlist_ids' => 'required|array',
            'wishlist_ids.*' => 'integer'
        ]);

        $deletedCount = Wishlist::where('user_id', Auth::id())
            ->whereIn('id', $request->wishlist_ids)
            ->delete();

        $totalCount = Wishlist::where('user_id', Auth::id())->count();

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} items removed from wishlist",
            'wishlist_count' => $totalCount
        ]);
    }

    public function clearAll(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $deletedCount = Wishlist::where('user_id', Auth::id())->delete();

        return response()->json([
            'success' => true,
            'message' => "All {$deletedCount} items removed from wishlist",
            'wishlist_count' => 0
        ]);
    }

    public function moveToCart(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $request->validate([
            'wishlist_id' => 'required|integer',
            'quantity' => 'nullable|integer|min:1|max:10'
        ]);

        DB::beginTransaction();
        try {
            $wishlistItem = Wishlist::where('user_id', Auth::id())
                ->where('id', $request->wishlist_id)
                ->with('product.variations.stock')
                ->first();

            if (!$wishlistItem) {
                return response()->json(['success' => false, 'message' => 'Wishlist item not found'], 404);
            }

            // Get best available variation
            $bestVariation = $wishlistItem->product->variations()
                ->with('stock')
                ->orderBy('price', 'asc')
                ->get()
                ->sortByDesc(function ($variation) {
                    return optional($variation->stock)->quantity > 0 ? 1 : 0;
                })
                ->first();

            if (!$bestVariation) {
                return response()->json(['success' => false, 'message' => 'No variations available for this product'], 404);
            }

            $stock = optional($bestVariation->stock)->quantity ?? 0;
            if ($stock <= 0) {
                return response()->json(['success' => false, 'message' => 'Product is currently out of stock'], 422);
            }

            $quantity = min($request->get('quantity', 1), $stock);
            
            // Add to cart
            $cart = $this->cartService->getOrCreateCart(Auth::user(), null);
            $this->cartService->addItem($cart, $bestVariation->id, $quantity);

            // Remove from wishlist
            $productName = $wishlistItem->product->name;
            $wishlistItem->delete();

            $cartSummary = $this->cartService->cartSummary($cart);
            $wishlistCount = Wishlist::where('user_id', Auth::id())->count();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$productName} moved to cart",
                'cart_summary' => $cartSummary,
                'wishlist_count' => $wishlistCount
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Failed to move item to cart'], 500);
        }
    }

    public function moveAllToCart(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        DB::beginTransaction();
        try {
            $wishlistItems = Wishlist::where('user_id', Auth::id())
                ->with('product.variations.stock')
                ->get();

            if ($wishlistItems->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Your wishlist is empty'], 404);
            }

            $cart = $this->cartService->getOrCreateCart(Auth::user(), null);
            $movedCount = 0;
            $skippedCount = 0;

            foreach ($wishlistItems as $wishlistItem) {
                // Get best available variation
                $bestVariation = $wishlistItem->product->variations()
                    ->with('stock')
                    ->orderBy('price', 'asc')
                    ->get()
                    ->sortByDesc(function ($variation) {
                        return optional($variation->stock)->quantity > 0 ? 1 : 0;
                    })
                    ->first();

                if ($bestVariation && optional($bestVariation->stock)->quantity > 0) {
                    $this->cartService->addItem($cart, $bestVariation->id, 1);
                    $wishlistItem->delete();
                    $movedCount++;
                } else {
                    $skippedCount++;
                }
            }

            $cartSummary = $this->cartService->cartSummary($cart);
            $wishlistCount = Wishlist::where('user_id', Auth::id())->count();

            DB::commit();

            $message = "{$movedCount} items moved to cart";
            if ($skippedCount > 0) {
                $message .= " ({$skippedCount} items skipped due to stock unavailability)";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'cart_summary' => $cartSummary,
                'wishlist_count' => $wishlistCount,
                'moved_count' => $movedCount,
                'skipped_count' => $skippedCount
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Failed to move items to cart'], 500);
        }
    }
}