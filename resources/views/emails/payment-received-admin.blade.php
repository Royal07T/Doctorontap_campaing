<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Received - Consultation {{ $consultation->reference }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%); padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Payment Received</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px;">
                            <p style="color: #333333; font-size: 16px; margin: 0 0 20px 0;">Hello Admin,</p>
                            
                            <p style="color: #666666; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;">
                                A payment has been successfully received for the following consultation:
                            </p>
                            
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; border-radius: 6px; padding: 20px; margin: 20px 0;">
                                <tr>
                                    <td style="padding: 10px 0;">
                                        <strong style="color: #333333;">Consultation Reference:</strong>
                                        <span style="color: #666666; margin-left: 10px;">{{ $consultation->reference }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0;">
                                        <strong style="color: #333333;">Patient:</strong>
                                        <span style="color: #666666; margin-left: 10px;">{{ $consultation->first_name }} {{ $consultation->last_name }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0;">
                                        <strong style="color: #333333;">Email:</strong>
                                        <span style="color: #666666; margin-left: 10px;">{{ $consultation->email }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0;">
                                        <strong style="color: #333333;">Phone:</strong>
                                        <span style="color: #666666; margin-left: 10px;">{{ $consultation->mobile }}</span>
                                    </td>
                                </tr>
                                @if($consultation->doctor)
                                <tr>
                                    <td style="padding: 10px 0;">
                                        <strong style="color: #333333;">Doctor:</strong>
                                        <span style="color: #666666; margin-left: 10px;">Dr. {{ $consultation->doctor->name }}</span>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding: 10px 0;">
                                        <strong style="color: #333333;">Amount Paid:</strong>
                                        <span style="color: #10b981; font-size: 18px; font-weight: bold; margin-left: 10px;">₦{{ number_format($payment->amount, 2) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0;">
                                        <strong style="color: #333333;">Payment Reference:</strong>
                                        <span style="color: #666666; margin-left: 10px;">{{ $payment->reference }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0;">
                                        <strong style="color: #333333;">Payment Date:</strong>
                                        <span style="color: #666666; margin-left: 10px;">{{ $payment->created_at->format('M d, Y H:i A') }}</span>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="color: #666666; font-size: 14px; line-height: 1.6; margin: 20px 0 0 0;">
                                The consultation payment status has been updated to <strong style="color: #10b981;">PAID</strong> in the system.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #9ca3af; font-size: 12px; margin: 0;">
                                This is an automated notification from DoctorOnTap
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

