<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Order Status Update – {{ $appName }}</title>
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

        .header { background-color: #1a1a2e; padding: 28px 24px; text-align: center; }
        .header .brand { color: #ffffff; font-size: 20px; font-weight: 700; letter-spacing: 0.5px; margin: 0; }
        .header .brand span { color: #4cc9f0; }
        .header .tagline { color: #a0a3b1; font-size: 12px; margin-top: 4px; }

        .status-banner { background-color: #e8f0fe; border-bottom: 2px solid #3b82f6; padding: 18px 24px; text-align: center; }
        .status-banner .icon { font-size: 26px; margin-bottom: 4px; }
        .status-banner h1 { color: #1a1a2e; font-size: 18px; font-weight: 700; margin: 0 0 4px; }
        .status-banner p { color: #555; font-size: 13px; margin: 0; }

        .content { padding: 26px 24px; }
        .content h2 { color: #1a1a2e; font-size: 15px; font-weight: 700; margin: 0 0 12px; }

        .info-box {
            background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px;
            padding: 14px 16px; margin-bottom: 20px;
        }
        .info-box table { width: 100%; }
        .info-box td { font-size: 13px; padding: 3px 0; vertical-align: top; }
        .info-box td.label { color: #6c757d; width: 45%; }
        .info-box td.value { color: #212529; font-weight: 600; }

        .message-box {
            background-color: #fff8e1; border: 1px solid #ffeeba; border-left: 4px solid #f0ad4e;
            border-radius: 4px; padding: 14px 16px; margin-bottom: 20px;
        }
        .message-box p { color: #555; font-size: 14px; margin: 0; }

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
                            <p class="tagline">Order Status Update</p>
                        </td>
                    </tr>

                    <!-- Status banner -->
                    <tr>
                        <td class="status-banner">
                            <div class="icon">📦</div>
                            <h1>Your Order Has Been Updated</h1>
                            <p>Order <strong>{{ $order->order_number }}</strong> is now: <strong>{{ $order->formatted_status }}</strong></p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="content">

                            <h2>Hello {{ $order->address?->name ?: ($order->user?->name ?: 'there') }},</h2>

                            @if($customMessage)
                            <div class="message-box">
                                <p>{{ $customMessage }}</p>
                            </div>
                            @endif

                            <div class="info-box">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                    <tr>
                                        <td class="label">Order Number</td>
                                        <td class="value">{{ $order->order_number }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label">Current Status</td>
                                        <td class="value">{{ $order->formatted_status }}</td>
                                    </tr>
                                    <tr>
                                        <td class="label">Order Total</td>
                                        <td class="value">₹{{ number_format((float)$order->total, 2) }}</td>
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
                                </table>
                            </div>

                            <div class="cta">
                                <a href="{{ $trackUrl }}">Track Your Order</a>
                            </div>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer">
                            <p>This is an automated status update from {{ $appName }}.</p>
                            <p>Need help? Visit our <a href="{{ $appUrl }}/support">Support Center</a>.</p>
                            <p style="margin-top: 10px; font-size: 11px;">© {{ date('Y') }} {{ $appName }}. All rights reserved.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
