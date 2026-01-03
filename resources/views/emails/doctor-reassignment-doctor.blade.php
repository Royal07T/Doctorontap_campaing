<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Consultation Assignment | DoctorOnTap</title>
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f6f8fc;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 640px;
            margin: 40px auto;
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }
        .header {
            background: linear-gradient(135deg, #5A67D8 0%, #805AD5 100%);
            color: white;
            text-align: center;
            padding: 40px 25px;
        }
        .header h1 {
            font-size: 2rem;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .content {
            padding: 35px;
            line-height: 1.7;
        }
        .content h2 {
            color: #5A67D8;
            font-size: 1.5rem;
            margin-top: 0 0 20px 0;
        }
        .info-box {
            background: #f0f4ff;
            border-left: 4px solid #5A67D8;
            padding: 20px;
            margin: 25px 0;
            border-radius: 8px;
        }
        .patient-info {
            background: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .patient-info h3 {
            color: #5A67D8;
            margin-top: 0;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #5A67D8 0%, #805AD5 100%);
            color: white;
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
        }
        .footer {
            background: #f8f9fa;
            padding: 25px;
            text-align: center;
            color: #6c757d;
            font-size: 0.9rem;
        }
        .severity-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .severity-mild { background: #d1fae5; color: #065f46; }
        .severity-moderate { background: #fef3c7; color: #92400e; }
        .severity-severe { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👨‍⚕️ New Consultation Assignment</h1>
            <p>A consultation has been assigned to you</p>
        </div>
        
        <div class="content">
            <h2>Hello Dr. {{ $data['doctor'] ?? 'Doctor' }},</h2>
            
            @if(isset($data['is_reassignment']) && $data['is_reassignment'])
            <p>A consultation has been reassigned to you. Please review the patient details below.</p>
            @else
            <p>A new consultation has been assigned to you. Please review the patient details below.</p>
            @endif
            
            <div class="info-box">
                <strong>Consultation Reference:</strong> {{ $data['consultation_reference'] ?? 'N/A' }}<br>
                <strong>Consultation Mode:</strong> {{ ucfirst(str_replace('_', ' ', $data['consult_mode'] ?? 'N/A')) }}<br>
                @if(isset($data['doctor_fee']) && $data['doctor_fee'] > 0)
                <strong>Consultation Fee:</strong> ₦{{ number_format($data['doctor_fee'], 2) }}<br>
                @endif
            </div>
            
            <div class="patient-info">
                <h3>👤 Patient Information</h3>
                <p><strong>Name:</strong> {{ ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '') }}</p>
                <p><strong>Age:</strong> {{ $data['age'] ?? 'N/A' }} years</p>
                <p><strong>Gender:</strong> {{ ucfirst($data['gender'] ?? 'N/A') }}</p>
                <p><strong>Email:</strong> {{ $data['email'] ?? 'N/A' }}</p>
                <p><strong>Mobile:</strong> {{ $data['mobile'] ?? 'N/A' }}</p>
                <p><strong>Severity:</strong> 
                    <span class="severity-badge severity-{{ $data['severity'] ?? 'moderate' }}">
                        {{ ucfirst($data['severity'] ?? 'Moderate') }}
                    </span>
                </p>
            </div>
            
            <div class="patient-info">
                <h3>📋 Medical Details</h3>
                <p><strong>Problem/Complaint:</strong></p>
                <p>{{ $data['problem'] ?? 'Not specified' }}</p>
                
                @if(!empty($data['emergency_symptoms']))
                <p style="margin-top: 15px;"><strong>Emergency Symptoms:</strong></p>
                <ul>
                    @foreach($data['emergency_symptoms'] as $symptom)
                    <li>{{ $symptom }}</li>
                    @endforeach
                </ul>
                @endif
                
                @if(isset($data['has_documents']) && $data['has_documents'])
                <p style="margin-top: 15px;">
                    <strong>📎 Medical Documents:</strong> {{ $data['documents_count'] ?? 0 }} file(s) attached
                </p>
                @endif
            </div>
            
            <p><strong>Next Steps:</strong></p>
            <ul>
                <li>Review the patient's consultation details</li>
                <li>Contact the patient via WhatsApp or phone</li>
                <li>Proceed with the consultation as scheduled</li>
            </ul>
            
            <a href="{{ route('doctor.consultations.view', ['id' => $data['consultation_id'] ?? '']) }}" class="button">View Consultation Details</a>
        </div>
        
        <div class="footer">
            <p><strong>DoctorOnTap Healthcare</strong></p>
            <p>Thank you for your service</p>
            <p style="margin-top: 15px; font-size: 0.85rem;">
                This is an automated notification. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>

