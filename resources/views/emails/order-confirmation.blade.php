<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Order Confirmation – {{ $appName }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:AllowPNG/>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        /* Reset */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            margin: 0; padding: 0;
            width: 100% !important; min-width: 100%;
            -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            background-color: #f1f3f5;
        }

        table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        td { border-collapse: collapse; }
        img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }

        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }

        /* Header */
        .header { background-color: #1a1a2e; padding: 32px 24px; text-align: center; }
        .header .brand { color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: 0.5px; margin: 0; }
        .header .brand span { color: #4cc9f0; }
        .header .tagline { color: #a0a3b1; font-size: 13px; margin-top: 4px; }

        /* Status banner */
        .status-banner { background-color: #e8f7ee; border-bottom: 2px solid #28a745; padding: 20px 24px; text-align: center; }
        .status-banner .check {
            width: 44px; height: 44px; line-height: 44px; margin: 0 auto 8px;
            background-color: #28a745; border-radius: 50%; color: #ffffff;
            font-size: 22px; font-weight: 700; text-align: center;
        }
        .status-banner h1 { color: #1a1a2e; font-size: 20px; font-weight: 700; margin: 0 0 4px; }
        .status-banner p { color: #555; font-size: 14px; margin: 0; }

        /* Content */
        .content { padding: 28px 24px; }
        .content h2 { color: #1a1a2e; font-size: 16px; font-weight: 700; margin: 0 0 12px; }

        /* Info boxes */
        .info-box {
            background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px;
            padding: 14px 16px; margin-bottom: 20px;
        }
        .info-box table { width: 100%; }
        .info-box td { font-size: 13px; padding: 3px 0; vertical-align: top; }
        .info-box td.label { color: #6c757d; width: 45%; }
        .info-box td.value { color: #212529; font-weight: 600; }

        /* Order items */
        .order-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .order-table th {
            background-color: #1a1a2e; color: #ffffff; font-size: 12px;
            text-transform: uppercase; letter-spacing: 0.5px;
            padding: 10px 12px; text-align: left;
        }
        .order-table td { font-size: 13px; padding: 12px; border-bottom: 1px solid #e9ecef; vertical-align: top; }
        .order-table .product-name { color: #212529; font-weight: 600; }
        .order-table .variant { color: #6c757d; font-size: 12px; }
        .order-table .price { text-align: right; color: #212529; white-space: nowrap; }
        .order-table .right { text-align: right; }

        /* Totals */
        .totals { width: 100%; margin-bottom: 24px; }
        .totals td { font-size: 13px; padding: 4px 0; }
        .totals td.label { color: #6c757d; text-align: right; width: 70%; }
        .totals td.amount { color: #212529; text-align: right; font-weight: 600; width: 30%; }
        .totals tr.discount td.label { color: #28a745; }
        .totals tr.shipping td.label, .totals tr.tax td.label { color: #6c757d; }
        .totals tr.grand {
            border-top: 2px solid #1a1a2e;
        }
        .totals tr.grand td { padding-top: 10px; }
        .totals tr.grand td.label { color: #1a1a2e; font-size: 15px; font-weight: 700; }
        .totals tr.grand td.amount { color: #1a1a2e; font-size: 18px; font-weight: 800; }

        /* CTA */
        .cta { text-align: center; margin: 8px 0 24px; }
        .cta a {
            display: inline-block; background-color: #1a1a2e; color: #ffffff !important;
            padding: 12px 32px; border-radius: 4px; text-decoration: none;
            font-size: 14px; font-weight: 600;
        }
        .cta a:hover { background-color: #2d2d4a; }

        /* Help */
        .help {
            background-color: #f8f9fa; border: 1px solid #e9ecef; border-left: 4px solid #4cc9f0;
            border-radius: 4px; padding: 14px 16px; margin-bottom: 24px;
        }
        .help p { color: #555; font-size: 13px; margin: 0 0 6px; }
        .help p:last-child { margin-bottom: 0; }
        .help a { color: #0d6efd; text-decoration: none; }
        .help a:hover { text-decoration: underline; }

        /* Footer */
        .footer { background-color: #1a1a2e; padding: 24px 20px; text-align: center; }
        .footer h4 { color: #ffffff; font-size: 14px; font-weight: 600; margin: 0 0 6px; }
        .footer p { color: #a0a3b1; font-size: 12px; margin: 4px 0; }
        .footer a { color: #4cc9f0; text-decoration: none; }

        @media only screen and (max-width: 600px) {
            .email-container { width: 100% !important; }
            .content { padding: 20px 16px !important; }
            .header { padding: 24px 16px !important; }
            .order-table th { font-size: 11px; padding: 8px; }
            .order-table td { padding: 10px 8px; }
        }
    </style>
</head>
<body>
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table class="email-container" role="presentation" cellspacing="0" cellpadding="0" border="0">

                    <!-- Header -->
                    <tr>
                        <td class="header">
                            <p class="brand">{{ $appName }}<span>.</span></p>
                            <p class="tagline">Thank you for shopping with us</p>
                        </td>
                    </tr>

                    <!-- Status banner -->
                    <tr>
                        <td class="status-banner">
                            <div class="check">✓</div>
                            <h1>Order Confirmed!</h1>
                            <p>Your order <strong>{{ $order->order_number }}</strong> has been placed successfully.</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="content">

                            <!-- Greeting -->
                            <h2>Hello {{ $customerName }},</h2>
                            <div class="info-box">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                    <tr>
                                        <td class="label">Order Number</td>
                                        <td class="value">{{ $order->order_number }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label">Order Date</td>
                                        <td class="value">{{ $order->created_at ? $order->created_at->format('F j, Y, g:i A') : now()->format('F j, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label">Payment Method</td>
                                        <td class="value">
                                            @if($order->payment_method === 'cod')
                                                Cash on Delivery
                                            @elseif($order->payment_method === 'online')
                                                Online Payment (Razorpay)
                                            @else
                                                {{ ucfirst($order->payment_method) }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label">Order Status</td>
                                        <td class="value">{{ $order->formatted_status }}</td>
                                    </tr>
                                    @if($order->address)
                                    <tr>
                                        <td class="label">Deliver To</td>
                                        <td class="value">
                                            {{ $order->address->name }}<br>
                                            {{ $order->address->address_line }}<br>
                                            {{ $order->address->city }}, {{ $order->address->state }} – {{ $order->address->zip }}<br>
                                            {{ $order->address->phone }}
                                        </td>
                                    </tr>
                                    @endif
                                </table>
                            </div>

                            <!-- Items -->
                            <h2>Items in Your Order</h2>
                            <table class="order-table" role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="right">Qty</th>
                                        <th class="right">Price</th>
                                        <th class="right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            <div class="product-name">{{ $item->product_name }}</div>
                                            @if($item->productVariation)
                                                <div class="variant">SKU: {{ $item->productVariation->sku }}</div>
                                                @if($item->productVariation->formatted_attributes)
                                                    <div class="variant">{{ $item->productVariation->formatted_attributes }}</div>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="price">{{ $item->quantity }}</td>
                                        <td class="price">₹{{ number_format((float)$item->price, 2) }}</td>
                                        <td class="price">₹{{ number_format((float)$item->price * $item->quantity, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Totals -->
                            <table class="totals" role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td class="label">Subtotal</td>
                                    <td class="amount">₹{{ number_format((float)$order->subtotal, 2) }}</td>
                                </tr>
                                @if($order->coupon_discount > 0)
                                <tr class="discount">
                                    <td class="label">Coupon ({{ $order->coupon_code }})</td>
                                    <td class="amount">− ₹{{ number_format((float)$order->coupon_discount, 2) }}</td>
                                </tr>
                                @endif
                                @if($order->tax_amount > 0)
                                <tr class="tax">
                                    <td class="label">{{ $order->tax_name ?: 'Tax' }}</td>
                                    <td class="amount">₹{{ number_format((float)$order->tax_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="shipping">
                                    <td class="label">Shipping</td>
                                    <td class="amount">
                                        @if((float)$order->shipping_cost > 0)
                                            ₹{{ number_format((float)$order->shipping_cost, 2) }}
                                        @else
                                            Free
                                        @endif
                                    </td>
                                </tr>
                                <tr class="grand">
                                    <td class="label">Order Total</td>
                                    <td class="amount">₹{{ number_format((float)$order->total, 2) }}</td>
                                </tr>
                            </table>

                            <!-- CTA -->
                            <div class="cta">
                                <a href="{{ $trackUrl }}">Track Your Order</a>
                            </div>

                            <!-- Help -->
                            <div class="help">
                                <p><strong>Need help with your order?</strong></p>
                                <p>If you have any questions, reply to this email or visit our <a href="{{ $appUrl }}/support">Support Center</a>.</p>
                                <p>Check your order status anytime on your <a href="{{ $appUrl }}/orders">Orders page</a>.</p>
                            </div>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer">
                            <h4>{{ $appName }}</h4>
                            <p>Thank you for choosing {{ $appName }} for your shopping needs.</p>
                            <p>This email was sent to {{ $order->user?->email ?: ($order->address?->email ?: 'our valued customer') }}.</p>
                            <p style="margin-top: 12px; font-size: 11px;">
                                © {{ date('Y') }} {{ $appName }}. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
