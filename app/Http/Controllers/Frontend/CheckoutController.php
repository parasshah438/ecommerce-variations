<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\CartService;
use App\Services\RazorpayService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Payment;

class CheckoutController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
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

        // Get cart summary with coupon information
        $cartSummary = $this->cartService->cartSummary($user->cart);

        return view('checkout.index', compact('cartSummary'));
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

            // Get cart summary with coupon information
            $cartSummary = $this->cartService->cartSummary($cart);

            // Create order in PENDING status (stock not yet reserved)
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $address->id,
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_PENDING,
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

            // Clear cart (order is placed but stock not reserved)
            $cart->items()->delete();
            $cart->delete();

            DB::commit();

            // Simulate payment process - for demo, auto-confirm COD orders
            if ($order->payment_method === 'cod') {
                \Log::info('COD order placed', [
                    'order_id' => $order->id,
                    'payment_id' => $payment->payment_id
                ]);
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

        $cart = Cart::where('user_id', $user->id)->with('items.productVariation.stock')->first();
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

            // Get cart summary with coupon information
            $cartSummary = $this->cartService->cartSummary($cart);

            // Create order with pending payment status
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $address->id,
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_PENDING,
                'payment_method' => 'online',
                'payment_gateway' => 'razorpay',
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

        DB::beginTransaction();
        try {
            // Fetch payment details from Razorpay
            $paymentDetails = $razorpayService->fetchPayment($request->razorpay_payment_id);

            // Update order with payment details
            $order->update([
                'payment_status' => Order::PAYMENT_PAID,
                'status' => Order::STATUS_CONFIRMED,
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

            // Clear cart after successful payment
            $cart = Cart::where('user_id', $order->user_id)->first();
            if ($cart) {
                $cart->items()->delete();
                $cart->delete();
            }

            // Here you can add stock deduction logic if needed
            // Or trigger order confirmation process

            DB::commit();

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

        $order->load(['items.productVariation.product', 'address']);

        // Define tracking timeline
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
                'timestamp' => $order->status === Order::STATUS_CONFIRMED ? $order->updated_at : null
            ],
            Order::STATUS_PROCESSING => [
                'title' => 'Processing',
                'description' => 'Your order is being processed and packed',
                'completed' => in_array($order->status, [Order::STATUS_PROCESSING, Order::STATUS_SHIPPED, Order::STATUS_DELIVERED]),
                'timestamp' => $order->status === Order::STATUS_PROCESSING ? $order->updated_at : null
            ],
            Order::STATUS_SHIPPED => [
                'title' => 'Shipped',
                'description' => 'Your order has been shipped and is on the way',
                'completed' => in_array($order->status, [Order::STATUS_SHIPPED, Order::STATUS_DELIVERED]),
                'timestamp' => $order->status === Order::STATUS_SHIPPED ? $order->updated_at : null
            ],
            Order::STATUS_DELIVERED => [
                'title' => 'Delivered',
                'description' => 'Your order has been delivered successfully',
                'completed' => $order->status === Order::STATUS_DELIVERED,
                'timestamp' => $order->status === Order::STATUS_DELIVERED ? $order->updated_at : null
            ]
        ];

        return view('orders.track', compact('order', 'trackingSteps'));
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

        // Check if order can be cancelled
        if (!in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_CONFIRMED])) {
            return redirect()->back()->with('error', 'This order cannot be cancelled at this stage.');
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Update order status
            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'notes' => $request->reason
            ]);

            // If payment was made, initiate refund
            $payment = $order->latestPayment;
            if ($payment && $payment->payment_status === Payment::PAYMENT_STATUS_PAID) {
                // For Razorpay, initiate refund
                if ($payment->gateway === Payment::GATEWAY_RAZORPAY) {
                    $razorpayService = app(RazorpayService::class);
                    $refund = $razorpayService->refundPayment($payment->gateway_payment_id, $order->total * 100);
                    
                    if ($refund && isset($refund['id'])) {
                        $payment->update([
                            'payment_status' => Payment::PAYMENT_STATUS_REFUNDED,
                            'refund_id' => $refund['id'],
                            'refund_amount' => $refund['amount'] / 100,
                            'refunded_at' => now()
                        ]);
                    }
                }
            }

            // Restore stock for cancelled items
            foreach ($order->items as $item) {
                if ($item->productVariation && $item->productVariation->stock) {
                    $item->productVariation->stock->increment('quantity', $item->quantity);
                }
            }

            DB::commit();

            return redirect()->route('orders.index')->with('success', 'Order cancelled successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Order cancellation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
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
        if ($order->updated_at->diffInDays(now()) > $returnWindow) {
            return redirect()->back()->with('error', "Return window of {$returnWindow} days has expired.");
        }

        $request->validate([
            'reason' => 'required|string|max:500',
            'return_items' => 'required|array|min:1',
            'return_items.*' => 'exists:order_items,id'
        ]);

        try {
            // Update order status
            $order->update([
                'status' => Order::STATUS_RETURNED,
                'returned_at' => now(),
                'notes' => $request->reason
            ]);

            // Create return request record (you might want to create a separate ReturnRequest model)
            // For now, we'll use the order notes field

            return redirect()->route('orders.index')->with('success', 'Return request submitted successfully. We will contact you soon.');
        } catch (\Exception $e) {
            \Log::error('Return request failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Failed to submit return request. Please try again.');
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
}
