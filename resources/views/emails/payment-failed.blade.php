<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Unsuccessful</title>
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
        .alert-box {
            background-color: #fef2f2;
            border-left: 4px solid #dc2626;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .alert-box h3 {
            color: #dc2626;
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .alert-box p {
            margin: 5px 0;
            color: #991b1b;
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
            <h1>Payment Unsuccessful</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">Hello {{ $consultation->full_name }},</div>
            
            <p class="message">
                We wanted to let you know that your payment for the consultation with <strong>{{ $consultation->doctor->name ?? 'our doctor' }}</strong> could not be processed.
            </p>

            <!-- Payment Failure Alert -->
            <div class="alert-box">
                <h3>❌ Payment Not Completed</h3>
                <p><strong>Reference:</strong> {{ $payment->reference }}</p>
                @if($failureReason)
                <p><strong>Reason:</strong> {{ $failureReason }}</p>
                @endif
            </div>

            <p class="message">
                Don't worry - this happens sometimes! Here are some common reasons why a payment might fail:
            </p>

            <ul style="color: #4b5563; font-size: 15px; line-height: 1.8;">
                <li>Insufficient funds in your account</li>
                <li>Card expired or invalid</li>
                <li>Bank declined the transaction</li>
                <li>Network timeout during payment</li>
                <li>Payment was cancelled</li>
            </ul>

            <div class="divider"></div>

            <!-- Payment Details -->
            <div class="info-box">
                <h3>Payment Details</h3>
                
                <div class="info-row">
                    <span class="info-label">Consultation Reference</span>
                    <span class="info-value">{{ $consultation->reference }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Payment Reference</span>
                    <span class="info-value">{{ $payment->reference }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Amount</span>
                    <span class="info-value">₦{{ number_format($payment->amount, 2) }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Doctor</span>
                    <span class="info-value">{{ $consultation->doctor->name ?? 'N/A' }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Status</span>
                    <span class="info-value" style="color: #dc2626;">Failed</span>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Call to Action -->
            <p class="message" style="margin-bottom: 15px;">
                <strong>Ready to Try Again?</strong><br>
                You can retry your payment using the link we sent you earlier, or contact us if you need assistance.
            </p>

            <center>
                <a href="{{ route('payment.request', $consultation->reference) }}" class="cta-button">
                    💳 Retry Payment
                </a>
            </center>

            <div class="divider"></div>

            <p class="message">
                If you continue to experience issues or need help, please don't hesitate to reach out to us:
            </p>

            <p class="message" style="text-align: center; margin-top: 20px;">
                📞 <strong>Call/WhatsApp:</strong> 0817 777 7122<br>
                📧 <strong>Email:</strong> inquiries@doctorontap.com.ng
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

