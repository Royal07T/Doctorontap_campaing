<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation Confirmation | DoctorOnTap</title>
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
            background: linear-gradient(135deg, #5A67D8 0%, #805AD5 100%);
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
            font-size: 2.2rem;
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
            color: #5A67D8;
            margin-top: 0;
            font-size: 1.6rem;
        }
        .info-box {
            background: #f9f9fb;
            border-left: 5px solid #5A67D8;
            padding: 20px;
            margin: 25px 0;
            border-radius: 6px;
        }
        .info-box strong {
            color: #5A67D8;
        }
        ul {
            margin: 15px 0;
            padding-left: 20px;
        }
        li {
            margin-bottom: 8px;
        }
        .payment-section {
            background: linear-gradient(135deg, #5A67D8 0%, #805AD5 100%);
            color: white;
            text-align: center;
            padding: 35px 25px;
            border-radius: 12px;
            margin: 30px 0;
        }
        .payment-section h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .payment-section p {
            margin: 10px 0;
        }
        .payment-button {
            display: inline-block;
            background: white;
            color: #5A67D8;
            font-weight: 600;
            padding: 15px 45px;
            font-size: 1rem;
            text-decoration: none;
            border-radius: 50px;
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.25);
            margin-top: 10px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .payment-button:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.3);
        }
        .optional-tag {
            display: inline-block;
            background: #FFF9E6;
            color: #7C5B00;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 8px;
        }
        .footer {
            background: #f9f9fb;
            text-align: center;
            padding: 25px;
            font-size: 0.9rem;
            color: #666;
            border-top: 1px solid #eee;
        }
        .footer p {
            margin: 6px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ env('APP_URL') }}/img/whitelogo.png" alt="DoctorOnTap Logo" class="logo">
            <h1>DoctorOnTap</h1>
            <p>Your Trusted Healthcare Partner</p>
        </div>

        <div class="content">
            <h2>Consultation Booking Confirmed 🎉</h2>

            <p>Dear <strong>{{ $data['first_name'] }} {{ $data['last_name'] }}</strong>,</p>

            <p>We’re thrilled to confirm your consultation booking with <strong>DoctorOnTap</strong>.  
            You can make your payment now for convenience — or choose to pay after your consultation. The choice is entirely yours 💙</p>

            <div class="info-box">
                <strong>Booking Details</strong><br><br>
                <strong>Personal Information:</strong><br>
                Name: {{ $data['first_name'] }} {{ $data['last_name'] }}<br>
                Email: {{ $data['email'] }}<br>
                WhatsApp: {{ $data['mobile'] }}<br>
                Age: {{ $data['age'] }} | Gender: {{ ucfirst($data['gender']) }}<br><br>

                <strong>Medical Info:</strong><br>
                Problem: {{ $data['problem'] }}<br>
                Severity: {{ ucfirst($data['severity']) }}<br>
                @if(isset($data['emergency_symptoms']) && !empty($data['emergency_symptoms']))
                Emergency Symptoms: {{ implode(', ', $data['emergency_symptoms']) }}<br>
                @endif
                @if(isset($data['has_documents']) && $data['has_documents'])
                <strong>Medical Documents:</strong> {{ $data['documents_count'] }} file(s) uploaded ✓<br>
                @endif
                <br>

                <strong>Consultation:</strong><br>
                Mode: {{ ucfirst($data['consult_mode']) }}<br>
                @if(isset($data['doctor']))
                Preferred Doctor: Dr. {{ $data['doctor'] }}<br>
                @endif
                @if(isset($data['doctor_fee']))
                Consultation Fee: <strong>₦{{ number_format($data['doctor_fee'], 2) }}</strong>  
                <em>(Pay now or later)</em><br>
                @endif
            </div>

            @if(isset($data['doctor_fee']) && $data['doctor_fee'] > 0)
            <!-- PAYMENT SECTION -->
            <div class="payment-section">
                <h2>💳 Payment Options</h2>
                <span class="optional-tag">Flexible & Secure</span>

                <p style="font-size: 1.1rem;">Consultation Fee: 
                    <strong style="font-size: 1.3rem;">₦{{ number_format($data['doctor_fee'], 2) }}</strong>
                </p>

                <div style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 10px; margin: 20px 0;">
                    <p style="margin: 5px 0;">⚡ <strong>Option 1:</strong> Pay Now (Instant confirmation)</p>
                    <p style="margin: 5px 0;">🎯 <strong>Option 2:</strong> Pay After Consultation</p>
                </div>

                <a href="{{ url('/payment/request/' . $data['consultation_reference']) }}" class="payment-button">
                    🔒 PAY NOW SECURELY
                </a>

                <p style="margin-top: 15px; font-size: 0.9rem; opacity: 0.9;">
                    Prefer to pay later? No problem — we’ll send you a reminder after your consultation.
                </p>

                <p style="font-size: 0.85rem; margin-top: 12px; opacity: 0.8;">
                    Secure payments via Korapay • Bank Transfer • Debit/Credit Cards • Mobile Money
                </p>
            </div>
            @endif

            <p><strong>Next Steps:</strong></p>
            <ul>
                <li>Our team will contact you on WhatsApp within <strong>1–2 hours</strong>.</li>
                <li>We’ll confirm your consultation time and doctor assignment.</li>
                <li>Your consultation will be conducted via {{ ucfirst($data['consult_mode']) }}.</li>
                <li>Payment can be made before or after consultation.</li>
            </ul>

            <p style="margin-top: 25px;">
                ⚠️ <strong>Emergency Notice:</strong>  
                If you’re experiencing severe symptoms, please visit the nearest hospital immediately or call emergency services.
            </p>

            <p>Thank you for choosing <strong>DoctorOnTap</strong>.  
            Together, we’re making healthcare accessible — one tap at a time.</p>

            <p style="margin-top: 25px;">Stay healthy,  
            <br><strong>The DoctorOnTap Team 💙</strong></p>
        </div>

        <div class="footer">
            <p>This is an automated confirmation email from DoctorOnTap Healthcare Campaign.</p>
            <p><strong>DoctorOnTap</strong> — caring for you, just like family</p>
            <p>&copy; {{ date('Y') }} DoctorOnTap. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
