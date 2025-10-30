<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Starting Soon</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 0;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            margin: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #7B3DE9 0%, #5a2ba8 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 18px;
            color: #7B3DE9;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .message {
            font-size: 15px;
            color: #555;
            line-height: 1.8;
            margin-bottom: 20px;
        }
        .highlight-box {
            background: linear-gradient(135deg, #f3e7ff 0%, #e9d5ff 100%);
            border-left: 4px solid #7B3DE9;
            padding: 20px;
            margin: 25px 0;
            border-radius: 5px;
        }
        .highlight-box h3 {
            margin: 0 0 10px 0;
            color: #7B3DE9;
            font-size: 18px;
        }
        .highlight-box p {
            margin: 5px 0;
            color: #555;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #7B3DE9 0%, #5a2ba8 100%);
            color: white;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
            box-shadow: 0 4px 15px rgba(123, 61, 233, 0.3);
        }
        .cta-button:hover {
            box-shadow: 0 6px 20px rgba(123, 61, 233, 0.4);
        }
        .footer {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #888;
            border-top: 1px solid #e0e0e0;
        }
        .footer a {
            color: #7B3DE9;
            text-decoration: none;
        }
        .icon {
            font-size: 24px;
            margin-bottom: 10px;
        }
        ul {
            padding-left: 20px;
        }
        ul li {
            margin-bottom: 10px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap Logo" style="height: 50px; width: auto; margin: 0 auto 15px auto; display: block;">
            <div class="icon">🚀</div>
            <h1>Campaign Alert!</h1>
            <p>Important Update for Our Medical Team</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Hello Dr. {{ $doctor->name }},
            </div>

            <div class="highlight-box">
                <h3>📅 Campaign Details</h3>
                <p><strong>Campaign Name:</strong> {{ $campaignDetails['name'] ?? 'Healthcare Access Campaign' }}</p>
                <p><strong>Start Date:</strong> {{ $campaignDetails['start_date'] ?? date('F d, Y') }}</p>
                @if(isset($campaignDetails['end_date']) && !empty($campaignDetails['end_date']))
                <p><strong>End Date:</strong> {{ $campaignDetails['end_date'] }}</p>
                @endif
                @if(isset($campaignDetails['description']) && !empty($campaignDetails['description']))
                <p style="margin-top: 15px;">{{ $campaignDetails['description'] }}</p>
                @endif
            </div>

            <div class="message">
                @if(isset($campaignDetails['email_body']) && !empty($campaignDetails['email_body']))
                    {!! nl2br(e($campaignDetails['email_body'])) !!}
                @else
                <p>We hope this message finds you well! We're excited to inform you about an upcoming campaign at <strong>DoctorOnTap</strong>.</p>
                
                <p><strong>What This Means for You:</strong></p>
                <ul>
                    <li>📈 <strong>Increased Patient Volume:</strong> Expect a higher number of consultation requests</li>
                    <li>💼 <strong>Flexible Scheduling:</strong> Please update your availability to accommodate more patients</li>
                    <li>💰 <strong>Enhanced Opportunities:</strong> More consultations mean better earning potential</li>
                    <li>🤝 <strong>Community Impact:</strong> Help us reach more patients in need of medical care</li>
                </ul>

                <p><strong>Action Required:</strong></p>
                <ul>
                    <li>✅ Ensure your profile and availability are up to date</li>
                    <li>✅ Check your notification settings</li>
                    <li>✅ Be prepared for increased consultation requests</li>
                    <li>✅ Maintain quick response times for optimal patient care</li>
                </ul>

                <p>If you have any questions or need support, our team is here to help. Feel free to reach out at any time.</p>
                
                <p>Thank you for being a valued member of the DoctorOnTap medical team. Together, we're making healthcare more accessible!</p>
                @endif
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ app_url('doctor/login') }}" class="cta-button">
                    Access Your Dashboard
                </a>
            </div>

            <div class="message" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #f0f0f0;">
                <p><strong>Best regards,</strong><br>
                The DoctorOnTap Team<br>
                <strong>caring for you, just like family</strong></p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>DoctorOnTap</strong> — caring for you, just like family</p>
            <p>📧 {{ config('mail.admin_email') }}</p>
            <p>📱 08177777122</p>
            <p style="margin-top: 10px; color: #aaa;">This is an automated campaign notification. Please do not reply to this email.</p>
            <p style="margin-top: 10px;">
                <a href="{{ app_url() }}">Visit Website</a> | 
                <a href="{{ app_url('doctor/login') }}">Doctor Login</a>
            </p>
        </div>
    </div>
</body>
</html>

