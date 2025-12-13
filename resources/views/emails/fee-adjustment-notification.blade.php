<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Adjustment Notification</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 24px;">Consultation Fee Update</h1>
    </div>
    
    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px;">Dear {{ $booking->payer_name }},</p>
        
        <p>We wanted to inform you about a fee adjustment for one of your consultations.</p>
        
        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid {{ $isIncrease ? '#f59e0b' : '#10b981' }};">
            <h3 style="margin-top: 0; color: #7E22CE;">Fee Adjustment Details</h3>
            
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0;"><strong>Booking Reference:</strong></td>
                    <td style="padding: 8px 0;">{{ $booking->reference }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Patient:</strong></td>
                    <td style="padding: 8px 0;">{{ $patient->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Previous Fee:</strong></td>
                    <td style="padding: 8px 0; text-decoration: line-through; color: #999;">₦{{ $oldFee }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>New Fee:</strong></td>
                    <td style="padding: 8px 0; color: {{ $isIncrease ? '#f59e0b' : '#10b981' }}; font-weight: bold; font-size: 18px;">₦{{ $newFee }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Difference:</strong></td>
                    <td style="padding: 8px 0; color: {{ $isIncrease ? '#f59e0b' : '#10b981' }};">
                        {{ $isIncrease ? '+' : '-' }}₦{{ $difference }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; vertical-align: top;"><strong>Reason:</strong></td>
                    <td style="padding: 8px 0;">{{ $reason }}</td>
                </tr>
            </table>
        </div>
        
        @if($isIncrease)
        <div style="background: #fef3c7; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #f59e0b;">
            <p style="margin: 0; color: #92400e;">
                <strong>⚠️ Action Required:</strong> The total amount has increased. Please review the updated invoice.
            </p>
        </div>
        @else
        <div style="background: #d1fae5; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #10b981;">
            <p style="margin: 0; color: #065f46;">
                <strong>✓ Good News:</strong> The fee has been reduced. The updated amount is reflected in your invoice.
            </p>
        </div>
        @endif
        
        <p>If you have any questions about this adjustment, please don't hesitate to contact us.</p>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ url('/') }}" style="display: inline-block; background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%); color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: bold;">View Dashboard</a>
        </div>
        
        <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
        
        <p style="font-size: 12px; color: #666; text-align: center;">
            DoctorOnTap - Your Health, Our Priority<br>
            This is an automated notification. Please do not reply to this email.
        </p>
    </div>
</body>
</html>

