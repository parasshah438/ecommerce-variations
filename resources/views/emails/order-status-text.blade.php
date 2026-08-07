Hello {{ $order->address?->name ?: ($order->user?->name ?: 'there') }},

Your order has been updated.

ORDER STATUS UPDATE
================================
Order Number   : {{ $order->order_number }}
Current Status : {{ $order->formatted_status }}
Order Total    : Rs {{ number_format((float)$order->total, 2) }}
Payment Method : @php
echo $order->payment_method === 'cod'
    ? 'Cash on Delivery'
    : ($order->payment_method === 'online' ? 'Online Payment (Razorpay)' : ucfirst($order->payment_method));
@endphp

@if($customMessage)
MESSAGE FROM OUR TEAM
--------------------------------
{{ $customMessage }}
@endif

Track your order anytime here: {{ $trackUrl }}

Need help? Visit our Support Center: {{ $appUrl }}/support

Thank you for choosing {{ $appName }}!

{{ $appName }}
© {{ date('Y') }} {{ $appName }}. All rights reserved.
