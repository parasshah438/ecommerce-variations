<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CouponService
{
    /**
     * Sum cart line items (price × quantity).
     */
    public function cartSubtotal(Cart $cart): float
    {
        $cart->loadMissing('items');

        return (float) $cart->items->sum(function ($item) {
            return ($item->price ?? 0) * ($item->quantity ?? 0);
        });
    }

    /**
     * Refresh discount on cart or remove coupon if it is no longer valid.
     */
    public function recalculateCartCoupon(Cart $cart): void
    {
        $cart->loadMissing(['coupon', 'items']);

        if (!$cart->coupon_id) {
            if ((float) ($cart->discount_amount ?? 0) !== 0.0) {
                $cart->discount_amount = 0;
                $cart->save();
            }

            return;
        }

        if ($cart->items->isEmpty()) {
            $cart->removeCoupon();

            return;
        }

        $coupon = $cart->coupon;
        if (!$coupon) {
            $cart->removeCoupon();

            return;
        }

        $subtotal = $this->cartSubtotal($cart);

        if ($coupon->getValidationError($subtotal, $cart->user_id)) {
            $cart->removeCoupon();

            return;
        }

        $discount = round($coupon->calculateDiscount($subtotal), 2);

        if (
            (float) $cart->discount_amount !== $discount
            || (int) $cart->coupon_id !== (int) $coupon->id
        ) {
            $cart->coupon_id = $coupon->id;
            $cart->discount_amount = $discount;
            $cart->save();
        }
    }

    /**
     * Re-validate coupon before checkout. Returns an error message or null if OK.
     */
    public function validateForCheckout(Cart $cart): ?string
    {
        $this->recalculateCartCoupon($cart);
        $cart->refresh();
        $cart->loadMissing(['coupon', 'items']);

        if ($cart->items->isEmpty()) {
            return null;
        }

        if (!$cart->coupon_id) {
            return null;
        }

        $coupon = $cart->coupon;
        if (!$coupon) {
            return 'The applied coupon is no longer valid and was removed. Please review your cart.';
        }

        return $coupon->getValidationError($this->cartSubtotal($cart), $cart->user_id);
    }

    /**
     * Increment coupon used_count once per completed order (with row lock).
     */
    public function recordCouponUsage(?string $couponCode): void
    {
        if (!$couponCode) {
            return;
        }

        DB::transaction(function () use ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->lockForUpdate()->first();

            if (!$coupon) {
                return;
            }

            if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
                Log::warning('Coupon usage limit already reached at checkout', [
                    'code' => $coupon->code,
                    'used_count' => $coupon->used_count,
                    'usage_limit' => $coupon->usage_limit,
                ]);

                return;
            }

            $coupon->increment('used_count');
        });
    }
}
