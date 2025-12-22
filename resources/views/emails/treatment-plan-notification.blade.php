<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Your Treatment Plan – DoctorOnTap</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td {font-family: Arial, sans-serif !important;}
    </style>
    <![endif]-->
    <style type="text/css">
        /* Reset styles */
        body, table, td, p, a, li, blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }
        
        /* Mobile styles */
        @media only screen and (max-width: 600px) {
            .container {
                width: 100% !important;
                max-width: 100% !important;
                padding: 15px !important;
            }
            .header {
                padding: 15px !important;
            }
            .header img {
                max-width: 100px !important;
                height: auto !important;
            }
            .header h2 {
                font-size: 20px !important;
                line-height: 1.3 !important;
            }
            .content {
                padding: 15px !important;
            }
            .btn {
                width: 100% !important;
                padding: 14px 20px !important;
                font-size: 16px !important;
                display: block !important;
                text-align: center !important;
            }
            .section {
                margin: 15px 0 !important;
                padding: 0 !important;
            }
            .section p, .section div {
                font-size: 14px !important;
                line-height: 1.6 !important;
            }
            .footer {
                font-size: 11px !important;
                padding: 15px !important;
            }
            .badge {
                font-size: 12px !important;
                padding: 6px 12px !important;
            }
        }
        
        /* Desktop styles */
        @media only screen and (min-width: 601px) {
            .container {
                max-width: 600px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f9fafb; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    <!-- Wrapper table for email clients -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f9fafb;">
        <tr>
            <td align="center" style="padding: 20px 10px;">
                <!-- Container -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" class="container" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.08);">
                    <!-- Header -->
                    <tr>
                        <td class="header" style="text-align: center; padding: 25px 25px 20px 25px; background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%); border-radius: 10px 10px 0 0;">
                            <img src="{{ email_logo_inline() }}" alt="DoctorOnTap" style="max-width: 120px; height: auto; display: block; margin: 0 auto 15px auto;">
                            <h2 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600; line-height: 1.3;">Your Treatment Plan is Ready</h2>
                            <div class="badge" style="background: #10b981; color: white; padding: 8px 14px; border-radius: 20px; display: inline-block; font-size: 13px; margin: 15px 0 0 0; font-weight: 600;">✓ Payment Confirmed</div>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td class="content" style="padding: 25px; color: #111827; font-size: 16px; line-height: 1.6;">
                            <p style="margin: 0 0 15px 0; font-size: 16px;">Hello <strong style="color: #111827;">{{ $consultation->first_name }}</strong>,</p>
                            
                            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.6;">
                                Your consultation with <strong>Dr. {{ $consultation->doctor->name }}</strong> has been completed.
                                Your personalized treatment plan is now available.
                            </p>
                            
                            <div class="section" style="margin: 20px 0; padding: 15px; background-color: #f9fafb; border-radius: 8px; border-left: 4px solid #9333EA;">
                                <strong style="color: #111827; display: block; margin-bottom: 5px;">Consultation Reference:</strong>
                                <span style="font-family: monospace; font-size: 14px; color: #6b7280;">{{ $consultation->reference }}</span>
                            </div>
                            
                            <div class="section" style="margin: 20px 0; padding: 15px; background-color: #EFF6FF; border-radius: 8px; border-left: 4px solid #3B82F6;">
                                <p style="margin: 0; font-size: 15px; color: #1e40af;">
                                    📎 A detailed PDF copy of your treatment plan is attached to this email.<br>
                                    Please download and store it securely.
                                </p>
                            </div>
                            
                            <!-- Button -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 25px 0;">
                                <tr>
                                    <td align="center" style="padding: 0;">
                                        <a href="{{ route('treatment-plan.view', $consultation->reference) }}" class="btn" style="display: inline-block; background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%); color: #ffffff; padding: 14px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px; text-align: center; box-shadow: 0 4px 6px rgba(147, 51, 234, 0.3);">
                                            🔐 View Secure Treatment Plan
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <div class="section" style="margin: 20px 0; padding: 15px; background-color: #FEF3C7; border-radius: 8px; border-left: 4px solid #F59E0B;">
                                <p style="margin: 0 0 10px 0; font-weight: 600; color: #92400E; font-size: 15px;">⚠️ Important:</p>
                                <p style="margin: 0; font-size: 14px; color: #78350F; line-height: 1.6;">
                                    Follow your doctor's instructions exactly.<br>
                                    If your symptoms worsen or you experience any emergency symptoms, seek immediate medical care.
                                </p>
                            </div>
                            
                            <div class="section" style="margin: 20px 0; padding: 15px; background-color: #F0FDF4; border-radius: 8px; border: 1px solid #BBF7D0;">
                                <p style="margin: 0 0 10px 0; font-weight: 600; color: #166534; font-size: 15px;">💬 Need help?</p>
                                <p style="margin: 5px 0; font-size: 14px; color: #166534;">
                                    📧 <a href="mailto:inquiries@doctorontap.com.ng" style="color: #166534; text-decoration: underline;">inquiries@doctorontap.com.ng</a>
                                </p>
                                <p style="margin: 5px 0; font-size: 14px; color: #166534;">
                                    📞 <a href="tel:08177777122" style="color: #166534; text-decoration: underline;">0817 777 7122</a>
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td class="footer" style="padding: 20px 25px; background-color: #f9fafb; border-top: 1px solid #e5e7eb; border-radius: 0 0 10px 10px; text-align: center; font-size: 12px; color: #6b7280; line-height: 1.6;">
                            <p style="margin: 0 0 5px 0;">
                                This email contains confidential medical information intended only for the recipient.
                            </p>
                            <p style="margin: 0;">
                                © {{ date('Y') }} DoctorOnTap. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
