<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vital Signs Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
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
            font-size: 14px;
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
            padding: 10px 15px;
            font-weight: 600;
            color: #4b5563;
            width: 40%;
            border-bottom: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }
        .info-value {
            display: table-cell;
            padding: 10px 15px;
            color: #1f2937;
            border-bottom: 1px solid #e5e7eb;
        }
        .vital-card {
            border: 1px solid #e5e7eb;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            background-color: #ffffff;
        }
        .vital-card-header {
            font-weight: bold;
            color: #9333EA;
            font-size: 13px;
            margin-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        .vital-card-value {
            font-size: 20px;
            font-weight: bold;
            color: #1f2937;
            margin-top: 5px;
        }
        .vital-card-unit {
            font-size: 11px;
            color: #6b7280;
            margin-left: 5px;
        }
        .vitals-grid {
            display: table;
            width: 100%;
            margin-top: 15px;
        }
        .vitals-row {
            display: table-row;
        }
        .vitals-cell {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            vertical-align: top;
        }
        .notes-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin-top: 15px;
            border-radius: 3px;
        }
        .notes-title {
            font-weight: bold;
            color: #92400e;
            margin-bottom: 8px;
            font-size: 13px;
        }
        .notes-content {
            color: #78350f;
            font-size: 11px;
            line-height: 1.5;
        }
        .interpretation {
            background-color: #dbeafe;
            border-left: 4px solid #3b82f6;
            padding: 12px 15px;
            margin-top: 10px;
            font-size: 11px;
            color: #1e40af;
            border-radius: 3px;
        }
        .interpretation strong {
            color: #1e3a8a;
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
        .badge {
            display: inline-block;
            padding: 4px 10px;
            background-color: #10b981;
            color: white;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            margin-left: 10px;
        }
        .badge.warning {
            background-color: #f59e0b;
        }
        .badge.danger {
            background-color: #ef4444;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">DOCTORONTAP</div>
        <div class="tagline">Quality Healthcare, Anytime, Anywhere</div>
    </div>

    <!-- Report Title -->
    <div class="report-title">
        VITAL SIGNS REPORT
    </div>

    <!-- Patient Information -->
    <div class="section">
        <div class="section-title">📋 Patient Information</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Patient Name</div>
                <div class="info-value">{{ $patient->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Gender</div>
                <div class="info-value">{{ ucfirst($patient->gender) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Phone Number</div>
                <div class="info-value">{{ $patient->phone }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email Address</div>
                <div class="info-value">{{ $patient->email }}</div>
            </div>
        </div>
    </div>

    <!-- Recording Information -->
    <div class="section">
        <div class="section-title">🩺 Recording Details</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Recorded By</div>
                <div class="info-value">{{ $nurse->name }} (Nurse)</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date & Time</div>
                <div class="info-value">{{ $vitalSign->created_at->format('F d, Y h:i A') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Report Generated</div>
                <div class="info-value">{{ now()->format('F d, Y h:i A') }}</div>
            </div>
        </div>
    </div>

    <!-- Vital Signs Measurements -->
    <div class="section">
        <div class="section-title">❤️ Vital Signs Measurements</div>
        
        <div class="vitals-grid">
            <div class="vitals-row">
                <!-- Blood Pressure -->
                @if($vitalSign->blood_pressure)
                <div class="vitals-cell">
                    <div class="vital-card">
                        <div class="vital-card-header">Blood Pressure</div>
                        <div class="vital-card-value">
                            {{ $vitalSign->blood_pressure }}
                            <span class="vital-card-unit">mmHg</span>
                        </div>
                        @if($vitalSign->blood_pressure_interpretation)
                        <div class="interpretation">
                            <strong>Status:</strong> {{ $vitalSign->blood_pressure_interpretation }}
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Heart Rate -->
                @if($vitalSign->heart_rate)
                <div class="vitals-cell">
                    <div class="vital-card">
                        <div class="vital-card-header">Heart Rate</div>
                        <div class="vital-card-value">
                            {{ $vitalSign->heart_rate }}
                            <span class="vital-card-unit">bpm</span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Oxygen Saturation -->
                @if($vitalSign->oxygen_saturation)
                <div class="vitals-cell">
                    <div class="vital-card">
                        <div class="vital-card-header">Oxygen Saturation</div>
                        <div class="vital-card-value">
                            {{ $vitalSign->oxygen_saturation }}
                            <span class="vital-card-unit">%</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="vitals-row">
                <!-- Temperature -->
                @if($vitalSign->temperature)
                <div class="vitals-cell">
                    <div class="vital-card">
                        <div class="vital-card-header">Temperature</div>
                        <div class="vital-card-value">
                            {{ $vitalSign->temperature }}
                            <span class="vital-card-unit">°C</span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Respiratory Rate -->
                @if($vitalSign->respiratory_rate)
                <div class="vitals-cell">
                    <div class="vital-card">
                        <div class="vital-card-header">Respiratory Rate</div>
                        <div class="vital-card-value">
                            {{ $vitalSign->respiratory_rate }}
                            <span class="vital-card-unit">breaths/min</span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Blood Sugar -->
                @if($vitalSign->blood_sugar)
                <div class="vitals-cell">
                    <div class="vital-card">
                        <div class="vital-card-header">Blood Sugar</div>
                        <div class="vital-card-value">
                            {{ $vitalSign->blood_sugar }}
                            <span class="vital-card-unit">mg/dL</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Physical Measurements -->
    @if($vitalSign->height || $vitalSign->weight)
    <div class="section">
        <div class="section-title">📏 Physical Measurements</div>
        <div class="info-grid">
            @if($vitalSign->height)
            <div class="info-row">
                <div class="info-label">Height</div>
                <div class="info-value">{{ $vitalSign->height }} cm</div>
            </div>
            @endif

            @if($vitalSign->weight)
            <div class="info-row">
                <div class="info-label">Weight</div>
                <div class="info-value">{{ $vitalSign->weight }} kg</div>
            </div>
            @endif

            @if($vitalSign->bmi)
            <div class="info-row">
                <div class="info-label">BMI (Body Mass Index)</div>
                <div class="info-value">
                    {{ number_format($vitalSign->bmi, 1) }}
                    @if($vitalSign->bmi < 18.5)
                        <span class="badge warning">Underweight</span>
                    @elseif($vitalSign->bmi >= 18.5 && $vitalSign->bmi < 25)
                        <span class="badge">Normal</span>
                    @elseif($vitalSign->bmi >= 25 && $vitalSign->bmi < 30)
                        <span class="badge warning">Overweight</span>
                    @else
                        <span class="badge danger">Obese</span>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Nurse's Notes -->
    @if($vitalSign->notes)
    <div class="section">
        <div class="notes-box">
            <div class="notes-title">📝 Nurse's Observations & Notes</div>
            <div class="notes-content">{{ $vitalSign->notes }}</div>
        </div>
    </div>
    @endif

    <!-- Disclaimer -->
    <div class="disclaimer">
        <strong>⚠️ Important Notice:</strong> This report is for informational purposes only and does not constitute medical advice. 
        Please consult with a qualified healthcare professional for interpretation of these results and any health concerns. 
        If you are experiencing a medical emergency, please call emergency services immediately.
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-info">
            <strong>DoctorOnTap</strong> - Quality Healthcare, Anytime, Anywhere
        </div>
        <div class="footer-info">
            For questions or to book a consultation, visit our website or contact our support team.
        </div>
        <div style="margin-top: 15px; color: #9ca3af;">
            Report ID: VSR-{{ $vitalSign->id }}-{{ date('Ymd') }}<br>
            Generated on {{ now()->format('F d, Y \a\t h:i A') }}
        </div>
        <div style="margin-top: 10px; font-size: 9px; color: #9ca3af;">
            © {{ date('Y') }} DoctorOnTap. All rights reserved. | Confidential Medical Record
        </div>
    </div>
</body>
</html>

