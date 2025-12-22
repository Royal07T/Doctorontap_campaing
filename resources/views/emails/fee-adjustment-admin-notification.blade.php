<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Adjustment - Admin Alert</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #dc2626; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <img src="{{ email_logo_inline() }}" alt="DoctorOnTap Logo" style="max-width: 150px; height: auto; margin: 0 auto 15px auto; display: block;">
        <h1 style="color: white; margin: 0; font-size: 24px;">🔔 Fee Adjustment Alert</h1>
        <p style="color: white; margin: 5px 0 0 0;">Audit & Accounting Notification</p>
    </div>
    
    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px;"><strong>Admin/Accountant,</strong></p>
        
        <p>A doctor has adjusted a consultation fee. Please review for audit purposes.</p>
        
        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc2626;">
            <h3 style="margin-top: 0; color: #dc2626;">Adjustment Details</h3>
            
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <tr>
                    <td style="padding: 8px 0;"><strong>Booking Reference:</strong></td>
                    <td style="padding: 8px 0;">{{ $booking->reference }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Doctor:</strong></td>
                    <td style="padding: 8px 0;">{{ $doctor->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Patient:</strong></td>
                    <td style="padding: 8px 0;">{{ $patient->name }} ({{ $patient->age }} yrs, {{ $patient->gender }})</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Payer:</strong></td>
                    <td style="padding: 8px 0;">{{ $booking->payer_name }} ({{ $booking->payer_email }})</td>
                </tr>
                <tr style="background: #fee2e2;">
                    <td style="padding: 8px 0;"><strong>Previous Fee:</strong></td>
                    <td style="padding: 8px 0;">₦{{ $oldFee }}</td>
                </tr>
                <tr style="background: #dbeafe;">
                    <td style="padding: 8px 0;"><strong>New Fee:</strong></td>
                    <td style="padding: 8px 0; font-weight: bold;">₦{{ $newFee }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Difference:</strong></td>
                    <td style="padding: 8px 0; color: {{ $difference >= 0 ? '#f59e0b' : '#10b981' }}; font-weight: bold;">
                        {{ $difference >= 0 ? '+' : '' }}₦{{ $difference }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; vertical-align: top;"><strong>Reason Given:</strong></td>
                    <td style="padding: 8px 0;">{{ $reason }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Timestamp:</strong></td>
                    <td style="padding: 8px 0;">{{ now()->format('Y-m-d H:i:s') }}</td>
                </tr>
            </table>
        </div>
        
        <div style="background: #fef3c7; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #f59e0b;">
            <p style="margin: 0; color: #92400e;">
                <strong>📝 Action Required:</strong> Review this adjustment and verify it's appropriate for your accounting records.
            </p>
        </div>
        
        <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
        
        <p style="font-size: 12px; color: #666; text-align: center;">
            DoctorOnTap - Internal Audit System<br>
            This is an automated alert for administrative review.
        </p>
    </div>
</body>
</html>

