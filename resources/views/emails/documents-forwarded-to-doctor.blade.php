<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Medical Documents | DoctorOnTap</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f9;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 650px;
            margin: 30px auto;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: white;
            text-align: center;
            padding: 35px 20px;
        }
        .header img.logo {
            max-width: 200px;
            height: auto;
            margin: 0 auto 15px;
            display: block;
        }
        .header h1 {
            font-size: 1.8rem;
            margin: 0 0 8px 0;
        }
        .header p {
            font-size: 0.95rem;
            opacity: 0.9;
            margin: 0;
        }
        .content {
            padding: 35px 30px;
            line-height: 1.6;
        }
        .content h2 {
            color: #16a34a;
            margin-top: 0;
            font-size: 1.5rem;
        }
        .info-box {
            background: #f0fdf4;
            border-left: 4px solid #16a34a;
            padding: 18px;
            margin: 20px 0;
            border-radius: 6px;
        }
        .info-box h3 {
            margin: 0 0 12px 0;
            color: #16a34a;
            font-size: 1.1rem;
        }
        .info-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: 600;
            color: #4b5563;
            min-width: 140px;
        }
        .value {
            color: #1f2937;
        }
        .document-list {
            background: #f9fafb;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        .document-item {
            display: flex;
            align-items: center;
            padding: 10px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            margin-bottom: 8px;
        }
        .document-item:last-child {
            margin-bottom: 0;
        }
        .document-icon {
            width: 40px;
            height: 40px;
            background: #16a34a;
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 1.2rem;
        }
        .attachment-notice {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
        }
        .footer {
            background: #f9fafb;
            padding: 25px 30px;
            text-align: center;
            color: #6b7280;
            font-size: 0.9rem;
            border-top: 1px solid #e5e7eb;
        }
        .footer a {
            color: #16a34a;
            text-decoration: none;
        }
        .severity-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .mild {
            background: #dcfce7;
            color: #166534;
        }
        .moderate {
            background: #fef3c7;
            color: #92400e;
        }
        .severe {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ env('APP_URL') }}/img/whitelogo.png" alt="DoctorOnTap Logo" class="logo">
            <h1>📎 Patient Medical Documents</h1>
            <p>Consultation Reference: {{ $consultation->reference }}</p>
        </div>

        <div class="content">
            <h2>Dear Dr. {{ $consultation->doctor->name }},</h2>
            <p>
                The admin has forwarded patient medical documents for your upcoming consultation. 
                Please review these documents before your scheduled consultation with the patient.
            </p>

            <div class="info-box">
                <h3>👤 Patient Information</h3>
                <div class="info-row">
                    <span class="label">Name:</span>
                    <span class="value">{{ $consultation->full_name }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Age / Gender:</span>
                    <span class="value">{{ $consultation->age }} years / {{ ucfirst($consultation->gender) }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Contact:</span>
                    <span class="value">{{ $consultation->mobile }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Email:</span>
                    <span class="value">{{ $consultation->email }}</span>
                </div>
            </div>

            <div class="info-box">
                <h3>🏥 Medical Information</h3>
                <div class="info-row">
                    <span class="label">Chief Complaint:</span>
                    <span class="value">{{ $consultation->problem }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Severity:</span>
                    <span class="value">
                        <span class="severity-badge {{ strtolower($consultation->severity) }}">
                            {{ ucfirst($consultation->severity) }}
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">Consult Mode:</span>
                    <span class="value">{{ ucfirst($consultation->consult_mode) }}</span>
                </div>
            </div>

            @if($consultation->emergency_symptoms && count($consultation->emergency_symptoms) > 0)
            <div class="attachment-notice">
                <strong>⚠️ Emergency Symptoms Reported:</strong>
                <ul style="margin: 8px 0 0 20px; padding: 0;">
                    @foreach($consultation->emergency_symptoms as $symptom)
                    <li>{{ ucwords(str_replace('_', ' ', $symptom)) }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="attachment-notice">
                <strong>📎 Attached Documents:</strong>
                <p style="margin: 8px 0 0 0;">
                    {{ count($consultation->medical_documents) }} medical document(s) are attached to this email. 
                    These files contain patient test results, lab reports, or other relevant medical records.
                </p>
            </div>

            <div class="document-list">
                <strong style="display: block; margin-bottom: 10px;">Document List:</strong>
                @foreach($consultation->medical_documents as $document)
                <div class="document-item">
                    <div class="document-icon">📄</div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: #1f2937;">{{ $document['original_name'] }}</div>
                        <div style="font-size: 0.85rem; color: #6b7280;">
                            {{ number_format($document['size'] / 1024, 2) }} KB
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <p style="margin-top: 25px;">
                Please review these documents at your earliest convenience. If you have any questions or need 
                additional information, please contact our admin team.
            </p>

            <p style="margin-top: 20px; color: #6b7280; font-size: 0.9rem;">
                <strong>Note:</strong> These documents are confidential and should be handled in accordance 
                with patient privacy regulations.
            </p>
        </div>

        <div class="footer">
            <p>
                <strong>DoctorOnTap</strong><br>
                Professional Healthcare Services<br>
                Need assistance? Contact us at <a href="mailto:inquiries@doctorontap.com.ng">inquiries@doctorontap.com.ng</a>
            </p>
            <p style="margin-top: 15px; font-size: 0.85rem;">
                © {{ date('Y') }} DoctorOnTap. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>

