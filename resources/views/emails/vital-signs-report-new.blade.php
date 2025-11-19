@php
    use App\Helpers\VitalSignsHelper;
    $alertData = VitalSignsHelper::checkForAlerts($vitalSign);
@endphp
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
        
        /* Alert Styles */
        .alert-box {
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            border-left: 5px solid;
        }
        .alert-critical {
            background-color: #fef2f2;
            border-left-color: #dc2626;
        }
        .alert-warning {
            background-color: #fffbeb;
            border-left-color: #f59e0b;
        }
        .alert-info {
            background-color: #eff6ff;
            border-left-color: #3b82f6;
        }
        .alert-success {
            background-color: #f0fdf4;
            border-left-color: #10b981;
        }
        .alert-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .alert-icon {
            font-size: 32px;
            margin-right: 15px;
        }
        .alert-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }
        .alert-critical .alert-title {
            color: #dc2626;
        }
        .alert-warning .alert-title {
            color: #d97706;
        }
        .alert-info .alert-title {
            color: #2563eb;
        }
        .alert-success .alert-title {
            color: #059669;
        }
        .alert-item {
            background-color: #ffffff;
            padding: 12px 15px;
            margin: 10px 0;
            border-radius: 6px;
            border-left: 3px solid;
        }
        .alert-critical .alert-item {
            border-left-color: #dc2626;
        }
        .alert-warning .alert-item {
            border-left-color: #f59e0b;
        }
        .alert-info .alert-item {
            border-left-color: #3b82f6;
        }
        .alert-vital {
            font-weight: 700;
            color: #1f2937;
            font-size: 15px;
            margin-bottom: 4px;
        }
        .alert-value {
            font-size: 18px;
            font-weight: 700;
            margin: 5px 0;
        }
        .alert-critical .alert-value {
            color: #dc2626;
        }
        .alert-warning .alert-value {
            color: #d97706;
        }
        .alert-message {
            color: #4b5563;
            font-size: 14px;
            margin-top: 5px;
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
        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 30px 0;
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

            {{-- HEALTH ALERTS SECTION --}}
            @if($alertData['needsAttention'])
                @if($alertData['hasCritical'])
                    {{-- Critical Alerts --}}
                    <div class="alert-box alert-critical">
                        <div class="alert-header">
                            <div class="alert-icon">🚨</div>
                            <h2 class="alert-title">URGENT: Immediate Medical Attention Needed</h2>
                        </div>
                        <p style="color: #991b1b; font-weight: 600; margin-bottom: 15px;">
                            Some of your vital signs readings require immediate medical attention. Please contact a doctor or visit the nearest emergency room as soon as possible.
                        </p>
                        @foreach($alertData['alerts'] as $alert)
                            @if($alert['type'] === 'critical')
                                <div class="alert-item">
                                    <div class="alert-vital">{{ $alert['icon'] }} {{ $alert['vital'] }}</div>
                                    <div class="alert-value">{{ $alert['value'] }}</div>
                                    <div class="alert-message">{{ $alert['message'] }}</div>
                                </div>
                            @endif
                        @endforeach
                        <p style="color: #991b1b; font-weight: 700; margin-top: 15px; font-size: 15px;">
                            📞 Call us now: 0817 777 7122 or book an urgent consultation below.
                        </p>
                    </div>
                @endif

                @if($alertData['hasWarning'] && !$alertData['hasCritical'])
                    {{-- Warning Alerts --}}
                    <div class="alert-box alert-warning">
                        <div class="alert-header">
                            <div class="alert-icon">⚠️</div>
                            <h2 class="alert-title">Attention: Some Readings Need Medical Review</h2>
                        </div>
                        <p style="color: #92400e; font-weight: 600; margin-bottom: 15px;">
                            Some of your vital signs are outside the normal range. We recommend consulting with a doctor to review these readings.
                        </p>
                        @foreach($alertData['alerts'] as $alert)
                            @if($alert['type'] === 'warning')
                                <div class="alert-item">
                                    <div class="alert-vital">{{ $alert['icon'] }} {{ $alert['vital'] }}</div>
                                    <div class="alert-value">{{ $alert['value'] }}</div>
                                    <div class="alert-message">{{ $alert['message'] }}</div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                {{-- Info Alerts (BMI, etc.) --}}
                @php
                    $infoAlerts = collect($alertData['alerts'])->where('type', 'info');
                @endphp
                @if($infoAlerts->count() > 0)
                    <div class="alert-box alert-info">
                        <div class="alert-header">
                            <div class="alert-icon">ℹ️</div>
                            <h2 class="alert-title">Health Recommendations</h2>
                        </div>
                        @foreach($infoAlerts as $alert)
                            <div class="alert-item">
                                <div class="alert-vital">{{ $alert['icon'] }} {{ $alert['vital'] }}</div>
                                <div class="alert-value" style="color: #2563eb;">{{ $alert['value'] }}</div>
                                <div class="alert-message">{{ $alert['message'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                {{-- All Normal --}}
                <div class="alert-box alert-success">
                    <div class="alert-header">
                        <div class="alert-icon">✅</div>
                        <h2 class="alert-title">Great News! All Readings Normal</h2>
                    </div>
                    <p style="color: #065f46; font-weight: 600;">
                        All your vital signs are within healthy ranges. Keep up the good work maintaining your health!
                    </p>
                </div>
            @endif

            <div class="divider"></div>

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

                @if($vitalSign->respiratory_rate)
                <div class="info-row">
                    <span class="info-label">Respiratory Rate</span>
                    <span class="info-value">{{ $vitalSign->respiratory_rate }} breaths/min</span>
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
                @if($alertData['needsAttention'])
                    Our doctors are available 24/7 to discuss your results and provide medical advice.
                @else
                    Have questions about your results? Our doctors are available 24/7 to help.
                @endif
                You can talk to a doctor right now and pay later.
            </p>

            <center>
                <a href="{{ app_url() }}" class="cta-button">
                    {{ $alertData['hasCritical'] ? '🚨 Book Urgent Consultation' : 'Book a Consultation' }}
                </a>
            </center>

            <div class="divider"></div>

            <p class="message" style="margin-bottom: 15px;">
                At DoctorOnTap, we care for you like family, making healthcare easier, more personal, and always within reach. A detailed PDF report is attached for your records - you can download, save, or print it anytime.
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

            @if($alertData['needsAttention'])
            <p class="footer-text" style="margin-top: 20px; font-weight: 600; color: #dc2626;">
                ⚠️ This report contains health alerts. Please consult a doctor.
            </p>
            @endif
        </div>
    </div>
</body>
</html>

