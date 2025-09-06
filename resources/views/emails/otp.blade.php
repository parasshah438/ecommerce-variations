<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login OTP Verification</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f8f9fa;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        
        .otp-container {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin: 25px 0;
            box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3);
        }
        
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 8px;
            margin: 10px 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .otp-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        
        .security-info {
            background-color: #f8f9fa;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }
        
        .security-info h3 {
            margin: 0 0 10px 0;
            color: #856404;
            font-size: 16px;
        }
        
        .security-info ul {
            margin: 0;
            padding-left: 20px;
            color: #6c6c6c;
            font-size: 14px;
        }
        
        .security-info li {
            margin-bottom: 5px;
        }
        
        .validity {
            background-color: #e3f2fd;
            color: #1565c0;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: 500;
        }
        
        .footer {
            background-color: #f8f9fa;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        
        .footer p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
        }
        
        .company-logo {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 15px;
        }
        
        @media only screen and (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            .otp-code {
                font-size: 28px;
                letter-spacing: 6px;
            }
        }
        
        .test-mode {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-logo">E</div>
            <h1>Login Verification</h1>
        </div>
        
        <div class="content">
            @if($testMode)
                <div class="test-mode">
                    🧪 <strong>TEST MODE:</strong> This is a test OTP for development purposes.
                </div>
            @endif
            
            <h2>Your One-Time Password</h2>
            <p>Use this code to complete your login:</p>
            
            <div class="otp-container">
                <div class="otp-label">OTP CODE</div>
                <div class="otp-code">{{ $otp }}</div>
            </div>
            
            <div class="validity">
                ⏰ This code will expire in <strong>5 minutes</strong>
            </div>
            
            <div class="security-info">
                <h3>🔒 Security Information</h3>
                <ul>
                    <li>Never share this code with anyone</li>
                    <li>We will never ask for your OTP over phone or email</li>
                    <li>If you didn't request this code, please ignore this email</li>
                    <li>You have maximum 3 attempts to enter the correct code</li>
                </ul>
            </div>
            
            <p style="color: #6c757d; font-size: 14px; margin-top: 30px;">
                Having trouble logging in? Contact our support team for assistance.
            </p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>This is an automated message, please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
