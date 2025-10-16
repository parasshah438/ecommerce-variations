# Razorpay Payment Integration Setup

This project has been configured with complete Razorpay payment integration for your ecommerce checkout system.

## Features Implemented

1. **Complete Payment Flow**:
   - Cash on Delivery (COD) - existing functionality
   - Online Payment via Razorpay (UPI, Cards, Net Banking, Wallets)

2. **Payment Processing**:
   - Secure payment order creation
   - Payment verification with signature validation
   - Order status updates based on payment status
   - Error handling for failed payments

3. **Database Integration**:
   - New payment fields added to orders table
   - Payment gateway tracking
   - Razorpay transaction details storage

## Setup Instructions

### 1. Get Razorpay Credentials

1. Sign up at [Razorpay Dashboard](https://dashboard.razorpay.com/)
2. Complete KYC verification for live payments
3. Get your API credentials from Settings > API Keys

### 2. Configure Environment Variables

Add these variables to your `.env` file:

```env
# Razorpay Configuration
RAZORPAY_KEY=rzp_test_your_key_here
RAZORPAY_SECRET=your_secret_here
RAZORPAY_WEBHOOK_SECRET=your_webhook_secret_here
```

**For Testing**: Use test credentials (keys start with `rzp_test_`)
**For Production**: Use live credentials (keys start with `rzp_live_`)

### 3. Test the Integration

1. Visit: `http://127.0.0.1:8000/checkout`
2. Add items to cart first
3. Select delivery address
4. Choose "Online Payment" option
5. Click "Place Order" to open Razorpay payment gateway

### 4. Payment Flow

#### COD Orders:
- Selects COD → Form submits to server → Order created → Success page

#### Online Payments:
- Selects Online → Creates Razorpay order → Opens payment gateway → Payment success → Verification → Order confirmed → Success page

## Technical Implementation

### New Routes Added:
- `POST /checkout/razorpay/create-order` - Creates Razorpay order
- `POST /checkout/razorpay/verify-payment` - Verifies payment signature
- `POST /checkout/razorpay/payment-failed` - Handles payment failures

### New Database Fields (orders table):
- `payment_gateway` - (razorpay/cod/etc.)
- `razorpay_order_id` - Razorpay order ID
- `razorpay_payment_id` - Razorpay payment ID
- `razorpay_signature` - Payment signature for verification
- `payment_data` - JSON field storing payment details

### New Service Class:
- `App\Services\RazorpayService` - Handles all Razorpay API interactions

## Security Features

1. **Payment Signature Verification**: Every payment is verified using Razorpay's signature
2. **CSRF Protection**: All AJAX requests include CSRF tokens
3. **Server-side Validation**: Payment verification happens on server
4. **Secure API Communication**: Uses HTTPS for all Razorpay API calls

## Error Handling

1. **Payment Failures**: Gracefully handled with user-friendly messages
2. **Network Issues**: Automatic retry mechanisms
3. **Invalid Signatures**: Orders marked as failed with detailed logs
4. **User Cancellation**: Allows users to retry payment

## Logs

All payment activities are logged in Laravel logs (`storage/logs/laravel.log`):
- Order creation attempts
- Payment success/failure
- Verification results
- Error details

## Support

For Razorpay-specific issues:
- [Razorpay Documentation](https://razorpay.com/docs/)
- [Razorpay Support](https://razorpay.com/support/)

## Test Card Details (for testing)

Use these test card details in test mode:
- **Card Number**: 4111 1111 1111 1111
- **Expiry**: Any future date
- **CVV**: Any 3 digits
- **OTP**: 123456

## Production Checklist

Before going live:
1. ✅ Complete Razorpay KYC verification
2. ✅ Switch to live API keys
3. ✅ Test with real payment methods
4. ✅ Set up webhook endpoints (optional)
5. ✅ Enable only required payment methods in Razorpay dashboard
6. ✅ Set up proper error monitoring