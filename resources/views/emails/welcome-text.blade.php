Welcome to {{ $appName }}!

Hello {{ $user->name }},

We're thrilled to have you join our community! Your account has been successfully created and you're ready to explore all the amazing features we have to offer.

Your Account Details:
- Name: {{ $user->name }}
- Email: {{ $user->email }}
@if($user->mobile_number)
- Mobile: {{ $user->country_code }} {{ $user->mobile_number }}
@endif
- Account Created: {{ $user->created_at->format('F j, Y') }}

What You Can Do Now:

✓ Browse Products
  Explore our wide range of high-quality products with detailed descriptions and reviews.

✓ Secure Shopping
  Shop with confidence using our secure payment system and buyer protection.

✓ Order Tracking
  Track your orders in real-time from purchase to delivery with detailed updates.

✓ Wishlist & Favorites
  Save products you love and get notified about price drops and availability.

Ready to Start Shopping?
Visit us at: {{ $appUrl }}

Thank you for choosing {{ $appName }} for your shopping needs.
If you have any questions, our support team is here to help.

---
This email was sent to {{ $user->email }}. If you didn't create an account, please ignore this email.
© {{ date('Y') }} {{ $appName }}. All rights reserved.
