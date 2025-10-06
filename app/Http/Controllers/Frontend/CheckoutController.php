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
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Load user with addresses and cart
        $user->load(['addresses', 'cart.items.productVariation.product', 'cart.items.productVariation.images']);
        
        // Check if cart exists and has items
        if (!$user->cart || $user->cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty. Add some items before checkout.');
        }

        return view('checkout.index');
    }

    public function placeOrder(Request $request, CartService $cartService)
    {
        \Log::info('PlaceOrder method called', $request->all());
        
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        \Log::info('User authenticated', ['user_id' => $user->id]);

        $request->validate([
            'address_id' => 'nullable|exists:addresses,id',
            'name' => 'required_without:address_id|string|nullable',
            'phone' => 'required_without:address_id|string|nullable',
            'address_line' => 'required_without:address_id|string|nullable',
            'city' => 'required_without:address_id|string|nullable',
            'state' => 'required_without:address_id|string|nullable',
            'zip' => 'required_without:address_id|string|nullable',
        ]);

        \Log::info('Validation passed');

        $cart = Cart::where('user_id', $user->id)->with('items.productVariation.stock')->first();
        if (! $cart || $cart->items->isEmpty()) {
            \Log::warning('Cart is empty or not found', ['user_id' => $user->id]);
            return redirect()->back()->with('error', 'Your cart is empty');
        }

        \Log::info('Cart found', [
            'cart_id' => $cart->id,
            'items_count' => $cart->items->count()
        ]);

        // Validate stock for all items (but don't reserve yet)
        foreach ($cart->items as $item) {
            $stockQty = optional($item->productVariation->stock)->quantity ?? 0;
            \Log::info('Checking stock', [
                'sku' => $item->productVariation->sku,
                'required' => $item->quantity,
                'available' => $stockQty
            ]);
            
            if ($stockQty < $item->quantity) {
                \Log::warning('Insufficient stock', [
                    'sku' => $item->productVariation->sku,
                    'required' => $item->quantity,
                    'available' => $stockQty
                ]);
                return redirect()->back()->with('error', 'Insufficient stock for SKU: ' . $item->productVariation->sku . ". Available: {$stockQty}, Required: {$item->quantity}");
            }
        }

        \Log::info('Stock validation passed, starting transaction');

        DB::beginTransaction();
        try {
            \Log::info('Transaction started');
            
            // Create address if provided
            if ($request->filled('address_id')) {
                $address = Address::find($request->input('address_id'));
                \Log::info('Using existing address', ['address_id' => $address->id]);
            } else {
                \Log::info('Creating new address');
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
                \Log::info('New address created', ['address_id' => $address->id]);
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
                \Log::info('Skipping order confirmation for debugging');
                $message = 'Order placed successfully! (Confirmation skipped for debugging)';
                
                // TEMPORARILY DISABLED FOR DEBUGGING
                /*
                // For COD, immediately confirm the order (this reserves stock)
                $orderService = app(\App\Services\OrderService::class);
                try {
                    \Log::info('Attempting to confirm order', ['order_id' => $order->id]);
                    $orderService->confirmOrder($order);
                    \Log::info('Order confirmed successfully', ['order_id' => $order->id]);
                    $message = 'Order placed and confirmed successfully! Stock has been reserved.';
                } catch (\Exception $e) {
                    \Log::error('Order confirmation failed', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                    // If stock reservation fails, cancel the order
                    $orderService->cancelOrder($order, 'Stock unavailable during confirmation', false);
                    return redirect()->back()->with('error', 'Order could not be confirmed: ' . $e->getMessage());
                }
                */
            } else {
                $message = 'Order placed successfully! Stock will be reserved after payment confirmation.';
            }

            \Log::info('Order created successfully', [
                'order_id' => $order->id,
                'message' => $message
            ]);

            \Log::info('About to redirect to success page', [
                'route' => 'checkout.success',
                'order_id' => $order->id
            ]);

            return redirect()->route('checkout.success', $order->id)->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order placement failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            report($e);
            return redirect()->back()->with('error', 'Could not place order: ' . $e->getMessage());
        }
    }

    public function success(Order $order)
    {
        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to order.');
        }

        // Load order with related data - load without attribute_values for now
        $order->load([
            'items.variation.product', 
            'address'
        ]);

        // Load attribute values manually for each variation
        foreach ($order->items as $item) {
            // The attribute_values accessor will handle loading the values
            $item->variation->append('attribute_values');
        }

        return view('checkout.success', compact('order'));
    }
}
