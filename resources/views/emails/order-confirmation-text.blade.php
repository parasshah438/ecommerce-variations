Hello {{ $customerName }},

Thank you for shopping with {{ $appName }}! Your order has been placed successfully.

ORDER SUMMARY
================================
Order Number : {{ $order->order_number }}
Order Date   : {{ $order->created_at ? $order->created_at->format('F j, Y, g:i A') : now()->format('F j, Y') }}
Payment      : @php
echo $order->payment_method === 'cod'
    ? 'Cash on Delivery'
    : ($order->payment_method === 'online' ? 'Online Payment (Razorpay)' : ucfirst($order->payment_method));
@endphp
Status       : {{ $order->formatted_status }}

@if($order->address)
DELIVER TO
--------------------------------
{{ $order->address->name }}
{{ $order->address->address_line }}
{{ $order->address->city }}, {{ $order->address->state }} - {{ $order->address->zip }}
{{ $order->address->phone }}
@endif

ITEMS
================================
@foreach($order->items as $item)
{{ $item->quantity }}x {{ $item->product_name }} @if($item->productVariation)({{ $item->productVariation->sku }})@endif
@if($item->productVariation && $item->productVariation->formatted_attributes)    {{ $item->productVariation->formatted_attributes }}
@endif    Unit: Rs {{ number_format((float)$item->price, 2) }} - Total: Rs {{ number_format((float)$item->price * $item->quantity, 2) }}
@endforeach

BILLING SUMMARY
================================
Subtotal      : Rs {{ number_format((float)$order->subtotal, 2) }}
@if($order->coupon_discount > 0)
Coupon ({{ $order->coupon_code }}) : -Rs {{ number_format((float)$order->coupon_discount, 2) }}
@endif
@if($order->tax_amount > 0)
{{ $order->tax_name ?: 'Tax' }} : Rs {{ number_format((float)$order->tax_amount, 2) }}
@endif
Shipping      : @php
echo (float)$order->shipping_cost > 0
    ? 'Rs ' . number_format((float)$order->shipping_cost, 2)
    : 'Free';
@endphp
--------------------------------
ORDER TOTAL   : Rs {{ number_format((float)$order->total, 2) }}

Track your order anytime here: {{ $trackUrl }}

Need help? Visit our Support Center: {{ $appUrl }}/support
You can also view your full order history here: {{ $appUrl }}/orders

Thank you for choosing {{ $appName }}!

{{ $appName }}
© {{ date('Y') }} {{ $appName }}. All rights reserved.
