<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\CartService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use App\Models\Cart;

class CheckoutController extends Controller
{
    public function index()
    {
        return view('checkout.index');
    }

    public function placeOrder(Request $request, CartService $cartService)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $request->validate([
            'address_id' => 'nullable|exists:addresses,id',
            'name' => 'required_without:address_id|string',
            'phone' => 'required_without:address_id|string',
            'address_line' => 'required_without:address_id|string',
            'city' => 'required_without:address_id|string',
            'state' => 'required_without:address_id|string',
            'zip' => 'required_without:address_id|string',
        ]);

        $cart = Cart::where('user_id', $user->id)->with('items.productVariation.stock')->first();
        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->back()->with('error', 'Your cart is empty');
        }

        // Validate stock for all items (but don't reserve yet)
        foreach ($cart->items as $item) {
            $stockQty = optional($item->productVariation->stock)->quantity ?? 0;
            if ($stockQty < $item->quantity) {
                return redirect()->back()->with('error', 'Insufficient stock for SKU: ' . $item->productVariation->sku . ". Available: {$stockQty}, Required: {$item->quantity}");
            }
        }

        DB::beginTransaction();
        try {
            // Create address if provided
            if ($request->filled('address_id')) {
                $address = Address::find($request->input('address_id'));
            } else {
                $address = Address::create([
                    'user_id' => $user->id,
                    'label' => $request->input('label', 'Home'),
                    'name' => $request->input('name'),
                    'phone' => $request->input('phone'),
                    'address_line' => $request->input('address_line'),
                    'city' => $request->input('city'),
                    'state' => $request->input('state'),
                    'zip' => $request->input('zip'),
                    'country' => $request->input('country', 'India'),
                ]);
            }

            $total = 0;
            foreach ($cart->items as $item) {
                $total += $item->price * $item->quantity;
            }

            // Create order in PENDING status (stock not yet reserved)
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $address->id,
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_PENDING,
                'total' => $total,
                'payment_method' => 'cod',
            ]);

            // Create order items (NO stock deduction yet)
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variation_id' => $item->product_variation_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);
            }

            // Clear cart (order is placed but stock not reserved)
            $cart->items()->delete();
            $cart->delete();

            DB::commit();

            // Simulate payment process - for demo, auto-confirm COD orders
            if ($order->payment_method === 'cod') {
                // For COD, immediately confirm the order (this reserves stock)
                $orderService = app(\App\Services\OrderService::class);
                try {
                    $orderService->confirmOrder($order);
                    $message = 'Order placed and confirmed successfully! Stock has been reserved.';
                } catch (\Exception $e) {
                    // If stock reservation fails, cancel the order
                    $orderService->cancelOrder($order, 'Stock unavailable during confirmation', false);
                    return redirect()->back()->with('error', 'Order could not be confirmed: ' . $e->getMessage());
                }
            } else {
                $message = 'Order placed successfully! Stock will be reserved after payment confirmation.';
            }

            return redirect()->route('orders.show', $order->id)->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return redirect()->back()->with('error', 'Could not place order: ' . $e->getMessage());
        }
    }
}
