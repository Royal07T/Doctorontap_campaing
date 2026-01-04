<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation Referral - DoctorOnTap</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f5f5f5;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; width: 100%; border-collapse: collapse; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 8px 8px 0 0;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600;">Consultation Referral</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                Hello <strong>{{ $data['patient_name'] ?? 'Patient' }}</strong>,
                            </p>
                            
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                We wanted to inform you that your consultation (Reference: <strong>{{ $data['original_consultation_reference'] ?? 'N/A' }}</strong>) has been referred to another doctor for specialized care.
                            </p>
                            
                            <div style="background-color: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 25px 0; border-radius: 4px;">
                                <p style="margin: 0 0 10px 0; color: #333333; font-size: 15px;">
                                    <strong>Referred From:</strong> Dr. {{ $data['referring_doctor_name'] ?? 'N/A' }}
                                </p>
                                <p style="margin: 0 0 10px 0; color: #333333; font-size: 15px;">
                                    <strong>Referred To:</strong> Dr. {{ $data['referred_to_doctor_name'] ?? 'N/A' }}
                                    @if(!empty($data['referred_to_doctor_specialization']))
                                        <span style="color: #666666;">({{ $data['referred_to_doctor_specialization'] }})</span>
                                    @endif
                                </p>
                                <p style="margin: 0 0 10px 0; color: #333333; font-size: 15px;">
                                    <strong>New Consultation Reference:</strong> {{ $data['new_consultation_reference'] ?? 'N/A' }}
                                </p>
                                @if(!empty($data['referral_reason']))
                                <p style="margin: 10px 0 0 0; color: #666666; font-size: 14px; font-style: italic;">
                                    <strong>Reason:</strong> {{ $data['referral_reason'] }}
                                </p>
                                @endif
                            </div>
                            
                            <p style="margin: 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                A new consultation has been created for you with Dr. {{ $data['referred_to_doctor_name'] ?? 'the referred doctor' }}. All your medical information and history have been shared with the new doctor to ensure continuity of care.
                            </p>
                            
                            <p style="margin: 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">
                                You will receive further updates about your new consultation. If you have any questions, please don't hesitate to contact us.
                            </p>
                            
                            <!-- CTA Button -->
                            <table role="presentation" style="width: 100%; margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $data['action_url_patient'] ?? '#' }}" style="display: inline-block; padding: 14px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">View New Consultation</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 30px 0 0 0; color: #666666; font-size: 14px; line-height: 1.6;">
                                Best regards,<br>
                                <strong>The DoctorOnTap Team</strong>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px 30px; background-color: #f8f9fa; border-top: 1px solid #e9ecef; border-radius: 0 0 8px 8px; text-align: center;">
                            <p style="margin: 0; color: #666666; font-size: 12px;">
                                This is an automated notification. Please do not reply to this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

