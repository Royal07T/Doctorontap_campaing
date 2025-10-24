<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Failed - DoctorOnTap</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .error-icon {
            width: 80px;
            height: 80px;
            background-color: #EF4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .error-icon svg {
            width: 40px;
            height: 40px;
            color: white;
        }
        h1 {
            color: #EF4444;
            margin-bottom: 10px;
        }
        .message {
            font-size: 18px;
            margin-bottom: 30px;
            color: #374151;
        }
        .button {
            display: inline-block;
            background-color: #7B3DE9;
            color: #ffffff;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            margin: 10px;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #6D28D9;
        }
        .info-box {
            background-color: #FEF2F2;
            border-left: 4px solid #EF4444;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
            border-radius: 4px;
        }
        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            color: #6B7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>
        
        <h1>Verification Failed</h1>
        <p class="message">{{ $message }}</p>
        
        <div class="info-box">
            <h3 style="margin-top: 0; color: #EF4444;">Possible Reasons:</h3>
            <ul>
                <li>The verification link has expired (links expire after 24 hours)</li>
                <li>The link has already been used</li>
                <li>The link was modified or corrupted</li>
                <li>Your account may have already been verified</li>
            </ul>
        </div>
        
        <div>
            <a href="{{ url('/') }}" class="button">Visit Our Website</a>
            <a href="mailto:support@doctorontap.com.ng" class="button">Contact Support</a>
        </div>
        
        <div class="footer">
            <p><strong>DoctorOnTap</strong> — caring for you, just like family</p>
            <p>&copy; {{ date('Y') }} DoctorOnTap. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
