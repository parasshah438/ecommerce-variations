<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50'
        ]);

        $code = strtoupper(trim($request->input('code')));
        
        // Find the coupon
        $coupon = Coupon::where('code', $code)->first();
        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon code']);
        }

        // Get or create cart
        $user = Auth::user();
        $guestUuid = $request->cookie('guest_cart_uuid');
        $cart = $this->cartService->getOrCreateCart($user, $guestUuid);

        // Check if cart is empty
        if ($cart->items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Cart is empty']);
        }

        // Validate coupon using model methods
        $cartSubtotal = $cart->subtotal;
        $validationError = $coupon->getValidationError($cartSubtotal);
        
        if ($validationError) {
            return response()->json(['success' => false, 'message' => $validationError]);
        }

        // Calculate discount using coupon model
        $discountAmount = $coupon->calculateDiscount($cartSubtotal);
        
        // Business rule: Check if discount results in very low total
        $subtotal = $cart->subtotal;
        $finalTotal = $subtotal - $discountAmount;
        
        // Optional business rule: Prevent free or very cheap orders
        /*
        if ($finalTotal < 50) { // Minimum ₹50 order value
            return response()->json([
                'success' => false, 
                'message' => 'This coupon would make your order total too low. Minimum order value is ₹50.'
            ]);
        }
        */
        
        // Apply the coupon
        $cart->coupon_id = $coupon->id;
        $cart->discount_amount = $discountAmount;
        $cart->save();

        // Get updated cart summary
        $summary = $this->cartService->cartSummary($cart);

        return response()->json([
            'success' => true, 
            'message' => 'Coupon applied successfully!',
            'coupon' => [
                'code' => $coupon->code,
                'discount' => $coupon->discount,
                'type' => $coupon->type,
                'discount_amount' => $discountAmount
            ],
            'summary' => $summary
        ]);
    }

    public function remove(Request $request)
    {
        $user = Auth::user();
        $guestUuid = $request->cookie('guest_cart_uuid');
        $cart = $this->cartService->getOrCreateCart($user, $guestUuid);

        if (!$cart->coupon_id) {
            return response()->json(['success' => false, 'message' => 'No coupon applied']);
        }

        $cart->coupon_id = null;
        $cart->discount_amount = 0;
        $cart->save();

        $summary = $this->cartService->cartSummary($cart);

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed successfully',
            'summary' => $summary
        ]);
    }

    private function calculateDiscount($cart, $coupon)
    {
        $subtotal = $cart->subtotal;
        
        if ($coupon->type === 'percentage') {
            $discount = round(($subtotal * $coupon->discount) / 100, 2);
            // Optional: Cap percentage discounts at 90% of subtotal
            // return min($discount, $subtotal * 0.9);
            return $discount;
        }
        
        // Fixed discount logic with business rules
        if ($coupon->type === 'fixed') {
            // Option A: Allow full discount (current behavior)
            return min($coupon->discount, $subtotal);
            
            // Option B: Prevent discount equal to or greater than cart total
            // Uncomment the lines below to require minimum ₹50 payment
            /*
            $maxDiscount = max($subtotal - 50, 0); // Minimum ₹50 payment required
            return min($coupon->discount, $maxDiscount);
            */
            
            // Option C: Cap fixed discounts at 90% of cart total
            /*
            $maxDiscount = $subtotal * 0.9; // Maximum 90% discount
            return min($coupon->discount, $maxDiscount);
            */
        }
        
        return 0;
    }
}
