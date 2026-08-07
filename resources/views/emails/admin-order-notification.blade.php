<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>New Order Notification – {{ $appName }}</title>
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
        .header { background-color: #1a1a2e; padding: 28px 24px; text-align: center; }
        .header .brand { color: #ffffff; font-size: 20px; font-weight: 700; letter-spacing: 0.5px; margin: 0; }
        .header .brand span { color: #4cc9f0; }
        .header .tagline { color: #a0a3b1; font-size: 12px; margin-top: 4px; }

        /* Alert banner */
        .alert-banner { background-color: #fff8e1; border-bottom: 2px solid #f0ad4e; padding: 18px 24px; text-align: center; }
        .alert-banner .icon { font-size: 26px; margin-bottom: 4px; }
        .alert-banner h1 { color: #1a1a2e; font-size: 18px; font-weight: 700; margin: 0 0 4px; }
        .alert-banner p { color: #555; font-size: 13px; margin: 0; }

        .content { padding: 26px 24px; }
        .content h2 { color: #1a1a2e; font-size: 15px; font-weight: 700; margin: 0 0 12px; }

        .info-box {
            background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px;
            padding: 14px 16px; margin-bottom: 20px;
        }
        .info-box table { width: 100%; }
        .info-box td { font-size: 13px; padding: 3px 0; vertical-align: top; }
        .info-box td.label { color: #6c757d; width: 40%; }
        .info-box td.value { color: #212529; font-weight: 600; }

        .badge {
            display: inline-block; padding: 3px 10px; border-radius: 12px;
            font-size: 11px; font-weight: 700; text-transform: uppercase;
        }
        .badge-paid { background-color: #d4edda; color: #155724; }
        .badge-pending { background-color: #fff3cd; color: #856404; }
        .badge-cod { background-color: #d1ecf1; color: #0c5460; }

        .order-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .order-table th {
            background-color: #1a1a2e; color: #ffffff; font-size: 11px;
            text-transform: uppercase; letter-spacing: 0.5px;
            padding: 8px 12px; text-align: left;
        }
        .order-table td { font-size: 13px; padding: 10px 12px; border-bottom: 1px solid #e9ecef; vertical-align: top; }
        .order-table .product-name { color: #212529; font-weight: 600; }
        .order-table .variant { color: #6c757d; font-size: 12px; }
        .order-table .right { text-align: right; }

        .cta { text-align: center; margin: 8px 0 20px; }
        .cta a {
            display: inline-block; background-color: #1a1a2e; color: #ffffff !important;
            padding: 12px 32px; border-radius: 4px; text-decoration: none;
            font-size: 14px; font-weight: 600;
        }
        .cta a:hover { background-color: #2d2d4a; }

        .footer { background-color: #1a1a2e; padding: 22px 20px; text-align: center; }
        .footer p { color: #a0a3b1; font-size: 12px; margin: 4px 0; }
        .footer a { color: #4cc9f0; text-decoration: none; }

        @media only screen and (max-width: 600px) {
            .email-container { width: 100% !important; }
            .content { padding: 18px 16px !important; }
            .header { padding: 22px 16px !important; }
            .order-table th { font-size: 10px; padding: 8px; }
            .order-table td { padding: 8px; }
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
                            <p class="tagline">Admin Notification</p>
                        </td>
                    </tr>

                    <!-- Alert banner -->
                    <tr>
                        <td class="alert-banner">
                            <div class="icon">📦</div>
                            <h1>New Order Received</h1>
                            <p>A new order <strong>{{ $order->order_number }}</strong> was placed on {{ $order->created_at ? $order->created_at->format('F j, Y, g:i A') : now()->format('F j, Y') }}.</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="content">

                            <!-- Order summary -->
                            <h2>Order Summary</h2>
                            <div class="info-box">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                    <tr>
                                        <td class="label">Order Number</td>
                                        <td class="value">{{ $order->order_number }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label">Customer</td>
                                        <td class="value">
                                            {{ $order->user?->name ?: ($order->address?->name ?: '-') }}
                                            @if($order->user?->email)
                                                <br>{{ $order->user->email }}
                                            @endif
                                            @if($order->address?->phone)
                                                <br>{{ $order->address->phone }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label">Order Total</td>
                                        <td class="value">₹{{ number_format((float)$order->total, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label">Payment Method</td>
                                        <td class="value">
                                            @if($order->payment_method === 'cod')
                                                <span class="badge badge-cod">Cash on Delivery</span>
                                            @elseif($order->payment_method === 'online')
                                                Online (Razorpay)
                                            @else
                                                {{ ucfirst($order->payment_method) }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label">Payment Status</td>
                                        <td class="value">
                                            @if($order->payment_status === 'paid')
                                                <span class="badge badge-paid">Paid</span>
                                            @else
                                                <span class="badge badge-pending">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label">Order Status</td>
                                        <td class="value">{{ $order->formatted_status }}</td>
                                    </tr>
                                    @if($order->address)
                                    <tr>
                                        <td class="label">Ship To</td>
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
                            <h2>Items ({{ $order->items->count() }})</h2>
                            <table class="order-table" role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="right">Qty</th>
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
                                            @endif
                                        </td>
                                        <td class="right">{{ $item->quantity }}</td>
                                        <td class="right">₹{{ number_format((float)$item->price * $item->quantity, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- CTA -->
                            <div class="cta">
                                <a href="{{ $adminOrderUrl }}">View Order in Admin Panel</a>
                            </div>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer">
                            <p>This is an automated notification from {{ $appName }}.</p>
                            <p>Please take the required action on this order in the admin panel.</p>
                            <p style="margin-top: 10px; font-size: 11px;">© {{ date('Y') }} {{ $appName }}. All rights reserved.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
