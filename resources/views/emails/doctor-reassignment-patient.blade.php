<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Reassignment Notice | DoctorOnTap</title>
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
        .doctor-info {
            background: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .doctor-info h3 {
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔄 Doctor Reassignment Notice</h1>
            <p>Your consultation has been updated</p>
        </div>
        
        <div class="content">
            <h2>Hello {{ $data['first_name'] ?? 'Patient' }},</h2>
            
            <p>We're writing to inform you that your consultation has been reassigned to a different doctor.</p>
            
            <div class="info-box">
                <strong>Consultation Reference:</strong> {{ $data['consultation_reference'] ?? 'N/A' }}<br>
                <strong>Patient:</strong> {{ ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '') }}<br>
                <strong>Date:</strong> {{ now()->format('F d, Y') }}
            </div>
            
            <div class="doctor-info">
                <h3>👨‍⚕️ Doctor Information</h3>
                <p><strong>Previous Doctor:</strong> {{ $data['old_doctor'] ?? 'No Doctor' }}</p>
                <p><strong>New Doctor:</strong> Dr. {{ $data['new_doctor'] ?? 'N/A' }}</p>
                @if(isset($data['doctor_fee']) && $data['doctor_fee'] > 0)
                <p><strong>Consultation Fee:</strong> ₦{{ number_format($data['doctor_fee'], 2) }}</p>
                @endif
            </div>
            
            <p><strong>What happens next?</strong></p>
            <ul>
                <li>Dr. {{ $data['new_doctor'] ?? 'your new doctor' }} will review your consultation details</li>
                <li>You'll be contacted shortly via WhatsApp or phone</li>
                <li>Your consultation will proceed as scheduled</li>
            </ul>
            
            <p>If you have any questions or concerns about this reassignment, please don't hesitate to contact us.</p>
            
            @if(isset($data['consultation_id']))
            <a href="{{ route('patient.consultation.view', $data['consultation_id']) }}" class="button">View Consultation Details</a>
            @else
            <a href="{{ route('consultation.index') }}" class="button">View Consultation Details</a>
            @endif
        </div>
        
        <div class="footer">
            <p><strong>DoctorOnTap Healthcare</strong></p>
            <p>Your trusted healthcare partner</p>
            <p style="margin-top: 15px; font-size: 0.85rem;">
                This is an automated notification. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>

