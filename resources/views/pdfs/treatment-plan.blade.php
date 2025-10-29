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
        TREATMENT PLAN - Reference: {{ $consultation->reference }}
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
                <div class="info-label">Age</div>
                <div class="info-value">{{ $consultation->age }} years</div>
            </div>
            <div class="info-row">
                <div class="info-label">Gender</div>
                <div class="info-value">{{ ucfirst($consultation->gender) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Phone</div>
                <div class="info-value">{{ $consultation->mobile }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email</div>
                <div class="info-value">{{ $consultation->email }}</div>
            </div>
        </div>
    </div>

    <!-- Doctor Information -->
    @if($consultation->doctor)
    <div class="section">
        <div class="section-title">Consulting Doctor</div>
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
            @if($consultation->doctor->consultation_fee)
            <div class="info-row">
                <div class="info-label">Consultation Fee</div>
                <div class="info-value">₦{{ number_format($consultation->doctor->consultation_fee, 2) }}</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Presenting Complaint -->
    @if($consultation->presenting_complaint)
    <div class="section">
        <div class="section-title">1. Presenting Complaint</div>
        <div class="content-box">{{ $consultation->presenting_complaint }}</div>
    </div>
    @endif

    <!-- History of Presenting Complaint -->
    @if($consultation->history_of_complaint)
    <div class="section">
        <div class="section-title">History of Presenting Complaint</div>
        <div class="content-box">{{ $consultation->history_of_complaint }}</div>
    </div>
    @endif

    <!-- Past Medical History -->
    @if($consultation->past_medical_history)
    <div class="section">
        <div class="section-title">2. Past Medical History</div>
        <div class="content-box">{{ $consultation->past_medical_history }}</div>
    </div>
    @endif

    <!-- Family History -->
    @if($consultation->family_history)
    <div class="section">
        <div class="section-title">Family Medical History</div>
        <div class="content-box">{{ $consultation->family_history }}</div>
    </div>
    @endif

    <!-- Drug History -->
    @if($consultation->drug_history)
    <div class="section">
        <div class="section-title">3. Drug History</div>
        <div class="content-box">{{ $consultation->drug_history }}</div>
    </div>
    @endif

    <!-- Social History -->
    @if($consultation->social_history)
    <div class="section">
        <div class="section-title">Social History</div>
        <div class="content-box">{{ $consultation->social_history }}</div>
    </div>
    @endif

    <!-- Diagnosis -->
    @if($consultation->diagnosis)
    <div class="section">
        <div class="section-title">4. Diagnosis</div>
        <div class="content-box">{{ $consultation->diagnosis }}</div>
    </div>
    @endif

    <!-- Investigation -->
    @if($consultation->investigation)
    <div class="section">
        <div class="section-title">5. Investigation</div>
        <div class="content-box">{{ $consultation->investigation }}</div>
    </div>
    @endif

    <!-- Treatment Plan -->
    @if($consultation->treatment_plan)
    <div class="section">
        <div class="section-title">6. Treatment Plan</div>
        <div class="content-box">{{ $consultation->treatment_plan }}</div>
    </div>
    @endif

    <!-- Prescribed Medications -->
    @if($consultation->prescribed_medications && count($consultation->prescribed_medications) > 0)
    <div class="section">
        <div class="section-title">Prescribed Medications</div>
        @foreach($consultation->prescribed_medications as $medication)
        <div class="medication-card">
            <div class="medication-name">{{ $medication['name'] ?? 'N/A' }}</div>
            <div class="medication-details">
                Dosage: {{ $medication['dosage'] ?? 'N/A' }} | 
                Frequency: {{ $medication['frequency'] ?? 'N/A' }} | 
                Duration: {{ $medication['duration'] ?? 'N/A' }}
            </div>
            @if(isset($medication['instructions']) && !empty($medication['instructions']))
            <div class="medication-details" style="margin-top: 5px; font-style: italic;">
                Instructions: {{ $medication['instructions'] }}
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    <!-- Follow-up Instructions -->
    @if($consultation->follow_up_instructions)
    <div class="section">
        <div class="section-title">Follow-up Instructions</div>
        <div class="content-box">{{ $consultation->follow_up_instructions }}</div>
    </div>
    @endif

    <!-- Lifestyle Recommendations -->
    @if($consultation->lifestyle_recommendations)
    <div class="section">
        <div class="section-title">Lifestyle Recommendations</div>
        <div class="content-box">{{ $consultation->lifestyle_recommendations }}</div>
    </div>
    @endif

    <!-- Next Appointment -->
    @if($consultation->next_appointment_date)
    <div class="section">
        <div class="section-title">Next Appointment</div>
        <div class="highlight-box">
            <strong>Date:</strong> {{ \Carbon\Carbon::parse($consultation->next_appointment_date)->format('F d, Y') }}
        </div>
    </div>
    @endif

    <!-- Referrals -->
    @if($consultation->referrals)
    <div class="section">
        <div class="section-title">Referrals</div>
        <div class="content-box">{{ $consultation->referrals }}</div>
    </div>
    @endif

    <!-- Additional Notes -->
    @if($consultation->additional_notes)
    <div class="section">
        <div class="section-title">Additional Notes</div>
        <div class="content-box">{{ $consultation->additional_notes }}</div>
    </div>
    @endif

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
