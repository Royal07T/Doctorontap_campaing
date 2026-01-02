<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - DoctorOnTap Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f7f7f7;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            max-width: 150px;
            height: auto;
        }
        .button {
            display: inline-block;
            background-color: #9333EA; /* Purple */
            color: #ffffff;
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #7E22CE;
        }
        .info-box {
            background-color: #F3F4F6;
            border-left: 4px solid #9333EA;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.8em;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ email_logo_inline() }}" alt="DoctorOnTap Logo">
            <h2>Verify Your Email Address</h2>
        </div>
        
        <p>Hello {{ $admin->name }},</p>
        
        <p>Thank you for joining DoctorOnTap as an administrator! To complete your account setup and access the admin dashboard, please verify your email address by clicking the button below:</p>
        
        <div style="text-align: center;">
            <a href="{{ $verificationUrl }}" class="button">
                Verify Email Address
            </a>
        </div>
        
        <div class="info-box">
            <h3 style="margin-top: 0;">Important Information:</h3>
            <ul>
                <li>This verification link will expire in 60 minutes</li>
                <li>If you didn't create an admin account with DoctorOnTap, please ignore this email</li>
                <li>For security reasons, please don't share this link with anyone</li>
            </ul>
        </div>
        
        <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
        <p style="word-break: break-all; background-color: #f5f5f5; padding: 10px; border-radius: 4px;">
            {{ $verificationUrl }}
        </p>
        
        <p>Once verified, you'll be able to:</p>
        <ul>
            <li>Access the admin dashboard</li>
            <li>Manage doctors, nurses, and canvassers</li>
            <li>View and manage consultations</li>
            <li>Process payments and doctor payouts</li>
            <li>Access all administrative features</li>
        </ul>
        
        <p>If you have any questions, please don't hesitate to contact the system administrator.</p>
        
        <div class="footer">
            <p>This email was sent from DoctorOnTap Admin Portal.</p>
            <p><strong>DoctorOnTap</strong> — caring for you, just like family</p>
            <p>© {{ date('Y') }} DoctorOnTap. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

