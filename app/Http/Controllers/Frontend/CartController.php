<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Models\CartItem;
use App\Models\SaveForLater;
use App\Models\ProductVariation;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $guestUuid = $request->cookie('guest_cart_uuid');
        
        // Debug logging to identify the issue
        \Log::info("Cart index - User: " . ($user ? $user->id : 'null') . ", Guest UUID: " . ($guestUuid ?: 'null'));
        
        // Get cart items
        $cart = $this->cartService->getOrCreateCart($user, $guestUuid);
        $cartItems = $cart->items()->with(['productVariation.product.images', 'productVariation.stock'])->get();
        
        // Get save for later items
        $saveForLaterUuid = $request->cookie('guest_save_later_uuid') ?: Str::uuid();
        
        // Get initial count before cleanup
        $beforeCleanup = SaveForLater::forUserOrGuest($user, $saveForLaterUuid)
            ->whereHas('productVariation')
            ->count();
        
        // Clean up duplicates first to ensure accurate count
        $this->cleanupSaveForLaterDuplicates($user, $saveForLaterUuid);
        
        // Get clean save for later items (no duplicates should exist after cleanup)
        $saveForLaterItems = SaveForLater::forUserOrGuest($user, $saveForLaterUuid)
            ->with(['productVariation.product.images', 'productVariation.stock'])
            ->whereHas('productVariation') // Only items with valid product variations
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Log for debugging
        $currentUserId = $user ? $user->id : 'guest';
        \Log::info("Save for later debug - Current user: $currentUserId, Before cleanup: $beforeCleanup, After cleanup: " . $saveForLaterItems->count());
        
        // Double check for duplicates in the collection (shouldn't be any after cleanup)
        $uniqueCheck = $saveForLaterItems->unique('product_variation_id');
        if ($saveForLaterItems->count() !== $uniqueCheck->count()) {
            \Log::warning("Found duplicates in collection after cleanup! Total: " . $saveForLaterItems->count() . ", Unique: " . $uniqueCheck->count());
            // Use the unique collection to prevent duplicate rendering
            $saveForLaterItems = $uniqueCheck->values();
        }
        
        // Calculate totals - handle empty cart
        $cartSummary = $this->cartService->cartSummary($cart);
        
        // Ensure all required keys exist
        $cartSummary = array_merge([
            'items' => 0,
            'unique_items' => 0,
            'subtotal' => 0,
            'shipping_cost' => 0,
            'tax_amount' => 0,
            'total' => 0,
            'savings' => 0,
            'free_shipping_eligible' => false,
            'free_shipping_remaining' => 500,
        ], $cartSummary);
        
        // Simple cart validation - check for out of stock items
        $cartIssues = $this->validateCartItems($cartItems);
        
        $response = response()->view('cart.index', compact(
            'cartItems', 
            'saveForLaterItems', 
            'cartSummary', 
            'cartIssues'
        ));
        
        // Set save for later UUID cookie for guests
        if (!$user && !$request->cookie('guest_save_later_uuid')) {
            $response->cookie('guest_save_later_uuid', $saveForLaterUuid, 60 * 24 * 30);
        }
        
        return $response;
    }

    public function add(Request $request)
    {
        $request->validate([
            'variation_id' => 'required|integer',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $quantity = $request->input('quantity', 1);
        $user = Auth::user();
        $uuid = $request->cookie('guest_cart_uuid');
        $cart = $this->cartService->getOrCreateCart($user, $uuid);

        // Server-side validation
        $variation = ProductVariation::find($request->input('variation_id'));
        if (!$variation) {
            return response()->json(['success' => false, 'message' => 'Invalid variation selected'], 422);
        }

        $stock = optional($variation->stock)->quantity ?? 0;
        if ($stock <= 0) {
            return response()->json(['success' => false, 'message' => 'Selected variation is out of stock'], 422);
        }

        // Check if item already exists in cart
        $existingItem = $cart->items()->where('product_variation_id', $request->input('variation_id'))->first();
        $currentCartQuantity = $existingItem ? $existingItem->quantity : 0;
        $totalQuantity = $currentCartQuantity + $quantity;

        // Validate total quantity (existing + new) doesn't exceed stock
        if ($totalQuantity > $stock) {
            if ($currentCartQuantity > 0) {
                $remainingStock = $stock - $currentCartQuantity;
                if ($remainingStock <= 0) {
                    return response()->json([
                        'success' => false, 
                        'message' => "This item is already in your cart with maximum available quantity ({$stock})"
                    ], 422);
                } else {
                    return response()->json([
                        'success' => false, 
                        'message' => "You can only add {$remainingStock} more of this item to your cart (Stock: {$stock}, In Cart: {$currentCartQuantity})"
                    ], 422);
                }
            } else {
                return response()->json([
                    'success' => false, 
                    'message' => "Requested quantity ({$quantity}) exceeds available stock ({$stock})"
                ], 422);
            }
        }

        $this->cartService->addItem($cart, (int)$request->input('variation_id'), (int)$quantity);
        $summary = $this->cartService->cartSummary($cart);

        $response = response()->json([
            'success' => true, 
            'cart_id' => $cart->id, 
            'summary' => $summary,
            'message' => 'Product added to cart successfully!'
        ]);

        // Set guest cart UUID cookie
        if (!$user && $cart->uuid) {
            $response->cookie('guest_cart_uuid', $cart->uuid, 60 * 24 * 30);
        }

        return $response;
    }

    public function update(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|integer',
            'quantity' => 'required|integer|min:1|max:50',
        ]);

        $user = Auth::user();
        $guestUuid = $request->cookie('guest_cart_uuid');
        $cart = $this->cartService->getOrCreateCart($user, $guestUuid);

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('id', $request->cart_item_id)
            ->first();

        if (!$cartItem) {
            return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);
        }

        // Check stock availability
        $stock = optional($cartItem->productVariation->stock)->quantity ?? 0;
        if ($request->quantity > $stock) {
            return response()->json([
                'success' => false, 
                'message' => "Only {$stock} items available in stock"
            ], 422);
        }

        $cartItem->update(['quantity' => $request->quantity]);
        $summary = $this->cartService->cartSummary($cart);

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'message' => 'Cart updated successfully!'
        ]);
    }

    public function remove(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|integer',
        ]);

        $user = Auth::user();
        $guestUuid = $request->cookie('guest_cart_uuid');
        $cart = $this->cartService->getOrCreateCart($user, $guestUuid);

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('id', $request->cart_item_id)
            ->first();

        if (!$cartItem) {
            return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);
        }

        $productName = $cartItem->productVariation->product->name ?? 'Product';
        $cartItem->delete();

        $summary = $this->cartService->cartSummary($cart);

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'message' => "{$productName} removed from cart"
        ]);
    }

    public function saveForLater(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $guestUuid = $request->cookie('guest_cart_uuid');
            $saveForLaterUuid = $request->cookie('guest_save_later_uuid') ?: Str::uuid();
            
            $cart = $this->cartService->getOrCreateCart($user, $guestUuid);
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('id', $request->cart_item_id)
                ->first();

            if (!$cartItem) {
                return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);
            }

            // Check if already saved for later
            $existingSaved = SaveForLater::forUserOrGuest($user, $saveForLaterUuid)
                ->where('product_variation_id', $cartItem->product_variation_id)
                ->first();

            $savedItem = null;
            if ($existingSaved) {
                // Update quantity
                $existingSaved->update([
                    'quantity' => $existingSaved->quantity + $cartItem->quantity
                ]);
                $savedItem = $existingSaved->fresh(['productVariation.product.images']);
            } else {
                // Create new save for later item
                $savedItem = SaveForLater::create([
                    'user_id' => $user?->id,
                    'guest_uuid' => $user ? null : $saveForLaterUuid,
                    'product_variation_id' => $cartItem->product_variation_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                ]);
                $savedItem->load(['productVariation.product.images']);
            }

            $productName = $cartItem->productVariation->product->name ?? 'Product';
            $cartItem->delete();

            $summary = $this->cartService->cartSummary($cart);
            DB::commit();

            // Prepare saved item data for frontend
            $savedItemData = [
                'id' => $savedItem->id,
                'product_name' => $savedItem->productVariation->product->name ?? '',
                'sku' => $savedItem->productVariation->sku ?? '',
                'price' => '₹' . number_format($savedItem->price, 2) . ' × ' . $savedItem->quantity,
                'image_url' => $savedItem->productVariation->product->images->first() 
                    ? \Illuminate\Support\Facades\Storage::url($savedItem->productVariation->product->images->first()->path) 
                    : null,
                'alt_text' => $savedItem->productVariation->product->name ?? '',
                'quantity' => $savedItem->quantity
            ];

            $response = response()->json([
                'success' => true,
                'summary' => $summary,
                'saved_item' => $savedItemData,
                'message' => "{$productName} saved for later"
            ]);

            // Set save for later UUID cookie for guests
            if (!$user) {
                $response->cookie('guest_save_later_uuid', $saveForLaterUuid, 60 * 24 * 30);
            }

            return $response;

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Failed to save item'], 500);
        }
    }

    public function moveToCart(Request $request)
    {
        $request->validate([
            'save_item_id' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $guestUuid = $request->cookie('guest_cart_uuid');
            $saveForLaterUuid = $request->cookie('guest_save_later_uuid');

            $saveItem = SaveForLater::forUserOrGuest($user, $saveForLaterUuid)
                ->where('id', $request->save_item_id)
                ->first();

            if (!$saveItem) {
                return response()->json(['success' => false, 'message' => 'Saved item not found'], 404);
            }

            // Check stock
            $stock = optional($saveItem->productVariation->stock)->quantity ?? 0;
            if ($stock <= 0) {
                return response()->json(['success' => false, 'message' => 'Item is currently out of stock'], 422);
            }

            $quantity = min($saveItem->quantity, $stock);
            $cart = $this->cartService->getOrCreateCart($user, $guestUuid);

            // Add to cart
            $this->cartService->addItem($cart, $saveItem->product_variation_id, $quantity);

            // Get the newly added cart item for frontend
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_variation_id', $saveItem->product_variation_id)
                ->with(['productVariation.product.images', 'productVariation.stock'])
                ->first();

            $productName = $saveItem->productVariation->product->name ?? 'Product';
            $saveItem->delete();

            $summary = $this->cartService->cartSummary($cart);
            DB::commit();

            // Prepare cart item data for frontend
            $cartItemData = null;
            if ($cartItem) {
                $cartItemData = [
                    'id' => $cartItem->id,
                    'product_name' => $cartItem->productVariation->product->name ?? '',
                    'sku' => $cartItem->productVariation->sku ?? '',
                    'price' => '₹' . number_format($cartItem->price, 2),
                    'quantity' => $cartItem->quantity,
                    'image_url' => $cartItem->productVariation->product->images->first() 
                        ? \Illuminate\Support\Facades\Storage::url($cartItem->productVariation->product->images->first()->path) 
                        : null,
                    'alt_text' => $cartItem->productVariation->product->name ?? '',
                    'stock' => optional($cartItem->productVariation->stock)->quantity ?? 0
                ];
            }

            return response()->json([
                'success' => true,
                'summary' => $summary,
                'cart_item' => $cartItemData,
                'message' => "{$productName} moved to cart"
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Failed to move item to cart'], 500);
        }
    }

    public function removeSaved(Request $request)
    {
        $request->validate([
            'save_item_id' => 'required|integer',
        ]);

        $user = Auth::user();
        $saveForLaterUuid = $request->cookie('guest_save_later_uuid');

        $saveItem = SaveForLater::forUserOrGuest($user, $saveForLaterUuid)
            ->where('id', $request->save_item_id)
            ->first();

        if (!$saveItem) {
            return response()->json(['success' => false, 'message' => 'Saved item not found'], 404);
        }

        $productName = $saveItem->productVariation->product->name ?? 'Product';
        $saveItem->delete();

        return response()->json([
            'success' => true,
            'message' => "{$productName} removed from saved items"
        ]);
    }

    public function moveToWishlist(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Please login to add items to wishlist'], 401);
        }

        $request->validate([
            'cart_item_id' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $guestUuid = $request->cookie('guest_cart_uuid');
            $cart = $this->cartService->getOrCreateCart($user, $guestUuid);

            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('id', $request->cart_item_id)
                ->first();

            if (!$cartItem) {
                return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);
            }

            $productId = $cartItem->productVariation->product->id;

            // Check if already in wishlist
            $existingWishlist = Wishlist::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->first();

            if (!$existingWishlist) {
                Wishlist::create([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                ]);
            }

            $productName = $cartItem->productVariation->product->name ?? 'Product';
            $cartItem->delete();

            $summary = $this->cartService->cartSummary($cart);
            DB::commit();

            return response()->json([
                'success' => true,
                'summary' => $summary,
                'message' => "{$productName} moved to wishlist"
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Failed to move item to wishlist'], 500);
        }
    }
    /**
     * Validate cart items for stock and pricing issues
     */
    private function validateCartItems($cartItems): array
    {
        $issues = [];
        
        foreach ($cartItems as $item) {
            $variation = $item->productVariation;
            $stock = $variation->stock ?? null;
            
            if (!$variation) {
                $issues[] = [
                    'item_id' => $item->id,
                    'issue' => 'product_not_found',
                    'message' => 'Product "' . ($item->productVariation->product->name ?? 'Unknown') . '" is no longer available'
                ];
                continue;
            }
            
            if (!$stock || $stock->quantity <= 0) {
                $issues[] = [
                    'item_id' => $item->id,
                    'issue' => 'out_of_stock',
                    'message' => 'Product "' . $variation->product->name . '" is out of stock'
                ];
                continue;
            }
            
            if ($item->quantity > $stock->quantity) {
                $issues[] = [
                    'item_id' => $item->id,
                    'issue' => 'insufficient_stock',
                    'message' => 'Only ' . $stock->quantity . ' items available for "' . $variation->product->name . '"',
                    'available_quantity' => $stock->quantity
                ];
            }
            
            // Check if price has changed significantly
            $currentPrice = $variation->price;
            $priceDifference = abs($currentPrice - $item->price);
            if ($priceDifference > 0.01) { // More than 1 paisa difference
                $issues[] = [
                    'item_id' => $item->id,
                    'issue' => 'price_changed',
                    'message' => 'Price for "' . $variation->product->name . '" has changed from ₹' . number_format($item->price, 2) . ' to ₹' . number_format($currentPrice, 2),
                    'old_price' => $item->price,
                    'new_price' => $currentPrice
                ];
            }
        }
        
        return $issues;
    }
    
    /**
     * Sync cart and save for later counts
     */
    public function syncCounts(Request $request)
    {
        $user = Auth::user();
        $guestUuid = $request->cookie('guest_cart_uuid');
        $saveForLaterUuid = $request->cookie('guest_save_later_uuid');
        
        // Get actual counts from database
        $cart = $this->cartService->getOrCreateCart($user, $guestUuid);
        $cartCount = $cart->items()->count();
        
        $saveForLaterCount = SaveForLater::forUserOrGuest($user, $saveForLaterUuid)
            ->whereHas('productVariation')
            ->count();
        
        // Clean up duplicates
        $this->cleanupSaveForLaterDuplicates($user, $saveForLaterUuid);
        
        // Get updated count after cleanup
        $cleanedSaveForLaterCount = SaveForLater::forUserOrGuest($user, $saveForLaterUuid)
            ->whereHas('productVariation')
            ->count();
        
        return response()->json([
            'success' => true,
            'cart_count' => $cartCount,
            'save_for_later_count' => $cleanedSaveForLaterCount,
            'cleaned_duplicates' => $saveForLaterCount - $cleanedSaveForLaterCount
        ]);
    }
    
    /**
     * Clean up duplicate save for later items
     */
    private function cleanupSaveForLaterDuplicates($user, $guestUuid)
    {
        try {
            $query = SaveForLater::forUserOrGuest($user, $guestUuid);
            
            // Get duplicates grouped by product_variation_id
            $duplicates = $query->select('product_variation_id', DB::raw('COUNT(*) as count'))
                ->groupBy('product_variation_id')
                ->having('count', '>', 1)
                ->get();
            
            foreach ($duplicates as $duplicate) {
                // Keep the latest record, delete the rest
                $items = SaveForLater::forUserOrGuest($user, $guestUuid)
                    ->where('product_variation_id', $duplicate->product_variation_id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                // Skip the first (latest) item and delete the rest
                $items->skip(1)->each(function ($item) {
                    $item->delete();
                });
            }
            
            // Also clean up items with invalid product variations
            SaveForLater::forUserOrGuest($user, $guestUuid)
                ->whereDoesntHave('productVariation')
                ->delete();
                
        } catch (\Exception $e) {
            // Log error but don't break the main functionality
            \Log::error('Save for later cleanup failed: ' . $e->getMessage());
        }
    }
}