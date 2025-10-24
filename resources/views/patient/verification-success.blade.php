<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified - DoctorOnTap</title>
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
        .success-icon {
            width: 80px;
            height: 80px;
            background-color: #10B981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .success-icon svg {
            width: 40px;
            height: 40px;
            color: white;
        }
        h1 {
            color: #10B981;
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
            background-color: #F3F4F6;
            border-left: 4px solid #7B3DE9;
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
        <div class="success-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        
        <h1>Email Verified Successfully!</h1>
        <p class="message">{{ $message }}</p>
        
        <div class="info-box">
            <h3 style="margin-top: 0; color: #7B3DE9;">What's Next?</h3>
            <ul>
                <li>Your account is now fully activated</li>
                <li>You can book consultations with our doctors</li>
                <li>You'll receive treatment plans via email</li>
                <li>Access your medical records anytime</li>
            </ul>
        </div>
        
        <div>
            <a href="{{ url('/') }}" class="button">Visit Our Website</a>
            <a href="{{ route('patient.login') }}" class="button">Login to Dashboard</a>
        </div>
        
        <div class="footer">
            <p><strong>DoctorOnTap</strong> — caring for you, just like family</p>
            <p>&copy; {{ date('Y') }} DoctorOnTap. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
