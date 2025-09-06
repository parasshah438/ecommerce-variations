<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Welcome to {{ $appName }}</title>
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
        /* Reset styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            min-width: 100%;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
        }

        table {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        td {
            border-collapse: collapse;
        }

        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
        }

        /* Main container */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            text-align: center;
        }

        .header h1 {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header p {
            color: #ffffff;
            font-size: 16px;
            margin: 8px 0 0 0;
            opacity: 0.9;
        }

        /* Content sections */
        .content {
            padding: 40px 30px;
        }

        .welcome-message {
            text-align: center;
            margin-bottom: 30px;
        }

        .welcome-message h2 {
            color: #333333;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .welcome-message p {
            color: #666666;
            font-size: 16px;
            line-height: 1.5;
        }

        .user-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            border-left: 4px solid #667eea;
        }

        .user-info h3 {
            color: #333333;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .user-info p {
            color: #666666;
            margin: 5px 0;
        }

        /* Features section */
        .features {
            margin: 30px 0;
        }

        .features h3 {
            color: #333333;
            font-size: 20px;
            margin-bottom: 20px;
            text-align: center;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            padding: 15px;
            background-color: #ffffff;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .feature-icon::before {
            content: "✓";
            color: #ffffff;
            font-weight: bold;
            font-size: 16px;
        }

        .feature-content h4 {
            color: #333333;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .feature-content p {
            color: #666666;
            font-size: 14px;
            margin: 0;
        }

        /* CTA Button */
        .cta-section {
            text-align: center;
            margin: 40px 0;
            padding: 30px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 10px;
        }

        .cta-section h3 {
            color: #ffffff;
            font-size: 20px;
            margin-bottom: 15px;
        }

        .cta-button {
            display: inline-block;
            background-color: #ffffff;
            color: #f5576c;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        /* Footer */
        .footer {
            background-color: #333333;
            padding: 30px 20px;
            text-align: center;
        }

        .footer h4 {
            color: #ffffff;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .footer p {
            color: #cccccc;
            font-size: 14px;
            margin: 5px 0;
        }

        .footer a {
            color: #667eea;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .social-links {
            margin: 20px 0;
        }

        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #cccccc;
            font-size: 20px;
            text-decoration: none;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }
            
            .content {
                padding: 20px 15px !important;
            }
            
            .header {
                padding: 30px 15px !important;
            }
            
            .header h1 {
                font-size: 24px !important;
            }
            
            .feature-item {
                flex-direction: column;
                text-align: center;
            }
            
            .feature-icon {
                margin-right: 0;
                margin-bottom: 10px;
            }
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
                            <h1>Welcome to {{ $appName }}!</h1>
                            <p>Your journey starts here</p>
                        </td>
                    </tr>

                    <!-- Main Content -->
                    <tr>
                        <td class="content">
                            <!-- Welcome Message -->
                            <div class="welcome-message">
                                <h2>Hello {{ $user->name }}! 👋</h2>
                                <p>We're thrilled to have you join our community. Your account has been successfully created and you're ready to explore all the amazing features we have to offer.</p>
                            </div>

                            <!-- User Info -->
                            <div class="user-info">
                                <h3>Your Account Details</h3>
                                <p><strong>Name:</strong> {{ $user->name }}</p>
                                <p><strong>Email:</strong> {{ $user->email }}</p>
                                @if($user->mobile_number)
                                <p><strong>Mobile:</strong> {{ $user->country_code }} {{ $user->mobile_number }}</p>
                                @endif
                                <p><strong>Account Created:</strong> {{ $user->created_at->format('F j, Y') }}</p>
                            </div>

                            <!-- Features -->
                            <div class="features">
                                <h3>What You Can Do Now</h3>
                                
                                <div class="feature-item">
                                    <div class="feature-icon"></div>
                                    <div class="feature-content">
                                        <h4>Browse Products</h4>
                                        <p>Explore our wide range of high-quality products with detailed descriptions and reviews.</p>
                                    </div>
                                </div>

                                <div class="feature-item">
                                    <div class="feature-icon"></div>
                                    <div class="feature-content">
                                        <h4>Secure Shopping</h4>
                                        <p>Shop with confidence using our secure payment system and buyer protection.</p>
                                    </div>
                                </div>

                                <div class="feature-item">
                                    <div class="feature-icon"></div>
                                    <div class="feature-content">
                                        <h4>Order Tracking</h4>
                                        <p>Track your orders in real-time from purchase to delivery with detailed updates.</p>
                                    </div>
                                </div>

                                <div class="feature-item">
                                    <div class="feature-icon"></div>
                                    <div class="feature-content">
                                        <h4>Wishlist & Favorites</h4>
                                        <p>Save products you love and get notified about price drops and availability.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- CTA Section -->
                            <div class="cta-section">
                                <h3>Ready to Start Shopping?</h3>
                                <a href="{{ $appUrl }}" class="cta-button">Explore Now</a>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer">
                            <h4>{{ $appName }}</h4>
                            <p>Thank you for choosing us for your shopping needs.</p>
                            <p>If you have any questions, our support team is here to help.</p>
                            
                            <div class="social-links">
                                <a href="#">📧</a>
                                <a href="#">📱</a>
                                <a href="#">🌐</a>
                            </div>
                            
                            <p style="font-size: 12px; margin-top: 20px;">
                                This email was sent to {{ $user->email }}. If you didn't create an account, please ignore this email.
                            </p>
                            <p style="font-size: 12px;">
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
