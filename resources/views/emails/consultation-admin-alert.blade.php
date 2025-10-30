<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header img.logo {
            max-width: 200px;
            height: auto;
            margin: 0 auto 15px;
            display: block;
        }
        .header h1 {
            margin: 0;
            font-size: 2rem;
        }
        .content {
            padding: 30px;
        }
        .content h2 {
            color: #667eea;
            margin-top: 0;
        }
        .patient-info {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        .patient-info h3 {
            margin-top: 0;
            color: #856404;
        }
        .info-row {
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #667eea;
            display: inline-block;
            width: 120px;
        }
        .symptoms-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            white-space: pre-wrap;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 0.9rem;
            color: #666;
        }
        .urgent {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 10px 15px;
            margin: 15px 0;
            color: #721c24;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ app_url('img/whitelogo.png') }}" alt="DoctorOnTap Logo" class="logo">
            <h1>🚨 New Consultation Request</h1>
            <p>DoctorOnTap Admin Alert</p>
        </div>
        
        <div class="content">
            <h2>Action Required: New Patient Consultation Booking</h2>
            
            <p>A new consultation has been booked through the DoctorOnTap "Pay After Consult" campaign.</p>
            
            <div class="patient-info">
                <h3>📋 Patient Information</h3>
                
                <div class="info-row">
                    <span class="label">Full Name:</span>
                    <span>{{ $data['first_name'] }} {{ $data['last_name'] }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Email:</span>
                    <span>{{ $data['email'] }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Mobile/WhatsApp:</span>
                    <span>{{ $data['mobile'] }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Age:</span>
                    <span>{{ $data['age'] }} years</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Gender:</span>
                    <span>{{ ucfirst($data['gender']) }}</span>
                </div>
            </div>
            
            <div style="background: #e8f4fd; border-left: 4px solid #0066cc; padding: 15px; margin: 20px 0;">
                <h3 style="margin-top: 0; color: #0066cc;">🩺 Medical Triage Information</h3>
                
                <div class="info-row">
                    <span class="label">Problem:</span>
                    <span>{{ $data['problem'] }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Severity:</span>
                    <span><strong>{{ strtoupper($data['severity']) }}</strong></span>
                </div>

                @if(isset($data['has_documents']) && $data['has_documents'])
                <div class="info-row">
                    <span class="label">📎 Medical Documents:</span>
                    <span><strong style="color: #16a34a;">{{ $data['documents_count'] }} file(s) uploaded ✓</strong></span>
                </div>
                @endif
                
                @if(isset($data['emergency_symptoms']) && is_array($data['emergency_symptoms']) && count($data['emergency_symptoms']) > 0)
                <div class="urgent">
                    <strong>⚠️ EMERGENCY SYMPTOMS REPORTED:</strong>
                    <ul style="margin: 10px 0 5px 20px; padding: 0;">
                        @foreach($data['emergency_symptoms'] as $symptom)
                            <li style="margin: 5px 0;"><strong>{{ ucfirst(str_replace('_', ' ', $symptom)) }}</strong></li>
                        @endforeach
                    </ul>
                    <p style="margin: 10px 0 0 0;">Please prioritize this consultation!</p>
                </div>
                @else
                <div class="info-row">
                    <span class="label">Emergency Symptoms:</span>
                    <span>None reported</span>
                </div>
                @endif
            </div>
            
            <div style="background: #f0f7ff; border-left: 4px solid #667eea; padding: 15px; margin: 20px 0;">
                <h3 style="margin-top: 0; color: #667eea;">👨‍⚕️ Consultation Preferences</h3>
                
                <div class="info-row">
                    <span class="label">Consult Mode:</span>
                    <span><strong>{{ strtoupper($data['consult_mode']) }}</strong></span>
                </div>
                
                @if(isset($data['doctor']) && !empty($data['doctor']))
                <div class="info-row">
                    <span class="label">Preferred Doctor:</span>
                    <span>{{ $data['doctor'] }}</span>
                </div>
                @else
                <div class="info-row">
                    <span class="label">Preferred Doctor:</span>
                    <span>Any Available Doctor</span>
                </div>
                @endif
                
                @if(isset($data['doctor_fee']))
                <div class="info-row">
                    <span class="label">Consultation Fee:</span>
                    <span><strong>NGN {{ number_format($data['doctor_fee'], 2) }}</strong></span>
                </div>
                @endif
            </div>
            
            <div class="urgent">
                ⚕️ Please contact patient via WhatsApp within 1-2 hours to confirm consultation time
            </div>
            
            <p style="margin-top: 30px;">
                <strong>Next Steps:</strong>
            </p>
            <ol>
                <li>Contact patient via WhatsApp: <strong>{{ $data['mobile'] }}</strong></li>
                <li>Confirm consultation time and assign doctor</li>
                <li>Conduct {{ $data['consult_mode'] }} consultation</li>
                <li>Process payment AFTER consultation</li>
                <li>Send follow-up care instructions if needed</li>
            </ol>
            
            <p style="margin-top: 20px; color: #666;">
                <em>Booking received at: {{ date('F j, Y, g:i a') }}</em>
            </p>
        </div>
        
        <div class="footer">
            <p>This is an automated admin alert from DoctorOnTap Healthcare Awareness Campaign.</p>
            <p><strong>DoctorOnTap</strong> — caring for you, just like family</p>
            <p>&copy; {{ date('Y') }} DoctorOnTap. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

