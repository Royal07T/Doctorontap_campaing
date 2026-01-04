<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation Reminder - DoctorOnTap</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%); padding: 30px 20px; text-align: center; }
        .header img { max-height: 50px; width: auto; }
        .content { padding: 30px 20px; }
        .info-box { background-color: #f9fafb; border-left: 4px solid #9333EA; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .button { display: inline-block; background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%); color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; font-weight: 600; }
        .footer { background-color: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #e5e7eb; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; margin: 5px 0; }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-scheduled { background-color: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ email_logo_inline() }}" alt="DoctorOnTap Logo">
        </div>
        <div class="content">
            <h2 style="color: #1f2937; margin-top: 0;">Consultation Reminder</h2>
            <p>Hello {{ $consultation->first_name }},</p>
            <p>This is a friendly reminder about your consultation with DoctorOnTap.</p>
            
            <div class="info-box">
                <p style="margin: 0 0 10px 0;"><strong>Consultation Reference:</strong> {{ $consultation->reference }}</p>
                <p style="margin: 0 0 10px 0;"><strong>Status:</strong> 
                    <span class="status-badge status-{{ $consultation->status }}">
                        {{ ucfirst($consultation->status) }}
                    </span>
                </p>
                @if($consultation->doctor)
                    <p style="margin: 0 0 10px 0;"><strong>Assigned Doctor:</strong> {{ $consultation->doctor->full_name }}</p>
                @endif
                @if($consultation->status === 'scheduled' && $consultation->scheduled_at)
                    <p style="margin: 0;"><strong>Scheduled Date:</strong> {{ $consultation->scheduled_at->format('M d, Y h:i A') }}</p>
                @endif
            </div>

            @if($consultation->status === 'pending')
                <p>Your consultation is currently pending. Our team is working to assign you to a qualified doctor soon.</p>
            @elseif($consultation->status === 'scheduled')
                <p>Your consultation is scheduled. Please make sure you're available at the scheduled time.</p>
            @endif

            @if($consultation->payment_status === 'unpaid')
                <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0; border-radius: 4px;">
                    <p style="margin: 0; color: #991b1b;"><strong>Payment Required:</strong> Please complete your payment to proceed with your consultation.</p>
                </div>
            @endif

            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ patient_url('consultations/' . $consultation->id) }}" class="button">View Consultation Details</a>
            </p>

            <p>If you have any questions or need to reschedule, please contact our support team.</p>
            <p>Thank you for choosing DoctorOnTap for your healthcare needs.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} DoctorOnTap. All rights reserved.</p>
            <p style="margin: 5px 0 0 0;">This is an automated reminder. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>

