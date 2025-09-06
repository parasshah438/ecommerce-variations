<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function apply(Request $request)
    {
        $code = $request->input('code');
        $coupon = Coupon::where('code', $code)->first();
        if (! $coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon']);
        }

        return response()->json(['success' => true, 'coupon' => $coupon]);
    }
}
