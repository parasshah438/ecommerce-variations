<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['order', 'user']);

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('payment_status', $request->status);
        }

        // Filter by gateway
        if ($request->has('gateway') && $request->gateway != '') {
            $query->where('gateway', $request->gateway);
        }

        // Search by payment ID or order ID
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('payment_id', 'like', "%{$search}%")
                  ->orWhere('gateway_payment_id', 'like', "%{$search}%")
                  ->orWhereHas('order', function($orderQuery) use ($search) {
                      $orderQuery->where('id', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->latest()->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $payments,
            'summary' => [
                'total_payments' => Payment::count(),
                'successful_payments' => Payment::successful()->count(),
                'failed_payments' => Payment::failed()->count(),
                'pending_payments' => Payment::pending()->count(),
                'total_amount' => Payment::successful()->sum('amount'),
            ]
        ]);
    }

    public function show(Payment $payment)
    {
        $payment->load(['order.items.variation.product', 'user']);
        
        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }
}
