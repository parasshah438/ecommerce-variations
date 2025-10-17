<?php

// Additional Security Enhancements for CartController

namespace App\Http\Controllers\Frontend;

class CartController extends Controller 
{
    public function add(Request $request)
    {
        // ✅ Already implemented - Basic validation
        $request->validate([
            'variation_id' => 'required|integer',
            'quantity' => 'nullable|integer|min:1|max:10', // Add max limit
        ]);

        // ✅ Already implemented - Variation existence check
        $variation = ProductVariation::find($request->input('variation_id'));
        if (!$variation) {
            return response()->json(['success' => false, 'message' => 'Invalid variation'], 422);
        }

        // 🆕 ENHANCEMENT 1: Check if product is active
        if (!$variation->product->active) {
            return response()->json(['success' => false, 'message' => 'Product is not available'], 422);
        }

        // ✅ Already implemented - Stock validation
        $stock = optional($variation->stock)->quantity ?? 0;
        if ($stock <= 0) {
            return response()->json(['success' => false, 'message' => 'Out of stock'], 422);
        }

        // 🆕 ENHANCEMENT 2: Rate limiting (add to middleware)
        // This would prevent rapid-fire requests

        // 🆕 ENHANCEMENT 3: User-specific quantity limits
        $dailyLimit = 50; // Max 50 items per user per day
        $todayTotal = CartItem::where('user_id', auth()->id())
            ->whereDate('created_at', today())
            ->sum('quantity');
        
        if ($todayTotal + $request->quantity > $dailyLimit) {
            return response()->json(['success' => false, 'message' => 'Daily purchase limit exceeded'], 422);
        }

        // 🆕 ENHANCEMENT 4: Log suspicious activity
        if ($request->quantity > 5) {
            \Log::warning('High quantity cart add attempt', [
                'user_id' => auth()->id(),
                'variation_id' => $request->variation_id,
                'quantity' => $request->quantity,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }

        // ✅ Already implemented - Continue with existing logic...
    }

    // 🆕 ENHANCEMENT 5: Add middleware for API rate limiting
    public function __construct()
    {
        $this->middleware('throttle:cart-add,10,1')->only('add'); // 10 requests per minute
    }
}