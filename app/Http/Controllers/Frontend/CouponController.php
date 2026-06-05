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

        $cart->load('items');
        $cartSubtotal = $cart->subtotal;
        $validationError = $coupon->getValidationError($cartSubtotal, $user?->id);

        if ($validationError) {
            return response()->json(['success' => false, 'message' => $validationError]);
        }

        $cart->applyCoupon($coupon);
        $discountAmount = $cart->discount_amount;

        $pincode = $request->input('pincode');
        $summary = $this->cartService->cartSummary($cart, $pincode);

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

        $cart->removeCoupon();

        $pincode = $request->input('pincode');
        $summary = $this->cartService->cartSummary($cart, $pincode);

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed successfully',
            'summary' => $summary
        ]);
    }
}
