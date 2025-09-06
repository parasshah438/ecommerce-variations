LOGIN VERIFICATION - OTP CODE
=================================

@if($testMode)
🧪 TEST MODE: This is a test OTP for development purposes.
@endif

Your One-Time Password: {{ $otp }}

This code will expire in 5 minutes.

SECURITY INFORMATION:
- Never share this code with anyone
- We will never ask for your OTP over phone or email  
- If you didn't request this code, please ignore this email
- You have maximum 3 attempts to enter the correct code

Having trouble logging in? Contact our support team for assistance.

© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
This is an automated message, please do not reply to this email.
