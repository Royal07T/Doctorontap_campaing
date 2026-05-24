<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payout Completed</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #f8f9fa; padding: 30px; border-radius: 10px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #9333EA; margin: 0;">Payout Completed</h1>
            <p style="color: #666; margin: 10px 0 0;">DoctorOnTap</p>
        </div>

        <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <p style="margin: 0 0 20px;">Dear {{ $payment->doctor->full_name }},</p>
            
            <p style="margin: 0 0 15px;">We're pleased to inform you that your payout has been successfully processed and transferred to your bank account.</p>

            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; color: #666;">Reference</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ $payment->reference }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; color: #666;">Amount</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; color: #9333EA;">₦{{ number_format($payment->doctor_amount, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; color: #666;">Consultations</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ $payment->total_consultations_count }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; color: #666;">Date Completed</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ $payment->payout_completed_at ? $payment->payout_completed_at->format('M d, Y H:i') : 'N/A' }}</td>
                </tr>
                @if($payment->korapay_reference)
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; color: #666;">Transaction Reference</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ $payment->korapay_reference }}</td>
                </tr>
                @endif
            </table>

            <p style="margin: 20px 0 15px; color: #666; font-size: 14px;">
                The funds have been transferred to your registered bank account. Please allow 1-2 business days for the amount to reflect in your account, depending on your bank's processing time.
            </p>

            <p style="margin: 0 0 20px;">If you have any questions or concerns, please don't hesitate to contact our support team.</p>

            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ route('doctor.payment-history') }}" style="display: inline-block; background: #9333EA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">View Payment History</a>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px; color: #999; font-size: 12px;">
            <p style="margin: 0;">This is an automated email. Please do not reply.</p>
            <p style="margin: 10px 0 0;">&copy; {{ date('Y') }} DoctorOnTap. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
