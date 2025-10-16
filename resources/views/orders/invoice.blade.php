<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-info {
            float: left;
            width: 50%;
        }
        
        .invoice-info {
            float: right;
            width: 45%;
            text-align: right;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #007bff;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        
        .customer-info {
            float: left;
            width: 48%;
        }
        
        .order-info {
            float: right;
            width: 48%;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
        }
        
        .items-table td {
            border: 1px solid #dee2e6;
            padding: 10px 8px;
            vertical-align: top;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .total-section {
            float: right;
            width: 300px;
            margin-top: 20px;
        }
        
        .total-table {
            width: 100%;
        }
        
        .total-table td {
            padding: 8px 12px;
            border: none;
        }
        
        .total-table .total-row {
            border-top: 2px solid #007bff;
            font-weight: bold;
            font-size: 14px;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 11px;
            color: #666;
        }
        
        .payment-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .print-actions {
            text-align: center;
            margin: 20px 0;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        /* Print styles */
        @media print {
            body {
                padding: 0;
            }
            
            .print-actions {
                display: none;
            }
            
            .header {
                border-bottom: 2px solid #000;
            }
            
            .company-name, .invoice-title, .section-title {
                color: #000 !important;
            }
        }
    </style>
</head>
<body>
    <!-- Print Actions -->
    <div class="print-actions">
        <button onclick="window.print()" class="btn btn-primary">Print Invoice</button>
        <a href="{{ route('order.details', $order) }}" class="btn btn-secondary">Back to Order</a>
    </div>

    <!-- Header -->
    <div class="header clearfix">
        <div class="company-info">
            <div class="company-name">Your Store Name</div>
            <div>123 Business Street</div>
            <div>City, State 12345</div>
            <div>Phone: +91 9876543210</div>
            <div>Email: support@yourstore.com</div>
        </div>
        <div class="invoice-info">
            <div class="invoice-title">INVOICE</div>
            <div><strong>Invoice #:</strong> {{ $order->id }}</div>
            <div><strong>Date:</strong> {{ $order->created_at->format('M d, Y') }}</div>
            <div><strong>Due Date:</strong> {{ $order->created_at->format('M d, Y') }}</div>
        </div>
    </div>

    <!-- Customer and Order Information -->
    <div class="section clearfix">
        <div class="customer-info">
            <div class="section-title">Bill To:</div>
            <div><strong>{{ $order->address->name }}</strong></div>
            <div>{{ $order->address->address_line }}</div>
            <div>{{ $order->address->city }}, {{ $order->address->state }} - {{ $order->address->zip }}</div>
            <div>{{ $order->address->country }}</div>
            <div>Phone: {{ $order->address->phone }}</div>
            <div>Email: {{ $order->user->email }}</div>
        </div>
        <div class="order-info">
            <div class="section-title">Order Details:</div>
            <div><strong>Order ID:</strong> {{ $order->id }}</div>
            <div><strong>Order Date:</strong> {{ $order->created_at->format('M d, Y h:i A') }}</div>
            <div><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</div>
            <div><strong>Order Status:</strong> {{ ucfirst($order->status) }}</div>
            @if($order->latestPayment)
                <div><strong>Payment Status:</strong> 
                    <span class="payment-status status-{{ $order->latestPayment->payment_status }}">
                        {{ ucfirst($order->latestPayment->payment_status) }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <div class="section">
        <div class="section-title">Order Items</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 10%">#</th>
                    <th style="width: 40%">Item Description</th>
                    <th style="width: 15%" class="text-center">Quantity</th>
                    <th style="width: 15%" class="text-right">Unit Price</th>
                    <th style="width: 20%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->productVariation->product->name }}</strong><br>
                            <small>SKU: {{ $item->productVariation->sku }}</small>
                            @if($item->productVariation->attributeValues->count() > 0)
                                <br>
                                @foreach($item->productVariation->attributeValues as $attrValue)
                                    <small>{{ $attrValue->attribute->name }}: {{ $attrValue->value }}</small>
                                    @if(!$loop->last), @endif
                                @endforeach
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">₹{{ number_format($item->price, 2) }}</td>
                        <td class="text-right">₹{{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Total Section -->
    <div class="clearfix">
        <div class="total-section">
            <table class="total-table">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">₹{{ number_format($order->total, 2) }}</td>
                </tr>
                <tr>
                    <td>Shipping:</td>
                    <td class="text-right">Free</td>
                </tr>
                <tr>
                    <td>Tax:</td>
                    <td class="text-right">₹0.00</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Total Amount:</strong></td>
                    <td class="text-right"><strong>₹{{ number_format($order->total, 2) }}</strong></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Payment Information -->
    @if($order->latestPayment)
        <div class="section">
            <div class="section-title">Payment Information</div>
            <div><strong>Payment ID:</strong> {{ $order->latestPayment->payment_id }}</div>
            <div><strong>Gateway:</strong> {{ ucfirst($order->latestPayment->gateway) }}</div>
            <div><strong>Transaction Date:</strong> {{ $order->latestPayment->created_at->format('M d, Y h:i A') }}</div>
            @if($order->latestPayment->gateway_payment_id)
                <div><strong>Gateway Transaction ID:</strong> {{ $order->latestPayment->gateway_payment_id }}</div>
            @endif
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div><strong>Terms & Conditions:</strong></div>
        <ul style="margin: 10px 0; padding-left: 20px;">
            <li>All orders are subject to availability and confirmation of the order price.</li>
            <li>Returns are accepted within 30 days of delivery for unused items in original packaging.</li>
            <li>For any queries regarding this invoice, please contact our support team.</li>
        </ul>
        
        <div style="margin-top: 20px; text-align: center;">
            <strong>Thank you for your business!</strong><br>
            This is a computer-generated invoice and does not require a physical signature.
        </div>
    </div>
</body>
</html>