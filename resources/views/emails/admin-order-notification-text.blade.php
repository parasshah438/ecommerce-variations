NEW ORDER RECEIVED
================================
A new order {{ $order->order_number }} was placed on {{ $order->created_at ? $order->created_at->format('F j, Y, g:i A') : now()->format('F j, Y') }}.

ORDER SUMMARY
================================
Order Number   : {{ $order->order_number }}
Customer       : {{ $order->user?->name ?: ($order->address?->name ?: '-') }}
Customer Email : {{ $order->user?->email ?: '-' }}
Customer Phone : {{ $order->address?->phone ?: '-' }}
Order Total    : Rs {{ number_format((float)$order->total, 2) }}
Payment Method : @php
echo $order->payment_method === 'cod'
    ? 'Cash on Delivery'
    : ($order->payment_method === 'online' ? 'Online (Razorpay)' : ucfirst($order->payment_method));
@endphp
Payment Status : {{ $order->formatted_payment_status }}
Order Status   : {{ $order->formatted_status }}

@if($order->address)
SHIP TO
--------------------------------
{{ $order->address->name }}
{{ $order->address->address_line }}
{{ $order->address->city }}, {{ $order->address->state }} - {{ $order->address->zip }}
{{ $order->address->phone }}
@endif

ITEMS ({{ $order->items->count() }})
================================
@foreach($order->items as $item)
{{ $item->quantity }}x {{ $item->product_name }} @if($item->productVariation)({{ $item->productVariation->sku }})@endif - Rs {{ number_format((float)$item->price * $item->quantity, 2) }}
@endforeach

View this order in the admin panel: {{ $adminOrderUrl }}

This is an automated notification from {{ $appName }}.
© {{ date('Y') }} {{ $appName }}. All rights reserved.
