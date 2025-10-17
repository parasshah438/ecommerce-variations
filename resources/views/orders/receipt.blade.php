<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $order->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        
        .receipt-container {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 2px dashed #ccc;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .store-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .store-info {
            font-size: 10px;
            color: #666;
            line-height: 1.2;
        }
        
        .receipt-title {
            font-size: 16px;
            font-weight: bold;
            margin: 15px 0;
            text-align: center;
        }
        
        .order-info {
            margin-bottom: 20px;
            font-size: 11px;
        }
        
        .order-info div {
            margin-bottom: 3px;
        }
        
        .items-section {
            border-bottom: 1px dashed #ccc;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        
        .item {
            margin-bottom: 10px;
            border-bottom: 1px dotted #eee;
            padding-bottom: 8px;
        }
        
        .item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .item-name {
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .item-details {
            font-size: 10px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .item-price-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .quantity {
            font-size: 10px;
        }
        
        .price {
            font-weight: bold;
        }
        
        .totals {
            border-bottom: 1px dashed #ccc;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        
        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .total-line.grand-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #333;
            padding-top: 8px;
            margin-top: 8px;
        }
        
        .payment-info {
            margin-bottom: 20px;
            font-size: 11px;
        }
        
        .customer-info {
            margin-bottom: 20px;
            font-size: 10px;
            color: #666;
        }
        
        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px dashed #ccc;
            padding-top: 15px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
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
        
        .qr-code {
            text-align: center;
            margin: 15px 0;
        }
        
        .barcode {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            letter-spacing: 2px;
            text-align: center;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <div class="store-name">YOUR STORE NAME</div>
            <div class="store-info">
                123 Business Street<br>
                City, State 12345<br>
                Phone: +91 9876543210<br>
                Email: support@yourstore.com
            </div>
        </div>

        <!-- Receipt Title -->
        <div class="receipt-title">PURCHASE RECEIPT</div>

        <!-- Order Information -->
        <div class="order-info">
            <div><strong>Receipt #:</strong> {{ $order->id }}</div>
            <div><strong>Date:</strong> {{ $order->created_at->format('M d, Y h:i A') }}</div>
            <div><strong>Cashier:</strong> System</div>
            @if($order->latestPayment)
                <div><strong>Transaction ID:</strong> {{ $order->latestPayment->payment_id }}</div>
            @endif
        </div>

        <!-- Items Section -->
        <div class="items-section">
            @foreach($order->items as $item)
                <div class="item">
                    <div class="item-name">{{ $item->productVariation->product->name }}</div>
                    <div class="item-details">
                        SKU: {{ $item->productVariation->sku }}
                        @if($item->productVariation->attributeValues->count() > 0)
                            <br>
                            @foreach($item->productVariation->attributeValues as $attrValue)
                                {{ $attrValue->attribute->name }}: {{ $attrValue->value }}@if(!$loop->last), @endif
                            @endforeach
                        @endif
                    </div>
                    <div class="item-price-line">
                        <span class="quantity">{{ $item->quantity }} x ₹{{ number_format($item->price, 2) }}</span>
                        <span class="price">₹{{ number_format($item->price * $item->quantity, 2) }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Totals -->
        <div class="totals">
            <div class="total-line">
                <span>Subtotal:</span>
                <span>{{ $order->formatted_subtotal }}</span>
            </div>
            @if($order->hasCoupon())
            <div class="total-line" style="color: #28a745;">
                <span>Coupon Discount ({{ $order->coupon_code }}):</span>
                <span>-{{ $order->formatted_coupon_discount }}</span>
            </div>
            @endif
            <div class="total-line">
                <span>Shipping:</span>
                <span>Free</span>
            </div>
            <div class="total-line">
                <span>Tax:</span>
                <span>₹0.00</span>
            </div>
            <div class="total-line grand-total">
                <span>TOTAL:</span>
                <span>₹{{ number_format($order->total, 2) }}</span>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="payment-info">
            <div><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</div>
            @if($order->latestPayment)
                <div><strong>Payment Status:</strong> 
                    <span class="status-badge status-{{ $order->latestPayment->payment_status }}">
                        {{ ucfirst($order->latestPayment->payment_status) }}
                    </span>
                </div>
                <div><strong>Gateway:</strong> {{ ucfirst($order->latestPayment->gateway) }}</div>
                @if($order->latestPayment->gateway_payment_id)
                    <div><strong>Gateway ID:</strong> {{ $order->latestPayment->gateway_payment_id }}</div>
                @endif
            @endif
        </div>

        <!-- Customer Information -->
        <div class="customer-info">
            <strong>Customer Details:</strong><br>
            {{ $order->address->name }}<br>
            {{ $order->address->phone }}<br>
            {{ $order->user->email }}
        </div>

        <!-- Delivery Address -->
        <div class="customer-info">
            <strong>Delivery Address:</strong><br>
            {{ $order->address->address_line }}<br>
            {{ $order->address->city }}, {{ $order->address->state }} - {{ $order->address->zip }}<br>
            {{ $order->address->country }}
        </div>

        <!-- Barcode -->
        <div class="barcode">
            *{{ str_pad($order->id, 8, '0', STR_PAD_LEFT) }}*
        </div>

        <!-- Footer -->
        <div class="footer">
            <div><strong>Thank you for your purchase!</strong></div>
            <div style="margin-top: 10px; font-size: 9px;">
                • Returns accepted within 30 days<br>
                • Keep this receipt for warranty claims<br>
                • For support: support@yourstore.com
            </div>
            
            <div style="margin-top: 15px; font-size: 9px;">
                Visit us online: www.yourstore.com<br>
                Follow us on social media @yourstore
            </div>
            
            <div style="margin-top: 15px; font-size: 8px; font-style: italic;">
                This receipt was generated electronically<br>
                {{ now()->format('Y-m-d H:i:s') }}
            </div>
        </div>
    </div>
</body>
</html>