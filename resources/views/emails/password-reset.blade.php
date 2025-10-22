<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - DoctorOnTap</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap Logo" style="height: 50px; width: auto; margin: 0 auto 15px auto; display: block;">
        <h1>DoctorOnTap</h1>
        <h2>Password Reset Request</h2>
    </div>
    
    <div class="content">
        <p>Hello {{ $user->first_name ?? $user->name }},</p>
        
        <p>You are receiving this email because we received a password reset request for your account.</p>
        
        <p>Click the button below to reset your password:</p>
        
        <div style="text-align: center;">
            <a href="{{ $resetUrl }}" class="button">Reset Password</a>
        </div>
        
        <p>If you did not request a password reset, no further action is required.</p>
        
        <p><strong>This password reset link will expire in 60 minutes.</strong></p>
        
        <p>If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:</p>
        <p style="word-break: break-all; background: #eee; padding: 10px; border-radius: 4px;">{{ $resetUrl }}</p>
    </div>
    
    <div class="footer">
        <p>This email was sent from DoctorOnTap. If you have any questions, please contact our support team.</p>
        <p><strong>DoctorOnTap</strong> — caring for you, just like family</p>
        <p>&copy; {{ date('Y') }} DoctorOnTap. All rights reserved.</p>
    </div>
</body>
</html>
