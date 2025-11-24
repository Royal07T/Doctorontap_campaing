<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Plan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #9333EA;
        }
        .logo-img {
            max-width: 180px;
            height: auto;
            margin: 0 auto 10px;
            display: block;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #9333EA;
            margin-bottom: 5px;
        }
        .tagline {
            color: #6b7280;
            font-size: 11px;
        }
        .report-title {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 25px;
            border-radius: 5px;
        }
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #f3f4f6;
            padding: 10px 15px;
            font-size: 13px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 15px;
            border-left: 4px solid #9333EA;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            padding: 8px 15px;
            font-weight: 600;
            color: #4b5563;
            width: 35%;
            border-bottom: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }
        .info-value {
            display: table-cell;
            padding: 8px 15px;
            color: #1f2937;
            border-bottom: 1px solid #e5e7eb;
        }
        .content-box {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            padding: 15px;
            margin-top: 10px;
            border-radius: 5px;
            white-space: pre-line;
            font-size: 11px;
            line-height: 1.6;
        }
        .medication-card {
            background-color: #EEF2FF;
            border-left: 3px solid #6366F1;
            padding: 12px;
            margin: 8px 0;
            border-radius: 4px;
        }
        .medication-name {
            font-weight: bold;
            color: #1f2937;
            font-size: 12px;
        }
        .medication-details {
            font-size: 10px;
            color: #4b5563;
            margin-top: 5px;
        }
        .disclaimer {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            padding: 12px;
            margin-top: 25px;
            font-size: 10px;
            color: #991b1b;
            border-radius: 3px;
        }
        .disclaimer strong {
            color: #7f1d1d;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }
        .footer-info {
            margin-bottom: 10px;
        }
        .footer-info strong {
            color: #1f2937;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            background-color: #10b981;
            color: white;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
        }
        .highlight-box {
            background-color: #FEF3C7;
            border-left: 4px solid #f59e0b;
            padding: 12px;
            margin-top: 15px;
            border-radius: 3px;
        }
        .info-box {
            background-color: #EFF6FF;
            border-left: 4px solid #3B82F6;
            padding: 12px;
            margin-top: 15px;
            border-radius: 3px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <img src="{{ public_path('img/sitelogo.png') }}" alt="DoctorOnTap" class="logo-img">
        <div class="tagline">caring for you, just like family</div>
    </div>

    <!-- Report Title -->
    <div class="report-title">
        YOUR TREATMENT PLAN
    </div>

    <!-- Patient Information -->
    <div class="section">
        <div class="section-title">Patient Information</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Patient Name</div>
                <div class="info-value">{{ $consultation->first_name }} {{ $consultation->last_name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Age / Gender</div>
                <div class="info-value">{{ $consultation->age }} years / {{ ucfirst($consultation->gender) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Reference Number</div>
                <div class="info-value">{{ $consultation->reference }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Consultation Date</div>
                <div class="info-value">{{ $consultation->created_at->format('F d, Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Doctor Information -->
    @if($consultation->doctor)
    <div class="section">
        <div class="section-title">Your Consulting Doctor</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Doctor Name</div>
                <div class="info-value">{{ $consultation->doctor->full_name }}</div>
            </div>
            @if($consultation->doctor->specialization)
            <div class="info-row">
                <div class="info-label">Specialization</div>
                <div class="info-value">{{ $consultation->doctor->specialization }}</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Information Notice -->
    <div class="info-box">
        <strong>📋 About This Document:</strong> This treatment plan contains your personalized medical recommendations, 
        prescriptions, and care instructions from your doctor. Please follow these instructions carefully and contact 
        your doctor if you have any questions or concerns.
    </div>

    <!-- Treatment Plan -->
    @if($consultation->treatment_plan)
    <div class="section">
        <div class="section-title">🩺 Treatment Plan</div>
        <div class="content-box">{{ $consultation->treatment_plan }}</div>
    </div>
    @endif

    <!-- Prescribed Medications -->
    @if($consultation->prescribed_medications && count($consultation->prescribed_medications) > 0)
    <div class="section">
        <div class="section-title">💊 Prescribed Medications</div>
        @foreach($consultation->prescribed_medications as $medication)
        <div class="medication-card">
            <div class="medication-name">{{ $medication['name'] ?? 'N/A' }}</div>
            <div class="medication-details">
                <strong>Dosage:</strong> {{ $medication['dosage'] ?? 'N/A' }}<br>
                <strong>Frequency:</strong> {{ $medication['frequency'] ?? 'N/A' }}<br>
                <strong>Duration:</strong> {{ $medication['duration'] ?? 'N/A' }}
            </div>
            @if(isset($medication['instructions']) && !empty($medication['instructions']))
            <div class="medication-details" style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #C7D2FE;">
                <strong>Special Instructions:</strong> {{ $medication['instructions'] }}
            </div>
            @endif
        </div>
        @endforeach
        
        <div class="info-box" style="margin-top: 15px;">
            <strong>⚠️ Medication Safety Tips:</strong><br>
            • Take medications exactly as prescribed<br>
            • Do not stop taking medication without consulting your doctor<br>
            • If you experience any side effects, contact your doctor immediately<br>
            • Store medications in a cool, dry place away from children
        </div>
    </div>
    @endif

    <!-- Follow-up Instructions -->
    @if($consultation->follow_up_instructions)
    <div class="section">
        <div class="section-title">📅 Follow-up Instructions</div>
        <div class="content-box">{{ $consultation->follow_up_instructions }}</div>
    </div>
    @endif

    <!-- Lifestyle Recommendations -->
    @if($consultation->lifestyle_recommendations)
    <div class="section">
        <div class="section-title">🌟 Lifestyle Recommendations</div>
        <div class="content-box">{{ $consultation->lifestyle_recommendations }}</div>
    </div>
    @endif

    <!-- Next Appointment -->
    @if($consultation->next_appointment_date)
    <div class="section">
        <div class="section-title">🗓️ Next Appointment</div>
        <div class="highlight-box">
            <strong>Scheduled Date:</strong> {{ \Carbon\Carbon::parse($consultation->next_appointment_date)->format('F d, Y') }}
            <br><br>
            Please mark this date on your calendar and contact us if you need to reschedule.
        </div>
    </div>
    @endif

    <!-- Referrals -->
    @if($consultation->referrals && is_array($consultation->referrals) && count($consultation->referrals) > 0)
    <div class="section">
        <div class="section-title">🏥 Specialist Referrals</div>
        <div class="content-box">
            @foreach($consultation->referrals as $referral)
                <div style="margin-bottom: 8px;">• {{ is_array($referral) ? implode(', ', $referral) : $referral }}</div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Additional Notes -->
    @if($consultation->additional_notes)
    <div class="section">
        <div class="section-title">📝 Additional Notes</div>
        <div class="content-box">{{ $consultation->additional_notes }}</div>
    </div>
    @endif

    <!-- When to Seek Help -->
    <div class="section">
        <div class="section-title" style="background-color: #FEE2E2; color: #991B1B; border-left-color: #DC2626;">
            🚨 When to Seek Immediate Medical Care
        </div>
        <div class="content-box" style="border-color: #FECACA; background-color: #FEF2F2;">
            <strong style="color: #991B1B;">Go to the nearest emergency room or call emergency services immediately if you experience:</strong><br><br>
            • Difficulty breathing or severe chest pain<br>
            • Severe allergic reaction (swelling of face/throat, difficulty breathing)<br>
            • Symptoms that suddenly become much worse<br>
            • High fever (above 39°C/102°F) that doesn't respond to medication<br>
            • Severe abdominal pain<br>
            • Confusion, severe headache, or loss of consciousness<br>
            • Any other symptoms your doctor specifically warned you about
        </div>
    </div>

    <!-- Contact Information -->
    <div class="section">
        <div class="section-title">📞 Need Help?</div>
        <div class="info-box">
            <strong>For non-emergency questions about your treatment:</strong><br>
            • Contact your doctor through the DoctorOnTap platform<br>
            • Call our support line: [Your Phone Number]<br>
            • Email: inquiries@doctorontap.com.ng<br>
            • Book a follow-up consultation at: {{ env('APP_URL', 'https://new.doctorontap.com.ng') }}
        </div>
    </div>

    <!-- Disclaimer -->
    <div class="disclaimer">
        <strong>⚠️ Important Medical Notice:</strong> This treatment plan is provided by a licensed healthcare professional and is confidential. 
        Please follow the treatment plan as prescribed and contact your healthcare provider if you have any concerns or questions. 
        Do not share this document without authorization. This document is a medical record protected by privacy laws.
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-info">
            <strong>DoctorOnTap</strong> - caring for you, just like family
        </div>
        <div class="footer-info">
            For questions or to book a follow-up consultation, visit our website or contact our support team.
        </div>
        <div style="margin-top: 15px; color: #9ca3af;">
            Reference: {{ $consultation->reference }}<br>
            Generated on {{ now()->format('F d, Y \a\t h:i A') }}
        </div>
        <div style="margin-top: 10px; font-size: 9px; color: #9ca3af;">
            © {{ date('Y') }} DoctorOnTap. All rights reserved. | Confidential Medical Document
        </div>
    </div>
</body>
</html>

