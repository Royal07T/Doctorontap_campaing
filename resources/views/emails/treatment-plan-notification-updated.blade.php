<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Treatment Plan - DoctorOnTap</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1F2937;
            max-width: 650px;
            margin: 0 auto;
            padding: 20px;
            background-color: #F9FAFB;
        }
        .container {
            background-color: #ffffff;
            padding: 0;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header img {
            max-width: 150px;
            height: auto;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .status-badge {
            background-color: #10B981;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 20px;
        }
        .alert-box {
            background-color: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 20px;
            margin: 25px 0;
            border-radius: 6px;
        }
        .alert-box h3 {
            margin: 0 0 10px 0;
            color: #92400E;
            font-size: 16px;
        }
        .alert-box p {
            margin: 5px 0;
            color: #78350F;
            font-size: 14px;
        }
        .security-box {
            background-color: #EFF6FF;
            border-left: 4px solid #3B82F6;
            padding: 20px;
            margin: 25px 0;
            border-radius: 6px;
        }
        .security-box h3 {
            margin: 0 0 10px 0;
            color: #1E40AF;
            font-size: 16px;
        }
        .consultation-details {
            background-color: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .consultation-details h3 {
            margin: 0 0 15px 0;
            color: #111827;
            font-size: 18px;
            border-bottom: 2px solid #9333EA;
            padding-bottom: 10px;
        }
        .detail-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #E5E7EB;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #6B7280;
            min-width: 140px;
        }
        .detail-value {
            color: #111827;
            flex: 1;
        }
        .treatment-section {
            background-color: #FFFFFF;
            border: 2px solid #9333EA;
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
        }
        .treatment-section h2 {
            margin: 0 0 20px 0;
            color: #9333EA;
            font-size: 22px;
            text-align: center;
        }
        .treatment-item {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #E5E7EB;
        }
        .treatment-item:last-child {
            border-bottom: none;
        }
        .treatment-label {
            font-weight: 700;
            color: #111827;
            font-size: 16px;
            margin-bottom: 8px;
            display: block;
        }
        .treatment-content {
            color: #4B5563;
            white-space: pre-line;
            line-height: 1.8;
        }
        .medication-card {
            background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%);
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid #6366F1;
        }
        .medication-name {
            font-weight: 700;
            color: #312E81;
            font-size: 16px;
        }
        .medication-details {
            color: #4338CA;
            margin-top: 5px;
            font-size: 14px;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
            color: #ffffff;
            padding: 16px 40px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(147, 51, 234, 0.3);
        }
        .hipaa-notice {
            background-color: #F3F4F6;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            font-size: 13px;
            color: #4B5563;
        }
        .hipaa-notice h4 {
            margin: 0 0 10px 0;
            color: #111827;
            font-size: 14px;
        }
        .emergency-box {
            background-color: #FEE2E2;
            border-left: 4px solid #DC2626;
            padding: 20px;
            margin: 25px 0;
            border-radius: 6px;
        }
        .emergency-box h3 {
            margin: 0 0 10px 0;
            color: #991B1B;
            font-size: 16px;
        }
        .contact-info {
            background-color: #F0FDF4;
            border: 1px solid #BBF7D0;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .contact-info h3 {
            margin: 0 0 15px 0;
            color: #166534;
        }
        .contact-item {
            padding: 8px 0;
            color: #166534;
        }
        .footer {
            background-color: #F9FAFB;
            padding: 30px;
            text-align: center;
            font-size: 13px;
            color: #6B7280;
            border-top: 1px solid #E5E7EB;
        }
        .divider {
            height: 1px;
            background-color: #E5E7EB;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap">
            <h1>📋 Your Treatment Plan is Ready</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Status Badge -->
            <center>
                <div class="status-badge">✓ Payment Confirmed - Treatment Plan Unlocked</div>
            </center>

            <p style="font-size: 16px; margin: 20px 0;">
                Hello <strong>{{ $consultation->first_name }}</strong>,
            </p>

            <p>
                Your consultation with <strong>Dr. {{ $consultation->doctor->name }}</strong> is complete, and your personalized treatment plan is ready.
                This document contains important medical information carefully prepared for your care.
            </p>

            <!-- Important Security Notice -->
            <div class="alert-box">
                <h3>🔒 Confidential Medical Information</h3>
                <p><strong>This email contains Protected Health Information (PHI).</strong></p>
                <p style="margin-top: 10px;">
                    ✓ Keep this email secure and private<br>
                    ✓ Do not forward to unauthorized persons<br>
                    ✓ If received in error, delete immediately<br>
                    ✓ Download the PDF and store it securely
                </p>
            </div>

            <!-- Patient Verification -->
            <div class="consultation-details">
                <h3>📋 Consultation Information</h3>
                <div class="detail-row">
                    <div class="detail-label">Reference Number:</div>
                    <div class="detail-value"><strong>{{ $consultation->reference }}</strong></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Patient Name:</div>
                    <div class="detail-value">{{ $consultation->first_name }} {{ $consultation->last_name }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Email:</div>
                    <div class="detail-value">{{ $consultation->email }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Phone:</div>
                    <div class="detail-value">{{ $consultation->mobile }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Attending Doctor:</div>
                    <div class="detail-value">Dr. {{ $consultation->doctor->name }}@if($consultation->doctor->gender) ({{ ucfirst($consultation->doctor->gender) }})@endif</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Consultation Date:</div>
                    <div class="detail-value">{{ $consultation->created_at->format('F d, Y \a\t h:i A') }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Treatment Plan Date:</div>
                    <div class="detail-value">{{ now()->format('F d, Y \a\t h:i A') }}</div>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Treatment Plan Content -->
            <!-- Treatment Plan Content - PATIENT-FRIENDLY VERSION -->
            <!-- Clinical documentation (diagnosis, history, investigations) hidden from patients -->
            <!-- These are available in the doctor's/admin's full PDF version -->
            @if($consultation->hasTreatmentPlan())
            <div class="treatment-section">
                <h2>📋 Your Treatment Plan</h2>

                @if($consultation->treatment_plan)
                <div class="treatment-item">
                    <span class="treatment-label">🩺 Treatment Plan</span>
                    <div class="treatment-content">{{ $consultation->treatment_plan }}</div>
                </div>
                @endif

                @if($consultation->prescribed_medications && count($consultation->prescribed_medications) > 0)
                <div class="treatment-item">
                    <span class="treatment-label">💊 Prescribed Medications</span>
                    @foreach($consultation->prescribed_medications as $medication)
                    <div class="medication-card">
                        <div class="medication-name">{{ $medication['name'] }}</div>
                        <div class="medication-details">
                            <strong>Dosage:</strong> {{ $medication['dosage'] }}<br>
                            <strong>Frequency:</strong> {{ $medication['frequency'] }}<br>
                            <strong>Duration:</strong> {{ $medication['duration'] }}
                        </div>
                    </div>
                    @endforeach
                    <div style="margin-top: 15px; padding: 12px; background-color: #FEF3C7; border-radius: 6px; font-size: 14px;">
                        <strong>⚠️ Medication Safety:</strong> Take exactly as prescribed. Do not stop or change dosage without consulting your doctor. Report any side effects immediately.
                    </div>
                </div>
                @endif

                @if($consultation->follow_up_instructions)
                <div class="treatment-item">
                    <span class="treatment-label">📅 Follow-up Instructions</span>
                    <div class="treatment-content">{{ $consultation->follow_up_instructions }}</div>
                </div>
                @endif

                @if($consultation->lifestyle_recommendations)
                <div class="treatment-item">
                    <span class="treatment-label">🌟 Lifestyle Recommendations</span>
                    <div class="treatment-content">{{ $consultation->lifestyle_recommendations }}</div>
                </div>
                @endif

                @if($consultation->next_appointment_date)
                <div class="treatment-item">
                    <span class="treatment-label">📆 Next Appointment</span>
                    <div class="treatment-content">{{ \Carbon\Carbon::parse($consultation->next_appointment_date)->format('F d, Y') }}</div>
                </div>
                @endif
            </div>
            @endif

            <!-- PDF Attachment Notice -->
            <div class="security-box">
                <h3>📎 Complete Treatment Plan Attached</h3>
                <p>
                    A detailed PDF copy of your treatment plan is attached to this email. 
                    We recommend downloading and storing it securely for your records.
                </p>
                <p style="margin-top: 10px;">
                    You can also view your treatment plan anytime by clicking the button below.
                </p>
            </div>

            <center>
                <a href="{{ route('treatment-plan.view', $consultation->reference) }}" class="cta-button">
                    🔐 View Secure Treatment Plan
                </a>
            </center>

            <div class="divider"></div>

            <!-- Emergency Instructions -->
            <div class="emergency-box">
                <h3>🚨 When to Seek Emergency Care</h3>
                <p style="color: #991B1B;">
                    If you experience any of the following, go to the nearest emergency room or call emergency services immediately:
                </p>
                <ul style="color: #991B1B; margin: 10px 0 10px 20px;">
                    <li>Difficulty breathing or chest pain</li>
                    <li>Severe allergic reaction (swelling, hives, difficulty breathing)</li>
                    <li>Symptoms getting significantly worse</li>
                    <li>High fever that doesn't respond to medication</li>
                    <li>Any condition your doctor marked as "seek immediate care if..."</li>
                </ul>
            </div>

            <!-- Contact Information -->
            <div class="contact-info">
                <h3>💬 Questions About Your Treatment Plan?</h3>
                <p style="color: #166534;">Our team is here to help. Contact us anytime:</p>
                <div class="contact-item">
                    <strong>📧 Email:</strong> inquiries@doctorontap.com.ng
                </div>
                <div class="contact-item">
                    <strong>📱 Phone:</strong> 0817 777 7122
                </div>
                <div class="contact-item">
                    <strong>💬 WhatsApp:</strong> <a href="https://wa.me/2348177777122" style="color: #166534;">Chat with us</a>
                </div>
                <div class="contact-item">
                    <strong>🌐 Website:</strong> <a href="https://doctorontap.com.ng" style="color: #166534;">doctorontap.com.ng</a>
                </div>
            </div>

            <!-- HIPAA & Privacy Notice -->
            <div class="hipaa-notice">
                <h4>🔒 Privacy & Security Notice (HIPAA Compliance)</h4>
                <p>
                    <strong>This email contains confidential medical information protected under HIPAA regulations.</strong>
                </p>
                <p style="margin-top: 10px;">
                    This information is intended only for <strong>{{ $consultation->first_name }} {{ $consultation->last_name }}</strong> 
                    ({{ $consultation->email }}). If you are not the intended recipient, you are hereby notified that any 
                    disclosure, copying, distribution, or taking any action based on the contents of this information is strictly 
                    prohibited.
                </p>
                <p style="margin-top: 10px;">
                    <strong>Your Privacy Rights:</strong>
                </p>
                <ul style="margin: 10px 0 0 20px; font-size: 12px;">
                    <li>Your medical information is stored securely and accessed only by authorized healthcare providers</li>
                    <li>You have the right to access, correct, or request deletion of your medical records</li>
                    <li>We will never share your information without your explicit consent, except as required by law</li>
                    <li>All electronic communications are encrypted and stored securely</li>
                </ul>
                <p style="margin-top: 10px; font-size: 12px;">
                    If you received this email in error, please delete it immediately and notify us at 
                    <a href="mailto:privacy@doctorontap.com.ng">privacy@doctorontap.com.ng</a>
                </p>
            </div>

            <div class="divider"></div>

            <!-- Important Reminders -->
            <div style="background-color: #F9FAFB; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <h3 style="color: #111827; margin: 0 0 15px 0;">📌 Important Reminders</h3>
                <ul style="color: #4B5563; line-height: 1.8;">
                    <li><strong>Follow your treatment plan exactly as prescribed</strong> by your doctor</li>
                    <li><strong>Set reminders</strong> for medications and follow-up appointments</li>
                    <li><strong>Keep this document accessible</strong> for reference and future medical visits</li>
                    <li><strong>Contact us immediately</strong> if you have questions or concerns</li>
                    <li><strong>Complete all recommended investigations</strong> and tests</li>
                    <li><strong>Report any medication side effects</strong> to your doctor promptly</li>
                </ul>
            </div>

            <p style="text-align: center; margin: 30px 0; color: #6B7280; font-size: 14px;">
                <strong>Reference Number:</strong> {{ $consultation->reference }}<br>
                <em>Please include this reference number in any correspondence</em>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin-bottom: 15px;">
                <strong style="color: #111827;">DoctorOnTap</strong><br>
                Caring for you, just like family
            </p>
            <p>
                📧 inquiries@doctorontap.com.ng | 📱 0817 777 7122<br>
                🌐 <a href="https://doctorontap.com.ng" style="color: #9333EA;">www.doctorontap.com.ng</a>
            </p>
            <p style="margin-top: 15px; font-size: 12px;">
                This is a secure, confidential medical communication.<br>
                © {{ date('Y') }} DoctorOnTap. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>

