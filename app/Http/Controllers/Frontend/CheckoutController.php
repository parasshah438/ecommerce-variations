<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\RazorpayService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Payment;

class CheckoutController extends Controller
{
    protected $cartService;

    protected $couponService;

    public function __construct(CartService $cartService, CouponService $couponService)
    {
        $this->cartService = $cartService;
        $this->couponService = $couponService;
    }

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

        // Get cart summary — use default address pincode if available for accurate shipping
        $defaultAddress = $user->addresses()->where('is_default', true)->first()
                       ?? $user->addresses()->latest()->first();
        $pincode = $defaultAddress->zip ?? $defaultAddress->postal_code ?? null;
        $cartSummary = $this->cartService->cartSummary($user->cart, $pincode);

        return view('checkout.index', compact('cartSummary'));
    }

    /**
     * Recalculate checkout summary for a pincode (live shipping refresh).
     */
    public function refreshSummary(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $request->validate([
            'pincode' => 'nullable|string|max:10',
        ]);

        $cart = Cart::where('user_id', $user->id)
            ->with(['items.productVariation.stock', 'coupon'])
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 422);
        }

        $pincode = $request->input('pincode');
        $summary = $this->cartService->cartSummary($cart, $pincode);

        return response()->json([
            'success' => true,
            'summary' => $summary,
        ]);
    }

    public function placeOrder(Request $request)
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

        $cart = Cart::where('user_id', $user->id)->with(['items.productVariation.stock', 'coupon'])->first();
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
                    'alternate_phone' => $request->input('alternate_phone'),
                    'address_line' => $request->input('address_line'),
                    'city' => $request->input('city'),
                    'state' => $request->input('state'),
                    'zip' => $request->input('zip'),
                    'country' => $request->input('country', 'India'),
                    'type' => $request->input('type', 'home'),
                    'is_default' => $request->input('is_default', false),
                    'delivery_instructions' => $request->input('delivery_instructions'),
                    'landmark' => $request->input('landmark'),
                ]);
                \Log::info('New address created', ['address_id' => $address->id]);
            }

            $couponError = $this->couponService->validateForCheckout($cart);
            if ($couponError) {
                throw new \RuntimeException($couponError);
            }

            // Get cart summary with coupon information — use the submitted pincode for accurate shipping
            $pincode = $address->zip ?? $address->postal_code ?? $request->input('zip');
            $cartSummary = $this->cartService->cartSummary($cart, $pincode);

            if ($cartSummary['coupon'] && $cartSummary['discount_amount'] <= 0) {
                throw new \RuntimeException('Coupon discount could not be applied. Please update your cart and try again.');
            }

            // Create order in PENDING status (stock not yet reserved)
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $address->id,
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_PENDING,
                'subtotal' => $cartSummary['subtotal'],
                'tax_amount' => $cartSummary['tax_amount'],
                'tax_rate' => $cartSummary['tax_rate'],
                'tax_name' => $cartSummary['tax_name'],
                'shipping_cost' => $cartSummary['shipping_cost'],
                'total' => $cartSummary['total'],
                'coupon_code' => $cartSummary['coupon'] ? $cartSummary['coupon']['code'] : null,
                'coupon_title' => $cartSummary['coupon'] ? $cartSummary['coupon']['code'] : null,
                'coupon_discount' => $cartSummary['discount_amount'],
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

            // Create payment record for COD
            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'payment_id' => Payment::generatePaymentId(),
                'gateway' => Payment::GATEWAY_COD,
                'status' => Payment::STATUS_PENDING,
                'amount' => $cartSummary['total'],
                'currency' => 'INR',
                'payment_method' => 'cod',
                'payment_status' => Payment::PAYMENT_STATUS_PENDING,
                'receipt_number' => 'order_' . $order->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'order_id' => $order->id,
                    'created_via' => 'checkout_form',
                    'payment_type' => 'cash_on_delivery'
                ]
            ]);

            $this->couponService->recordCouponUsage($order->coupon_code);

            // Clear cart (order is placed but stock not reserved)
            $cart->items()->delete();
            $cart->delete();

            DB::commit();

            // For COD, immediately confirm the order (reserves stock + triggers ShipRocket)
            if ($order->payment_method === 'cod') {
                \Log::info('COD order placed, confirming immediately', [
                    'order_id' => $order->id,
                    'payment_id' => $payment->payment_id
                ]);
                $orderService = app(\App\Services\OrderService::class);
                try {
                    \Log::info('Attempting to confirm COD order', ['order_id' => $order->id]);
                    $orderService->confirmOrder($order);
                    \Log::info('COD order confirmed successfully', ['order_id' => $order->id]);
                    $message = 'Order placed and confirmed successfully! Stock has been reserved.';
                } catch (\Exception $e) {
                    \Log::error('COD order confirmation failed', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                    // If stock reservation fails, cancel the order
                    $orderService->cancelOrder($order, 'Stock unavailable during confirmation', false);
                    return redirect()->back()->with('error', 'Order could not be confirmed: ' . $e->getMessage());
                }
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

    /**
     * Create Razorpay order for payment
     */
    public function createRazorpayOrder(Request $request, RazorpayService $razorpayService)
    {
        \Log::info('CreateRazorpayOrder method called', $request->all());
        
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $request->validate([
            'address_id' => 'nullable|exists:addresses,id',
            'name' => 'required_without:address_id|string|nullable',
            'phone' => 'required_without:address_id|string|nullable',
            'address_line' => 'required_without:address_id|string|nullable',
            'city' => 'required_without:address_id|string|nullable',
            'state' => 'required_without:address_id|string|nullable',
            'zip' => 'required_without:address_id|string|nullable',
        ]);

        $cart = Cart::where('user_id', $user->id)->with(['items.productVariation.stock', 'coupon'])->first();
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['error' => 'Your cart is empty'], 400);
        }

        // Validate stock for all items
        foreach ($cart->items as $item) {
            $stockQty = optional($item->productVariation->stock)->quantity ?? 0;
            if ($stockQty < $item->quantity) {
                return response()->json([
                    'error' => 'Insufficient stock for SKU: ' . $item->productVariation->sku . ". Available: {$stockQty}, Required: {$item->quantity}"
                ], 400);
            }
        }

        DB::beginTransaction();
        try {
            // Create or get address
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

            $couponError = $this->couponService->validateForCheckout($cart);
            if ($couponError) {
                throw new \RuntimeException($couponError);
            }

            // Get cart summary with coupon information — use the submitted pincode for accurate shipping
            $pincode = $address->zip ?? $address->postal_code ?? $request->input('zip');
            $cartSummary = $this->cartService->cartSummary($cart, $pincode);

            if ($cartSummary['coupon'] && $cartSummary['discount_amount'] <= 0) {
                throw new \RuntimeException('Coupon discount could not be applied. Please update your cart and try again.');
            }

            // Create order with pending payment status
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $address->id,
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_PENDING,
                'payment_method' => 'online',
                'payment_gateway' => 'razorpay',
                'subtotal' => $cartSummary['subtotal'],
                'tax_amount' => $cartSummary['tax_amount'],
                'tax_rate' => $cartSummary['tax_rate'],
                'tax_name' => $cartSummary['tax_name'],
                'shipping_cost' => $cartSummary['shipping_cost'],
                'total' => $cartSummary['total'],
                'coupon_code' => $cartSummary['coupon'] ? $cartSummary['coupon']['code'] : null,
                'coupon_title' => $cartSummary['coupon'] ? $cartSummary['coupon']['code'] : null,
                'coupon_discount' => $cartSummary['discount_amount'],
            ]);

            // Create order items
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variation_id' => $item->product_variation_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);
            }

            // Create Razorpay order
            $razorpayOrder = $razorpayService->createOrder(
                $cartSummary['total'],
                'INR',
                'order_' . $order->id,
                ['order_id' => $order->id]
            );

            // Update order with Razorpay order ID
            $order->update([
                'razorpay_order_id' => $razorpayOrder['id']
            ]);

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'payment_id' => Payment::generatePaymentId(),
                'gateway' => Payment::GATEWAY_RAZORPAY,
                'gateway_order_id' => $razorpayOrder['id'],
                'status' => Payment::STATUS_PENDING,
                'amount' => $cartSummary['total'],
                'currency' => 'INR',
                'payment_method' => 'online',
                'payment_status' => Payment::PAYMENT_STATUS_PENDING,
                'receipt_number' => 'order_' . $order->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'order_id' => $order->id,
                    'razorpay_order_id' => $razorpayOrder['id'],
                    'created_via' => 'checkout_form'
                ]
            ]);

            DB::commit();

            \Log::info('Razorpay order and payment record created successfully', [
                'order_id' => $order->id,
                'payment_id' => $payment->payment_id,
                'razorpay_order_id' => $razorpayOrder['id']
            ]);

            // Get Razorpay config for frontend
            $razorpayConfig = $razorpayService->getConfig();

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'razorpay_order_id' => $razorpayOrder['id'],
                'amount' => $cartSummary['total'] * 100, // Amount in paise
                'currency' => 'INR',
                'razorpay_config' => $razorpayConfig
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Razorpay order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to create payment order: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Verify Razorpay payment and complete order
     */
    public function verifyRazorpayPayment(Request $request, RazorpayService $razorpayService)
    {
        \Log::info('VerifyRazorpayPayment method called', $request->all());

        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'order_id' => 'required|exists:orders,id'
        ]);

        $order = Order::find($request->order_id);
        
        if (!$order || $order->user_id !== auth()->id()) {
            return response()->json(['error' => 'Invalid order'], 400);
        }

        // Verify payment signature
        $isValidSignature = $razorpayService->verifyPaymentSignature(
            $request->razorpay_order_id,
            $request->razorpay_payment_id,
            $request->razorpay_signature
        );

        if (!$isValidSignature) {
            \Log::error('Invalid Razorpay signature', [
                'order_id' => $order->id,
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id
            ]);
            return response()->json(['error' => 'Payment verification failed'], 400);
        }

        $wasAlreadyPaid = $order->payment_status === Order::PAYMENT_PAID;

        DB::beginTransaction();
        try {
            // Fetch payment details from Razorpay
            $paymentDetails = $razorpayService->fetchPayment($request->razorpay_payment_id);

            // Save payment details only — status transition handled by OrderService::confirmOrder()
            $order->update([
                'payment_status' => Order::PAYMENT_PAID,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
                'payment_data' => $paymentDetails
            ]);

            // Find and update payment record
            $payment = Payment::where('order_id', $order->id)
                            ->where('gateway_order_id', $request->razorpay_order_id)
                            ->first();

            if ($payment) {
                $payment->update([
                    'gateway_payment_id' => $request->razorpay_payment_id,
                    'transaction_id' => $paymentDetails['acquirer_data']['bank_transaction_id'] ?? null,
                    'status' => Payment::STATUS_COMPLETED,
                    'payment_status' => Payment::PAYMENT_STATUS_PAID,
                    'method' => $paymentDetails['method'] ?? null,
                    'gateway_response' => $paymentDetails,
                    'paid_at' => now(),
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'razorpay_payment_id' => $request->razorpay_payment_id,
                        'razorpay_signature' => $request->razorpay_signature,
                        'verified_at' => now()->toISOString(),
                        'payment_details' => $paymentDetails
                    ])
                ]);

                \Log::info('Payment record updated successfully', [
                    'payment_id' => $payment->payment_id,
                    'gateway_payment_id' => $request->razorpay_payment_id
                ]);
            } else {
                \Log::warning('Payment record not found for order', [
                    'order_id' => $order->id,
                    'razorpay_order_id' => $request->razorpay_order_id
                ]);
            }

            if (!$wasAlreadyPaid && $order->coupon_code) {
                $this->couponService->recordCouponUsage($order->coupon_code);
            }

            // Clear cart after successful payment
            $cart = Cart::where('user_id', $order->user_id)->first();
            if ($cart) {
                $cart->items()->delete();
                $cart->delete();
            }

            DB::commit();

            // Confirm order after payment transaction is committed:
            // validates stock, reserves stock, updates status to confirmed, triggers Shiprocket
            $orderService = app(\App\Services\OrderService::class);
            try {
                $orderService->confirmOrder($order->fresh());
                \Log::info('Order confirmed after Razorpay payment', ['order_id' => $order->id]);
            } catch (\Exception $e) {
                // Payment is saved. Order stays pending so admin can confirm manually.
                \Log::error('Order confirmation failed after payment for Order #' . $order->id . ': ' . $e->getMessage());
            }

            \Log::info('Payment verified and order confirmed', [
                'order_id' => $order->id,
                'payment_id' => $request->razorpay_payment_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully',
                'redirect_url' => route('checkout.success', $order->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment verification failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Payment verification failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle Razorpay payment failure
     */
    public function handleRazorpayFailure(Request $request)
    {
        \Log::info('RazorpayPaymentFailure method called', $request->all());

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'error' => 'required|array'
        ]);

        $order = Order::find($request->order_id);
        
        if (!$order || $order->user_id !== auth()->id()) {
            return response()->json(['error' => 'Invalid order'], 400);
        }

        // Update order status to failed
        $order->update([
            'payment_status' => Order::PAYMENT_FAILED,
            'payment_data' => $request->error,
            'notes' => 'Payment failed: ' . ($request->error['description'] ?? 'Unknown error')
        ]);

        // Update payment record
        $payment = Payment::where('order_id', $order->id)
                        ->where('payment_status', Payment::PAYMENT_STATUS_PENDING)
                        ->first();

        if ($payment) {
            $payment->markAsFailed(
                $request->error['description'] ?? 'Payment failed through Razorpay',
                $request->error
            );

            \Log::info('Payment record marked as failed', [
                'payment_id' => $payment->payment_id,
                'reason' => $request->error['description'] ?? 'Unknown error'
            ]);
        }

        \Log::error('Razorpay payment failed', [
            'order_id' => $order->id,
            'error' => $request->error
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Payment failed: ' . ($request->error['description'] ?? 'Unknown error'),
            'redirect_url' => route('checkout.index')
        ]);
    }

    /**
     * Display order history for the authenticated user
     */
    public function orderHistory(Request $request)
    {
        $user = Auth::user();
        
        $query = Order::with(['items.productVariation.product', 'address', 'latestPayment'])
            ->where('user_id', $user->id);

        // Filter by status if provided
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date range if provided
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by order ID
        if ($request->filled('search')) {
            $query->where('id', 'like', '%' . $request->search . '%');
        }

        $orders = $query->latest()->paginate(10);
        $statuses = Order::getStatuses();

        return view('orders.index', compact('orders', 'statuses'));
    }

    /**
     * Display detailed order information
     */
    public function orderDetails(Order $order)
    {
        // Ensure user can only see their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to order details.');
        }

        $order->load([
            'items.productVariation.product.images',
            'address',
            'payments'
        ]);

        // Load attribute values separately since it's not a true relationship
        foreach ($order->items as $item) {
            if ($item->productVariation) {
                $item->productVariation->setAttribute('attributeValues', $item->productVariation->attributeValues());
            }
        }

        return view('orders.details', compact('order'));
    }

    /**
     * Display order tracking information
     */
    public function trackOrder(Order $order)
    {
        // Ensure user can only track their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to order tracking.');
        }

        $order->load([
            'items.productVariation.product',
            'items.productVariation.variationImages',
            'address',
            'shipments',
        ]);

        // Get active shipment with tracking info
        $activeShipment = $order->shipments->where('status', '!=', 'cancelled')->first();

        // Build tracking steps from order status — using dedicated timestamp columns
        $trackingSteps = [
            Order::STATUS_PENDING => [
                'title' => 'Order Placed',
                'description' => 'Your order has been placed successfully',
                'completed' => true,
                'timestamp' => $order->created_at
            ],
            Order::STATUS_CONFIRMED => [
                'title' => 'Order Confirmed',
                'description' => 'Your order has been confirmed and is being prepared',
                'completed' => in_array($order->status, [Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED]),
                'timestamp' => $order->confirmed_at ?? ($order->status === Order::STATUS_CONFIRMED ? $order->updated_at : null)
            ],
            Order::STATUS_PROCESSING => [
                'title' => 'Processing',
                'description' => 'Your order is being processed and packed',
                'completed' => in_array($order->status, [Order::STATUS_PROCESSING, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED]),
                'timestamp' => $order->processing_at ?? ($order->status === Order::STATUS_PROCESSING ? $order->updated_at : null)
            ],
            Order::STATUS_SHIPPED => [
                'title' => 'Shipped',
                'description' => 'Your order has been shipped and is on the way',
                'completed' => in_array($order->status, [Order::STATUS_SHIPPED, Order::STATUS_DELIVERED]),
                'timestamp' => $order->shipped_at ?? ($order->status === Order::STATUS_SHIPPED ? $order->updated_at : null)
            ],
            Order::STATUS_DELIVERED => [
                'title' => 'Delivered',
                'description' => 'Your order has been delivered successfully',
                'completed' => $order->status === Order::STATUS_DELIVERED,
                'timestamp' => $order->delivered_at ?? ($order->status === Order::STATUS_DELIVERED ? $order->updated_at : null)
            ]
        ];

        return view('orders.track', compact('order', 'trackingSteps', 'activeShipment'));
    }

    /**
     * Show public order tracking form
     */
    public function showTrackingForm()
    {
        return view('orders.public-track');
    }

    /**
     * Search for order by order number and email/phone
     */
    public function searchOrder(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
            'contact_info' => 'required|string',
            'contact_type' => 'required|in:email,phone'
        ]);

        $orderNumber = $request->order_number;
        $contactInfo = $request->contact_info;
        $contactType = $request->contact_type;

        // Find order by order number
        $order = Order::where('id', $orderNumber)->first();

        if (!$order) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Order not found. Please check your order number.');
        }

        // Verify contact information
        $isValid = false;
        if ($contactType === 'email') {
            $isValid = $order->user->email === $contactInfo || 
                      ($order->address && $order->address->email === $contactInfo);
        } else if ($contactType === 'phone') {
            $cleanPhone = preg_replace('/[^0-9]/', '', $contactInfo);
            $orderPhone = preg_replace('/[^0-9]/', '', $order->address->phone ?? '');
            $userPhone = preg_replace('/[^0-9]/', '', $order->user->phone ?? '');
            
            $isValid = $orderPhone === $cleanPhone || $userPhone === $cleanPhone;
        }

        if (!$isValid) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Contact information does not match our records.');
        }

        // Redirect to order details
        return redirect()->route('order.track.public.details', $orderNumber)
            ->with('success', 'Order found! Here are your tracking details.');
    }

    /**
     * Display public order tracking details (guest access)
     */
    public function publicTrackOrder($orderNumber)
    {
        $order = Order::with([
            'items.productVariation.product',
            'items.productVariation.variationImages',
            'address',
            'user',
            'shipments'
        ])->findOrFail($orderNumber);

        // Get active shipment with tracking info
        $activeShipment = $order->shipments->where('status', '!=', 'cancelled')->first();

        // Fetch courier scan events from ShipRocket if active shipment has an AWB
        $courierScans = [];
        if ($activeShipment && ($activeShipment->awb_code || $activeShipment->tracking_number)) {
            $awb = $activeShipment->awb_code ?: $activeShipment->tracking_number;

            // First, check if we have tracking_data stored locally from webhooks
            if ($activeShipment->tracking_data && is_array($activeShipment->tracking_data)) {
                $courierScans = $this->parseTrackingDataFromWebhook($activeShipment->tracking_data);
            }

            // If no local data, try fetching live from ShipRocket API
            if (empty($courierScans) && app('shiprocket.enabled')) {
                try {
                    $shipmentService = app(\App\Services\ShiprocketShipmentService::class);
                    $apiScans = $shipmentService->getTrackingHistory($awb);
                    if (!empty($apiScans)) {
                        $courierScans = $this->parseTrackingDataFromApi($apiScans);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to fetch ShipRocket tracking history', [
                        'awb' => $awb,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // As fallback, extract from stored raw tracking_data
            if (empty($courierScans) && $activeShipment->tracking_data) {
                $courierScans = $this->extractScansFromTrackingData($activeShipment->tracking_data);
            }
        }

        // Build tracking timeline steps — using dedicated timestamp columns
        $trackingSteps = [
            Order::STATUS_PENDING => [
                'title' => 'Order Placed',
                'description' => 'Your order has been placed successfully',
                'completed' => true,
                'timestamp' => $order->created_at,
                'icon' => 'bi-check-circle-fill',
            ],
            Order::STATUS_CONFIRMED => [
                'title' => 'Order Confirmed',
                'description' => 'Your order has been confirmed and is being prepared',
                'completed' => in_array($order->status, [Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED]),
                'timestamp' => $order->confirmed_at ?? ($order->status === Order::STATUS_CONFIRMED ? $order->updated_at : null),
                'icon' => 'bi-check-circle-fill',
            ],
            Order::STATUS_PROCESSING => [
                'title' => 'Processing',
                'description' => 'Your order is being processed and packed',
                'completed' => in_array($order->status, [Order::STATUS_PROCESSING, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED]),
                'timestamp' => $order->processing_at ?? ($order->status === Order::STATUS_PROCESSING ? $order->updated_at : null),
                'icon' => 'bi-gear-fill',
            ],
            Order::STATUS_SHIPPED => [
                'title' => 'Shipped',
                'description' => 'Your order has been shipped and is on the way',
                'completed' => in_array($order->status, [Order::STATUS_SHIPPED, Order::STATUS_DELIVERED]),
                'timestamp' => $order->shipped_at ?? ($order->status === Order::STATUS_SHIPPED ? $order->updated_at : null),
                'icon' => 'bi-truck',
            ],
            Order::STATUS_DELIVERED => [
                'title' => 'Delivered',
                'description' => 'Your order has been delivered successfully',
                'completed' => $order->status === Order::STATUS_DELIVERED,
                'timestamp' => $order->delivered_at ?? ($order->status === Order::STATUS_DELIVERED ? $order->updated_at : null),
                'icon' => 'bi-house-door-fill',
            ],
        ];

        // Load attribute values for each item
        foreach ($order->items as $item) {
            if ($item->productVariation) {
                $item->productVariation->setAttribute('attributeValues', $item->productVariation->attributeValues());
            }
        }

        return view('orders.public-track-details', compact('order', 'trackingSteps', 'activeShipment', 'courierScans'));
    }

    /**
     * Parse tracking data stored from webhook snapshots into uniform scan events.
     */
    private function parseTrackingDataFromWebhook(array $trackingData): array
    {
        $scans = [];
        // tracking_data is an array of webhook snapshots
        foreach ($trackingData as $entry) {
            if (isset($entry['webhook_data'])) {
                $wd = $entry['webhook_data'];
                $scans[] = [
                    'location' => $wd['current_location'] ?? $wd['location'] ?? '',
                    'status'   => $wd['current_status'] ?? $wd['status'] ?? '',
                    'activity' => $wd['activity'] ?? $wd['current_status'] ?? '',
                    'date'     => isset($entry['timestamp']) ? \Carbon\Carbon::parse($entry['timestamp']) : now(),
                ];
            } elseif (isset($entry['current_status'])) {
                $scans[] = [
                    'location' => $entry['location'] ?? $entry['current_location'] ?? '',
                    'status'   => $entry['current_status'],
                    'activity' => $entry['activity'] ?? $entry['current_status'],
                    'date'     => isset($entry['timestamp']) ? \Carbon\Carbon::parse($entry['timestamp']) : (isset($entry['date']) ? \Carbon\Carbon::parse($entry['date']) : null),
                ];
            }
        }
        return $scans;
    }

    /**
     * Parse tracking data returned from ShipRocket API getTrackingHistory().
     * ShipRocket returns array of track_status objects.
     */
    private function parseTrackingDataFromApi(array $apiData): array
    {
        $scans = [];
        foreach ($apiData as $entry) {
            $scans[] = [
                'location' => $entry['location'] ?? $entry['current_location'] ?? '',
                'status'   => $entry['status'] ?? $entry['current_status'] ?? '',
                'activity' => $entry['activity'] ?? $entry['status'] ?? '',
                'date'     => isset($entry['date']) ? \Carbon\Carbon::parse($entry['date']) : (isset($entry['datetime']) ? \Carbon\Carbon::parse($entry['datetime']) : null),
            ];
        }
        return $scans;
    }

    /**
     * Extract scan events from raw tracking_data array (stored from webhook or API response).
     */
    private function extractScansFromTrackingData(array $trackingData): array
    {
        $scans = [];
        // Check if tracking_data has a 'track_status' key (from ShipRocket API response format)
        if (isset($trackingData['track_status']) && is_array($trackingData['track_status'])) {
            return $this->parseTrackingDataFromApi($trackingData['track_status']);
        }
        // Check if it's an array of scan objects directly
        if (isset($trackingData[0]) && is_array($trackingData[0])) {
            // Try API format first
            if (isset($trackingData[0]['status']) || isset($trackingData[0]['current_status'])) {
                return $this->parseTrackingDataFromApi($trackingData);
            }
            // Try webhook format
            return $this->parseTrackingDataFromWebhook($trackingData);
        }
        return $scans;
    }

    /**
     * Cancel an order
     */
    public function cancelOrder(Request $request, Order $order)
    {
        // Ensure user can only cancel their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to cancel order.');
        }

        // Hard block: already cancelled / returned / refunded / delivered
        if (!$order->canBeCancelled()) {
            return redirect()->back()->with('error', 'This order cannot be cancelled at this stage.');
        }

        // Amazon-style AWB gate:
        // Once Shiprocket has assigned an AWB (label generated / courier pickup done),
        // the parcel is physically on the way — customer cannot cancel anymore.
        // They must wait for delivery and then raise a return request.
        $shipment = $order->activeShipment;
        if ($shipment && !empty($shipment->awb_code)) {
            return redirect()->back()->with('error',
                'Your order has already been dispatched (AWB: ' . $shipment->awb_code . '). ' .
                'Cancellation is no longer possible. You can return the item after delivery.'
            );
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $orderService = app(\App\Services\OrderService::class);

            // OrderService::cancelOrder() handles everything in the correct order:
            //   1. DB transaction  → stock restore + status = CANCELLED
            //   2. Razorpay refund → full order total (correct amount, correct unit)
            //   3. Shiprocket cancel → shipment (if created but AWB not yet assigned) cancelled
            $orderService->cancelOrder($order, $request->reason);

            return redirect()->route('orders.index')->with('success',
                'Order cancelled successfully. Refund will be processed within 5–7 business days.'
            );
        } catch (\Exception $e) {
            \Log::error('Order cancellation failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Failed to cancel order. Please try again.');
        }
    }

    /**
     * Reorder items from a previous order
     */
    public function reorder(Order $order)
    {
        // Ensure user can only reorder their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to reorder.');
        }

        $order->load('items.productVariation.stock');
        $addedItems = 0;
        $unavailableItems = [];

        try {
            foreach ($order->items as $item) {
                if ($item->productVariation && $item->productVariation->stock) {
                    $availableQty = $item->productVariation->stock->quantity;
                    $requestedQty = $item->quantity;
                    
                    if ($availableQty > 0) {
                        $qtyToAdd = min($availableQty, $requestedQty);
                        $this->cartService->addToCart(Auth::id(), $item->product_variation_id, $qtyToAdd);
                        $addedItems++;
                        
                        if ($qtyToAdd < $requestedQty) {
                            $unavailableItems[] = [
                                'name' => $item->productVariation->product->name,
                                'requested' => $requestedQty,
                                'available' => $availableQty
                            ];
                        }
                    } else {
                        $unavailableItems[] = [
                            'name' => $item->productVariation->product->name,
                            'requested' => $requestedQty,
                            'available' => 0
                        ];
                    }
                }
            }

            $message = "Successfully added {$addedItems} items to cart.";
            if (!empty($unavailableItems)) {
                $message .= " Some items have limited availability.";
            }

            return redirect()->route('cart.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Reorder failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Failed to reorder items. Please try again.');
        }
    }

    /**
     * Request order return
     * Creates an OrderReturnRequest for admin review.
     */
    public function returnOrder(Request $request, Order $order)
    {
        // Ensure user can only return their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to return order.');
        }

        // Check if order can be returned
        if ($order->status !== Order::STATUS_DELIVERED) {
            return redirect()->back()->with('error', 'Only delivered orders can be returned.');
        }

        // Check return window (e.g., 30 days)
        $returnWindow = 30; // days
        if ($order->delivered_at && $order->delivered_at->diffInDays(now()) > $returnWindow) {
            return redirect()->back()->with('error', "Return window of {$returnWindow} days has expired.");
        }

        $request->validate([
            'reason' => 'required|string|max:500',
            'return_items' => 'required|array|min:1',
            'return_items.*' => 'exists:order_items,id'
        ]);

        try {
            // Submit return request via OrderService
            $orderService = app(\App\Services\OrderService::class);
            $returnRequest = $orderService->submitReturnRequest(
                $order,
                $request->return_items,
                $request->reason
            );

            return redirect()->route('orders.index')->with('success', 
                'Return request #' . $returnRequest->id . ' submitted successfully. Our team will review and contact you within 24-48 hours.'
            );
        } catch (\Exception $e) {
            \Log::error('Return request failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Failed to submit return request: ' . $e->getMessage());
        }
    }

    /**
     * Request order exchange
     */
    public function exchangeOrder(Request $request, Order $order)
    {
        // Ensure user can only exchange their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to exchange order.');
        }

        // Check if order can be exchanged
        if ($order->status !== Order::STATUS_DELIVERED) {
            return redirect()->back()->with('error', 'Only delivered orders can be exchanged.');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
            'exchange_items' => 'required|array|min:1',
            'exchange_items.*' => 'exists:order_items,id'
        ]);

        try {
            // Create exchange request (you might want to create a separate ExchangeRequest model)
            // For now, we'll update the order notes

            $order->update([
                'notes' => 'Exchange requested: ' . $request->reason
            ]);

            return redirect()->route('orders.index')->with('success', 'Exchange request submitted successfully. We will contact you soon.');
        } catch (\Exception $e) {
            \Log::error('Exchange request failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Failed to submit exchange request. Please try again.');
        }
    }

    /**
     * Download order invoice
     */
    public function downloadInvoice(Order $order)
    {
        // Ensure user can only download their own order invoices
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to invoice.');
        }

        $order->load([
            'items.productVariation.product',
            'address',
            'user'
        ]);

        // Load attribute values separately since it's not a true relationship
        foreach ($order->items as $item) {
            if ($item->productVariation) {
                $item->productVariation->setAttribute('attributeValues', $item->productVariation->attributeValues());
            }
        }

        // For now, return HTML view that can be printed as PDF
        return view('orders.invoice', compact('order'));
    }

    /**
     * Download order receipt
     */
    public function downloadReceipt(Order $order)
    {
        // Ensure user can only download their own order receipts
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to receipt.');
        }

        $order->load([
            'items.productVariation.product',
            'address',
            'user',
            'latestPayment'
        ]);

        // For now, return HTML view that can be printed as PDF
        return view('orders.receipt', compact('order'));
    }

    /**
     * Get an address for editing
     */
    public function getAddress(Address $address)
    {
        // Ensure user can only access their own addresses
        if ($address->user_id !== Auth::id()) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to address.'
                ], 403);
            }
            abort(403, 'Unauthorized access to address.');
        }

        return response()->json($address);
    }

    /**
     * Store a new address
     */
    public function storeAddress(Request $request)
    {
        $request->validate([
            'label' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'address_line' => 'required|string',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip' => 'required|string|max:10',
            'country' => 'nullable|string|max:255',
            'type' => 'required|in:home,work,other',
            'is_default' => 'boolean',
            'delivery_instructions' => 'nullable|string',
            'landmark' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        
        // If this is set as default, unset other default addresses
        if ($request->is_default) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create([
            'label' => $request->label,
            'name' => $request->name,
            'phone' => $request->phone,
            'alternate_phone' => $request->alternate_phone,
            'address_line' => $request->address_line,
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->zip,
            'country' => $request->country ?? 'India',
            'type' => $request->type,
            'is_default' => $request->is_default ?? false,
            'delivery_instructions' => $request->delivery_instructions,
            'landmark' => $request->landmark,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Address added successfully',
                'address' => $address
            ]);
        }

        return redirect()->back()->with('success', 'Address added successfully');
    }

    /**
     * Update an existing address
     */
    public function updateAddress(Request $request, Address $address)
    {
        // Ensure user can only update their own addresses
        if ($address->user_id !== Auth::id()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to address.'
                ], 403);
            }
            abort(403, 'Unauthorized access to address.');
        }

        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'alternate_phone' => 'nullable|string|max:20',
                'address_line' => 'required|string',
                'city' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'zip' => 'required|string|max:10',
                'country' => 'nullable|string|max:255',
                'type' => 'required|in:home,work,other',
                'is_default' => 'nullable|boolean',
                'delivery_instructions' => 'nullable|string',
                'landmark' => 'nullable|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        try {
            $user = Auth::user();
            
            // Handle default address logic
            $isDefault = $request->has('is_default') && $request->is_default;
            if ($isDefault && !$address->is_default) {
                $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $address->update([
                'name' => $validatedData['name'],
                'phone' => $validatedData['phone'],
                'alternate_phone' => $validatedData['alternate_phone'] ?? null,
                'address_line' => $validatedData['address_line'],
                'city' => $validatedData['city'],
                'state' => $validatedData['state'],
                'zip' => $validatedData['zip'],
                'country' => $validatedData['country'] ?? 'India',
                'type' => $validatedData['type'],
                'is_default' => $isDefault,
                'delivery_instructions' => $validatedData['delivery_instructions'] ?? null,
                'landmark' => $validatedData['landmark'] ?? null,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Address updated successfully',
                    'address' => $address->fresh()
                ]);
            }

            return redirect()->back()->with('success', 'Address updated successfully');

        } catch (\Exception $e) {
            \Log::error('Error updating address: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the address'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while updating the address');
        }
    }

    /**
     * Delete an address
     */
    public function deleteAddress(Address $address)
    {
        // Ensure user can only delete their own addresses
        if ($address->user_id !== Auth::id()) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to address.'
                ], 403);
            }
            abort(403, 'Unauthorized access to address.');
        }

        // Don't allow deletion if this is the only address
        $user = Auth::user();
        if ($user->addresses()->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the only address. Please add another address first.'
            ], 400);
        }

        try {
            // If this was the default address, set another address as default
            if ($address->is_default) {
                $nextAddress = $user->addresses()->where('id', '!=', $address->id)->first();
                if ($nextAddress) {
                    $nextAddress->update(['is_default' => true]);
                }
            }

            $address->delete();

            return response()->json([
                'success' => true,
                'message' => 'Address deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting address: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the address'
            ], 500);
        }
    }

    /**
     * Set an address as default
     */
    public function setDefaultAddress(Address $address)
    {
        // Ensure user can only modify their own addresses
        if ($address->user_id !== Auth::id()) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to address.'
                ], 403);
            }
            abort(403, 'Unauthorized access to address.');
        }

        try {
            $address->setAsDefault();

            return response()->json([
                'success' => true,
                'message' => 'Default address updated successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error setting default address: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while setting default address'
            ], 500);
        }
    }
}
