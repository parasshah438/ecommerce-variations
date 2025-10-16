# SSL Certificate Fix for Local Development

## Problem
When testing Razorpay integration locally, you might encounter this error:
```
cURL error 60: SSL certificate problem: self-signed certificate in certificate chain
```

## Solution Applied ✅

I've updated the system to automatically handle SSL issues in local development:

### 1. **Automatic SSL Skip for Local Development**
- The `RazorpayService` now automatically skips SSL verification when `APP_ENV=local`
- All HTTP requests to Razorpay API will work in local development

### 2. **Environment Configuration**
Added to your `.env` file:
```env
# For local development - skip SSL verification (not for production!)
RAZORPAY_SKIP_SSL_VERIFICATION=true
```

### 3. **Safe for Production**
- SSL verification is only skipped in `local` environment
- Production deployments will use proper SSL verification
- Environment variable can be set to `false` for production

## Alternative Solutions (if needed)

### Option 1: Download CA Certificates
1. Download `cacert.pem` from https://curl.haxx.se/ca/cacert.pem
2. Place it in your PHP directory
3. Update `php.ini`:
   ```ini
   curl.cainfo = "C:\path\to\cacert.pem"
   ```

### Option 2: Windows Certificate Store
For Windows with XAMPP/WAMP:
1. Download latest `cacert.pem`
2. Save to `C:\wamp64\bin\php\php8.x\extras\ssl\cacert.pem`
3. Update `php.ini`:
   ```ini
   openssl.cafile="C:\wamp64\bin\php\php8.x\extras\ssl\cacert.pem"
   curl.cainfo="C:\wamp64\bin\php\php8.x\extras\ssl\cacert.pem"
   ```

### Option 3: System-wide Certificate Installation
1. Download Razorpay's certificate
2. Install it in Windows Certificate Store
3. Restart web server

## Testing

After the fix, your Razorpay payments should work perfectly in local development:

1. **Visit**: `http://127.0.0.1:8000/checkout`
2. **Select**: Online Payment
3. **Test**: Payment should now initialize without SSL errors

## Important Notes

⚠️ **Security Warning**: SSL verification is disabled only for local development. Never disable SSL verification in production!

✅ **Production Ready**: The system automatically enables SSL verification in production environments.

🔧 **Fallback**: If you still face issues, you can manually set `RAZORPAY_SKIP_SSL_VERIFICATION=false` and use the alternative certificate solutions above.

## Verification

You can verify the SSL settings are working by checking the logs in `storage/logs/laravel.log` for successful Razorpay API calls.

The fix ensures seamless local development while maintaining security in production! 🚀