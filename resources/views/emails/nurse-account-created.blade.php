<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Nurse Account | DoctorOnTap</title>
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f6f8fc;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 640px;
            margin: 40px auto;
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }
        .header {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
            color: white;
            text-align: center;
            padding: 40px 25px;
        }
        .header img.logo {
            max-width: 200px;
            height: auto;
            margin: 0 auto 15px;
            display: block;
        }
        .header h1 {
            font-size: 2rem;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .header p {
            font-size: 1rem;
            opacity: 0.9;
            margin-top: 8px;
        }
        .content {
            padding: 35px;
            line-height: 1.7;
        }
        .content h2 {
            color: #9333EA;
            margin-top: 0;
            font-size: 1.6rem;
        }
        .credentials-box {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
            color: white;
            padding: 25px;
            margin: 25px 0;
            border-radius: 12px;
            text-align: center;
        }
        .credentials-box h3 {
            margin: 0 0 15px 0;
            font-size: 1.3rem;
        }
        .credential-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            text-align: left;
        }
        .credential-label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        .credential-value {
            font-size: 1.1rem;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
        }
        .info-box {
            background: #f9f9fb;
            border-left: 5px solid #9333EA;
            padding: 20px;
            margin: 25px 0;
            border-radius: 6px;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
            color: white;
            font-weight: 600;
            padding: 15px 45px;
            font-size: 1rem;
            text-decoration: none;
            border-radius: 50px;
            box-shadow: 0 5px 18px rgba(147, 51, 234, 0.4);
            margin: 20px 0;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .cta-button:hover {
            transform: scale(1.05);
            box-shadow: 0 7px 25px rgba(147, 51, 234, 0.6);
        }
        .steps {
            background: #f9f9fb;
            padding: 25px;
            border-radius: 12px;
            margin: 25px 0;
        }
        .steps h3 {
            color: #9333EA;
            margin-top: 0;
        }
        .step {
            display: flex;
            align-items: start;
            margin: 15px 0;
        }
        .step-number {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .footer {
            background: #f9f9fb;
            text-align: center;
            padding: 30px 25px;
            color: #666;
            font-size: 0.9rem;
        }
        .footer a {
            color: #9333EA;
            text-decoration: none;
        }
        .warning-box {
            background: #FEF3C7;
            border-left: 5px solid #F59E0B;
            padding: 20px;
            margin: 25px 0;
            border-radius: 6px;
        }
        .warning-box strong {
            color: #F59E0B;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap Logo" class="logo">
            <h1>Welcome to DoctorOnTap!</h1>
            <p>Your Nurse Account Has Been Created</p>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>Hello {{ $nurse->name }}! 👋</h2>
            
            <p>Great news! Your nurse account has been successfully created by <strong>{{ $adminName }}</strong>. You're now part of the DoctorOnTap healthcare team!</p>

            <!-- Login Credentials -->
            <div class="credentials-box">
                <h3>🔐 Your Login Credentials</h3>
                <div class="credential-item">
                    <div class="credential-label">Email Address</div>
                    <div class="credential-value">{{ $nurse->email }}</div>
                </div>
                <div class="credential-item">
                    <div class="credential-label">Password</div>
                    <div class="credential-value">{{ $password }}</div>
                </div>
                <div class="credential-item">
                    <div class="credential-label">Login URL</div>
                    <div class="credential-value">{{ route('nurse.login') }}</div>
                </div>
            </div>

            <!-- Warning Box -->
            <div class="warning-box">
                <strong>⚠️ Important Security Note:</strong><br>
                Please change your password after your first login for security purposes. Keep this email safe and do not share your credentials with anyone.
            </div>

            <!-- Next Steps -->
            <div class="steps">
                <h3>📋 Next Steps to Get Started:</h3>
                
                <div class="step">
                    <div class="step-number">1</div>
                    <div>
                        <strong>Verify Your Email</strong><br>
                        Click the button below to verify your email address.
                    </div>
                </div>

                <div class="step">
                    <div class="step-number">2</div>
                    <div>
                        <strong>Login to Your Dashboard</strong><br>
                        Use the credentials above to access your nurse portal.
                    </div>
                </div>

                <div class="step">
                    <div class="step-number">3</div>
                    <div>
                        <strong>Start Attending to Patients</strong><br>
                        Begin your journey providing quality healthcare services.
                    </div>
                </div>
            </div>

            <!-- Verify Email Button -->
            <div style="text-align: center;">
                <a href="{{ route('nurse.verification.verify', ['id' => $nurse->id, 'hash' => sha1($nurse->email)]) }}" 
                   class="cta-button">
                    ✓ Verify Email Address
                </a>
            </div>

            <!-- Account Info -->
            <div class="info-box">
                <strong>📱 Your Account Details:</strong><br>
                <strong>Name:</strong> {{ $nurse->name }}<br>
                <strong>Email:</strong> {{ $nurse->email }}<br>
                <strong>Phone:</strong> {{ $nurse->phone }}<br>
                <strong>Role:</strong> Nurse<br>
                <strong>Status:</strong> Active
            </div>

            <p>If you have any questions or need assistance, please don't hesitate to contact the admin team.</p>
            
            <p style="margin-top: 30px;">
                <strong>Welcome aboard!</strong><br>
                The DoctorOnTap Team
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                <strong>DoctorOnTap</strong><br>
                caring for you, just like family
            </p>
            <p>
                This is an automated email. Please do not reply to this message.<br>
                If you need help, contact your administrator.
            </p>
            <p>
                <a href="{{ url('/') }}">Visit Our Website</a>
            </p>
        </div>
    </div>
</body>
</html>

