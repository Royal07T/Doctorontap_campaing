<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #7B3DE9 0%, #5a2ba8 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header img.logo {
            max-width: 200px;
            height: auto;
            margin: 0 auto 15px;
            display: block;
        }
        .header h1 {
            margin: 0;
            font-size: 2rem;
        }
        .content {
            padding: 30px;
        }
        .content h2 {
            color: #7B3DE9;
            margin-top: 0;
        }
        .consultation-info {
            background: #f0f7ff;
            border-left: 4px solid #7B3DE9;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .consultation-info h3 {
            margin-top: 0;
            color: #7B3DE9;
        }
        .info-row {
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 150px;
        }
        .payment-section {
            background: linear-gradient(135deg, #7B3DE9 0%, #5a2ba8 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            margin: 30px 0;
        }
        .payment-amount {
            font-size: 3rem;
            font-weight: bold;
            margin: 20px 0;
        }
        .payment-button {
            display: inline-block;
            background: white;
            color: #7B3DE9;
            padding: 18px 50px;
            font-size: 1.3rem;
            font-weight: bold;
            text-decoration: none;
            border-radius: 50px;
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            transition: transform 0.2s;
        }
        .payment-button:hover {
            transform: scale(1.05);
        }
        .secure-note {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 0.9rem;
            color: #666;
        }
        .note-box {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ env('APP_URL') }}/img/whitelogo.png" alt="DoctorOnTap Logo" class="logo">
            <h1>💰 Payment Request</h1>
            <p>Thank you for consulting with us!</p>
        </div>
        
        <div class="content">
            <h2>Hi {{ $consultation->first_name }}! 👋</h2>
            
            <p>Thank you for choosing DoctorOnTap for your healthcare needs. Your consultation with <strong>{{ $consultation->doctor->name }}</strong> has been completed successfully.</p>
            
            <div class="note-box">
                <strong>✅ Good News!</strong> As part of our "Pay After Consult" campaign, you only pay now that your consultation is complete. No surprises, no upfront fees!
            </div>
            
            <div class="consultation-info">
                <h3>📋 Consultation Summary</h3>
                
                <div class="info-row">
                    <span class="label">Reference:</span>
                    <span>{{ $consultation->reference }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Doctor:</span>
                    <span>{{ $consultation->doctor->name }}</span>
                </div>
                
                @if($consultation->doctor->specialization)
                <div class="info-row">
                    <span class="label">Specialization:</span>
                    <span>{{ $consultation->doctor->specialization }}</span>
                </div>
                @endif
                
                <div class="info-row">
                    <span class="label">Consultation For:</span>
                    <span>{{ $consultation->problem }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Consult Mode:</span>
                    <span>{{ ucfirst($consultation->consult_mode) }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Date:</span>
                    <span>{{ $consultation->consultation_completed_at ? $consultation->consultation_completed_at->format('F j, Y') : 'Recently' }}</span>
                </div>
            </div>
            
            <!-- PAYMENT SECTION -->
            <div class="payment-section">
                <h2 style="margin-top: 0; color: white;">💳 Payment Details</h2>
                
                <div class="payment-amount">
                    NGN {{ number_format($consultation->doctor->consultation_fee, 2) }}
                </div>
                
                <p style="font-size: 1.1rem; margin: 10px 0;">
                    Consultation Fee
                </p>
                
                <a href="{{ $paymentUrl }}" class="payment-button">
                    🔒 PAY NOW SECURELY
                </a>
                
                <p style="font-size: 0.9rem; margin-top: 20px; opacity: 0.9;">
                    Click the button above to complete your payment via our secure payment gateway
                </p>
            </div>
            
            <div class="secure-note">
                <strong>🔐 Secure Payment</strong><br>
                Your payment is processed through Korapay, a secure and trusted payment gateway. We accept:
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Bank Transfer</li>
                    <li>Debit/Credit Cards</li>
                    <li>Mobile Money</li>
                    <li>Pay with Bank</li>
                </ul>
            </div>
            
            <p><strong>What happens after payment?</strong></p>
            <ul>
                <li>You'll receive a payment confirmation email immediately</li>
                <li>Your receipt will be sent to your email</li>
                <li>You can contact us anytime if you have questions</li>
            </ul>
            
            <p style="margin-top: 30px;">
                <strong>Need Help?</strong><br>
                If you have any questions about this payment or your consultation, please contact us:
            </p>
            
            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;">
                <p style="margin: 5px 0;">
                    📧 Email: {{ env('ADMIN_EMAIL', 'inquiries@doctorontap.com.ng') }}<br>
                    📱 Phone: 08177777122<br>
                    📱 WhatsApp: {{ $consultation->mobile }}
                </p>
            </div>
            
            <p style="margin-top: 20px; color: #666;">
                Thank you for trusting DoctorOnTap with your healthcare needs!
            </p>
            
            <p style="margin-top: 10px;">
                <strong>The DoctorOnTap Team</strong>
            </p>
        </div>
        
        <div class="footer">
            <p>This is an automated payment request from DoctorOnTap "Pay After Consult" Campaign.</p>
            <p>&copy; {{ date('Y') }} DoctorOnTap. All rights reserved.</p>
            <p style="margin-top: 10px; font-size: 0.8rem;">
                Payment Reference: {{ $consultation->reference }}
            </p>
        </div>
    </div>
</body>
</html>

