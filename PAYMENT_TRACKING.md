# Payment Tracking System Documentation

## Overview
A comprehensive payment tracking system has been implemented to track all payment information for both Cash on Delivery (COD) and online payments through Razorpay.

## Database Schema

### Payments Table
All payment transactions are now stored in a dedicated `payments` table with the following fields:

#### Primary Fields
- `id` - Primary key
- `order_id` - Foreign key to orders table
- `user_id` - Foreign key to users table
- `payment_id` - Unique internal payment ID (format: PAY_XXXXX_TIMESTAMP)

#### Gateway Information
- `gateway` - Payment gateway (razorpay, cod, stripe, etc.)
- `gateway_payment_id` - Gateway's payment ID (e.g., Razorpay payment ID)
- `gateway_order_id` - Gateway's order ID (e.g., Razorpay order ID)
- `transaction_id` - Bank transaction ID

#### Payment Details
- `status` - Internal status (pending, processing, completed, failed, cancelled, refunded)
- `amount` - Payment amount (decimal 10,2)
- `currency` - Currency code (default: INR)
- `method` - Payment method (card, upi, netbanking, wallet, etc.)
- `payment_method` - More specific method details
- `payment_status` - Payment status (pending, paid, failed, refunded, cancelled)

#### Tracking Information
- `gateway_response` - JSON field storing full gateway response
- `metadata` - JSON field for additional payment metadata
- `failure_reason` - Text field for failure reasons
- `receipt_number` - Receipt/invoice number

#### Timestamps
- `paid_at` - When payment was successful
- `failed_at` - When payment failed
- `cancelled_at` - When payment was cancelled
- `refunded_at` - When payment was refunded
- `created_at` / `updated_at` - Standard Laravel timestamps

#### Additional Tracking
- `ip_address` - User's IP address
- `user_agent` - User's browser information
- `billing_details` - JSON field for billing information

## Model Relationships

### Payment Model
```php
// Belongs to Order and User
$payment->order
$payment->user

// Scopes
Payment::successful()  // Only successful payments
Payment::failed()      // Only failed payments
Payment::pending()     // Only pending payments
Payment::byGateway('razorpay')  // Filter by gateway
```

### Order Model
```php
// Has many payments (for retry scenarios)
$order->payments

// Latest payment
$order->latestPayment

// Only successful payments
$order->successfulPayments
```

## Payment Flow

### 1. COD Orders
1. User selects COD and places order
2. Order created with status `pending`
3. Payment record created with:
   - `gateway` = 'cod'
   - `status` = 'pending'
   - `payment_status` = 'pending'
   - All order details stored

### 2. Razorpay Orders
1. User selects online payment
2. Order created with status `pending`
3. Payment record created with:
   - `gateway` = 'razorpay'
   - `gateway_order_id` = Razorpay order ID
   - `status` = 'pending'
4. Razorpay payment gateway opens
5. On successful payment:
   - Payment record updated with gateway response
   - `payment_status` = 'paid'
   - `status` = 'completed'
   - `paid_at` timestamp set
6. On failed payment:
   - Payment record updated with failure details
   - `payment_status` = 'failed'
   - `failed_at` timestamp set

## Admin Interface

### Payment Management Routes
- `GET /admin/payments` - List all payments with filters
- `GET /admin/payments/{payment}` - View payment details

### Available Filters
- Payment status (pending, paid, failed, etc.)
- Gateway (razorpay, cod, etc.)
- Search by payment ID, gateway payment ID, or order ID

## API Endpoints

### Admin Payment API
```php
GET /admin/payments
- Filters: status, gateway, search
- Returns: Paginated payment list with summary statistics

GET /admin/payments/{payment}
- Returns: Detailed payment information with order and user details
```

## Payment Model Helper Methods

### Status Management
```php
$payment->markAsPaid($gatewayResponse);
$payment->markAsFailed($reason, $gatewayResponse);
$payment->markAsCancelled($reason);
$payment->markAsRefunded($gatewayResponse);
```

### Attributes
```php
$payment->formatted_amount     // Formatted amount string
$payment->is_successful        // Boolean
$payment->is_failed           // Boolean
$payment->is_pending          // Boolean
```

### Static Methods
```php
Payment::generatePaymentId()   // Generate unique payment ID
Payment::getStatuses()         // Get all possible statuses
Payment::getPaymentStatuses()  // Get all payment statuses
```

## Tracking Benefits

1. **Complete Audit Trail**: Every payment attempt is recorded
2. **Failure Analysis**: Detailed failure reasons and gateway responses
3. **Reconciliation**: Easy matching with gateway records
4. **Analytics**: Payment success rates, failure patterns
5. **Customer Support**: Complete payment history per user
6. **Compliance**: Full transaction records for auditing

## Example Usage

### Create Payment Record
```php
$payment = Payment::create([
    'order_id' => $order->id,
    'user_id' => $user->id,
    'payment_id' => Payment::generatePaymentId(),
    'gateway' => Payment::GATEWAY_RAZORPAY,
    'amount' => $total,
    'currency' => 'INR',
    'payment_method' => 'online',
    // ... other fields
]);
```

### Query Payments
```php
// Get all successful Razorpay payments
$payments = Payment::successful()
                  ->byGateway('razorpay')
                  ->with(['order', 'user'])
                  ->latest()
                  ->get();

// Get payment statistics
$stats = [
    'total' => Payment::count(),
    'successful' => Payment::successful()->count(),
    'total_amount' => Payment::successful()->sum('amount'),
];
```

### Update Payment Status
```php
// Mark as paid with gateway response
$payment->markAsPaid($razorpayResponse);

// Mark as failed with reason
$payment->markAsFailed('Insufficient funds', $gatewayError);
```

This comprehensive payment tracking system ensures complete visibility into all payment transactions and provides robust data for analytics, reconciliation, and customer support.