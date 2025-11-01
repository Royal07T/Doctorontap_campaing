<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vital Signs Report</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
            padding: 30px;
            text-align: center;
        }
        .header img {
            max-width: 180px;
            height: auto;
            margin-bottom: 10px;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #1f2937;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .message {
            color: #4b5563;
            margin-bottom: 25px;
            font-size: 15px;
        }
        .info-box {
            background-color: #f9fafb;
            border-left: 4px solid #9333EA;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .info-box h3 {
            margin: 0 0 15px 0;
            color: #1f2937;
            font-size: 16px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
        }
        .info-value {
            color: #1f2937;
            font-weight: 500;
            font-size: 14px;
        }
        .attachment-notice {
            background-color: #ecfdf5;
            border: 1px solid #10b981;
            border-radius: 6px;
            padding: 20px;
            margin: 25px 0;
            display: flex;
            align-items: center;
        }
        .attachment-icon {
            width: 40px;
            height: 40px;
            background-color: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .attachment-text {
            color: #065f46;
            font-size: 14px;
        }
        .attachment-text strong {
            display: block;
            font-size: 15px;
            margin-bottom: 4px;
        }
        .cta-button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 15px;
            text-align: center;
            margin: 20px 0;
            transition: transform 0.2s;
        }
        .cta-button:hover {
            transform: translateY(-2px);
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer-text {
            color: #6b7280;
            font-size: 13px;
            margin: 5px 0;
        }
        .social-links {
            margin: 20px 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #9333EA;
            text-decoration: none;
            font-size: 13px;
        }
        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 30px 0;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }
            .content {
                padding: 30px 20px;
            }
            .info-row {
                flex-direction: column;
            }
            .info-label {
                margin-bottom: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap">
            <h1>Vital Signs Report</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">Hello {{ $patient->name }},</div>
            
            <p class="message">
                Thanks for stopping by our booth at the Velorafair for your free health check-up!<br>
                Your vital signs have been successfully recorded by our team and your personalized report is ready.
            </p>

            <!-- Vital Signs Summary -->
            <div class="info-box">
                <h3>Your Vital Signs Summary</h3>
                
                @if($vitalSign->blood_pressure)
                <div class="info-row">
                    <span class="info-label">Blood Pressure</span>
                    <span class="info-value">{{ $vitalSign->blood_pressure }} mmHg</span>
                </div>
                @endif

                @if($vitalSign->blood_sugar)
                <div class="info-row">
                    <span class="info-label">Blood Sugar</span>
                    <span class="info-value">{{ $vitalSign->blood_sugar }} mg/dL</span>
                </div>
                @endif

                @if($vitalSign->heart_rate)
                <div class="info-row">
                    <span class="info-label">Heart Rate</span>
                    <span class="info-value">{{ $vitalSign->heart_rate }} bpm</span>
                </div>
                @endif

                @if($vitalSign->oxygen_saturation)
                <div class="info-row">
                    <span class="info-label">Oxygen Saturation</span>
                    <span class="info-value">{{ $vitalSign->oxygen_saturation }}%</span>
                </div>
                @endif

                @if($vitalSign->temperature)
                <div class="info-row">
                    <span class="info-label">Temperature</span>
                    <span class="info-value">{{ $vitalSign->temperature }}°C</span>
                </div>
                @endif

                @if($vitalSign->height)
                <div class="info-row">
                    <span class="info-label">Height</span>
                    <span class="info-value">{{ $vitalSign->height }} cm</span>
                </div>
                @endif

                @if($vitalSign->weight)
                <div class="info-row">
                    <span class="info-label">Weight</span>
                    <span class="info-value">{{ $vitalSign->weight }} kg</span>
                </div>
                @endif

                @if($vitalSign->bmi)
                <div class="info-row">
                    <span class="info-label">BMI</span>
                    <span class="info-value">{{ number_format($vitalSign->bmi, 1) }}</span>
                </div>
                @endif

                <div class="info-row">
                    <span class="info-label">Date & Time</span>
                    <span class="info-value">{{ $vitalSign->created_at->format('F d, Y, h:i A') }}</span>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Talk with Doctor Section -->
            <p class="message" style="margin-bottom: 15px;">
                <strong>Talk with a Doctor Now, Pay Later</strong><br>
                Not sure what your results mean? Our doctors are available 24/7 to answer your questions.<br>
                You can talk to a doctor right now and pay later. If you have health concerns, they can also speak with you privately.
            </p>

            <center>
                <a href="{{ app_url() }}" class="cta-button">Book a Consultation</a>
            </center>

            <div class="divider"></div>

            <p class="message" style="margin-bottom: 15px;">
                At DoctorOnTap, we care for you like family, making healthcare easier, more personal, and always within reach. A detailed PDF report is attached for your records you can download, save, or print it anytime.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="footer-text" style="margin-bottom: 15px;">
                With care,
            </p>

            <p class="footer-text" style="margin-bottom: 10px;">
                <strong>The DoctorOnTap Team</strong><br>
                Caring for you, just like family
            </p>

            <p class="footer-text" style="margin-top: 20px; font-size: 14px;">
                <a href="https://doctorontap.com.ng" style="color: #9333EA; text-decoration: none;">https://doctorontap.com.ng</a><br>
                WhatsApp: <a href="https://wa.me/2348177777122" style="color: #9333EA; text-decoration: none;">0817 777 7122</a><br>
                Email: <a href="mailto:inquiries@doctorontap.com.ng" style="color: #9333EA; text-decoration: none;">inquiries@doctorontap.com.ng</a>
            </p>
        </div>
    </div>
</body>
</html>


