<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Plan Ready - DoctorOnTap</title>
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
        .ready-badge {
            background-color: #3B82F6;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
        }
        .payment-badge {
            background-color: #F59E0B;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            background-color: #10B981;
            color: #ffffff;
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin: 20px 0;
        }
        .info-box {
            background-color: #F3F4F6;
            border-left: 4px solid #3B82F6;
            padding: 15px;
            margin: 20px 0;
        }
        .payment-box {
            background-color: #FEF3C7;
            border-left: 4px solid #F59E0B;
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
            <img src="{{ asset('img/logo-text.png') }}" alt="DoctorOnTap Logo">
            <h2>Your Treatment Plan is Ready!</h2>
        </div>
        
        <div class="ready-badge">📋 Treatment Plan Complete</div>
        <div class="payment-badge">💳 Payment Required</div>
        
        <p>Hello {{ $consultation->first_name }},</p>
        
        <p>Great news! Your doctor has completed your consultation and created your personalized treatment plan. However, payment is required to unlock and access the full treatment plan.</p>
        
        <div class="info-box">
            <h3 style="margin-top: 0;">Consultation Details:</h3>
            <p><strong>Reference:</strong> {{ $consultation->reference }}</p>
            <p><strong>Doctor:</strong> {{ $consultation->doctor->name }}@if($consultation->doctor->gender) ({{ ucfirst($consultation->doctor->gender) }})@endif</p>
            <p><strong>Date:</strong> {{ $consultation->created_at->format('M d, Y') }}</p>
            <p><strong>Status:</strong> Treatment Plan Ready - Payment Required</p>
        </div>
        
        <div class="payment-box">
            <h3 style="margin-top: 0;">💳 Payment Required</h3>
            <p><strong>Consultation Fee:</strong> NGN {{ number_format($consultation->doctor->consultation_fee, 2) }}</p>
            <p>To access your complete treatment plan, please complete the payment. Once payment is confirmed, you will receive:</p>
            <ul>
                <li>Complete medical diagnosis</li>
                <li>Detailed treatment plan</li>
                <li>Prescribed medications with instructions</li>
                <li>Follow-up instructions</li>
                <li>Lifestyle recommendations</li>
                <li>Specialist referrals (if needed)</li>
            </ul>
        </div>
        
        <p><strong>What happens next?</strong></p>
        <ol>
            <li>Complete your payment using the secure payment link</li>
            <li>You'll receive immediate payment confirmation</li>
            <li>Your treatment plan will be unlocked automatically</li>
            <li>You'll receive the complete treatment plan via email</li>
        </ol>
        
        <div style="text-align: center; margin: 30px 0;">
            <p><strong>Payment will be requested separately via email with a secure payment link.</strong></p>
            <p style="color: #666; font-size: 0.9em;">Please wait for the payment request email from our team.</p>
        </div>
        
        <p><strong>Need Help?</strong></p>
        <p>If you have any questions about your treatment plan or payment, please contact us:</p>
        
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;">
            <p style="margin: 5px 0;">
                📧 Email: {{ env('ADMIN_EMAIL', 'inquiries@doctorontap.com.ng') }}<br>
                📱 Phone: 08177777122<br>
                📱 WhatsApp: {{ $consultation->mobile }}
            </p>
        </div>
        
        <p style="margin-top: 20px; color: #666;">
            Thank you for choosing DoctorOnTap for your healthcare needs!
        </p>
        
        <p style="margin-top: 10px;">
            <strong>The DoctorOnTap Team</strong>
        </p>
        
        <div class="footer">
            <p>This email was sent from DoctorOnTap.</p>
            <p><strong>DoctorOnTap</strong> — caring for you, just like family</p>
            <p>&copy; 2025 DoctorOnTap. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
