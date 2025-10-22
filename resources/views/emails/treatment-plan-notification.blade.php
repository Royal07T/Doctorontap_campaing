<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Plan Ready - DoctorOnTap</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f7f7f7;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            max-width: 150px;
            height: auto;
        }
        .success-badge {
            background-color: #10B981;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            background-color: #10B981;
            color: #ffffff;
            padding: 12px 24px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin: 20px 0;
        }
        .info-box {
            background-color: #F3F4F6;
            border-left: 4px solid #10B981;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.8em;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('img/logo-text.png') }}" alt="DoctorOnTap Logo">
            <h2>Your Treatment Plan is Ready!</h2>
        </div>
        
        <div class="success-badge">✓ Payment Confirmed</div>
        
        <p>Hello {{ $consultation->first_name }},</p>
        
        <p>Great news! Your payment has been confirmed and your treatment plan is now ready for viewing.</p>
        
        <div class="info-box">
            <h3 style="margin-top: 0;">Consultation Details:</h3>
            <p><strong>Reference:</strong> {{ $consultation->reference }}</p>
            <p><strong>Doctor:</strong> {{ $consultation->doctor->name }}</p>
            <p><strong>Date:</strong> {{ $consultation->created_at->format('M d, Y') }}</p>
            <p><strong>Status:</strong> Treatment Plan Ready</p>
        </div>
        
        <p>Your comprehensive treatment plan includes:</p>
        <ul>
            <li>Medical diagnosis</li>
            <li>Detailed treatment plan</li>
            <li>Prescribed medications (if any)</li>
            <li>Follow-up instructions</li>
            <li>Lifestyle recommendations</li>
            <li>Specialist referrals (if needed)</li>
        </ul>
        
        @if($consultation->hasTreatmentPlan())
        <div style="background-color: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3 style="color: #374151; margin-top: 0;">Treatment Plan Summary</h3>
            
            @if($consultation->diagnosis)
            <div style="margin-bottom: 15px;">
                <strong style="color: #1F2937;">Diagnosis:</strong><br>
                <span style="color: #4B5563;">{{ $consultation->diagnosis }}</span>
            </div>
            @endif
            
            @if($consultation->treatment_plan)
            <div style="margin-bottom: 15px;">
                <strong style="color: #1F2937;">Treatment Plan:</strong><br>
                <span style="color: #4B5563; white-space: pre-line;">{{ $consultation->treatment_plan }}</span>
            </div>
            @endif
            
            @if($consultation->prescribed_medications && count($consultation->prescribed_medications) > 0)
            <div style="margin-bottom: 15px;">
                <strong style="color: #1F2937;">Prescribed Medications:</strong><br>
                @foreach($consultation->prescribed_medications as $medication)
                <div style="background-color: #EEF2FF; padding: 10px; margin: 5px 0; border-radius: 4px;">
                    <strong>{{ $medication['name'] }}</strong> - {{ $medication['dosage'] }}<br>
                    <small>Frequency: {{ $medication['frequency'] }} | Duration: {{ $medication['duration'] }}</small>
                </div>
                @endforeach
            </div>
            @endif
            
            @if($consultation->follow_up_instructions)
            <div style="margin-bottom: 15px;">
                <strong style="color: #1F2937;">Follow-up Instructions:</strong><br>
                <span style="color: #4B5563; white-space: pre-line;">{{ $consultation->follow_up_instructions }}</span>
            </div>
            @endif
            
            @if($consultation->lifestyle_recommendations)
            <div style="margin-bottom: 15px;">
                <strong style="color: #1F2937;">Lifestyle Recommendations:</strong><br>
                <span style="color: #4B5563; white-space: pre-line;">{{ $consultation->lifestyle_recommendations }}</span>
            </div>
            @endif
            
            @if($consultation->next_appointment_date)
            <div style="margin-bottom: 15px;">
                <strong style="color: #1F2937;">Next Appointment:</strong><br>
                <span style="color: #4B5563;">{{ \Carbon\Carbon::parse($consultation->next_appointment_date)->format('M d, Y') }}</span>
            </div>
            @endif
        </div>
        @endif
        
        <div style="text-align: center;">
            <a href="{{ route('treatment-plan.view', $consultation->reference) }}" class="button">
                View Your Treatment Plan
            </a>
        </div>
        
        <p><strong>Important:</strong> This treatment plan is confidential and should not be shared without your doctor's permission.</p>
        
        <p>If you have any questions about your treatment plan, please contact our support team.</p>
        
        <div class="footer">
            <p>This email was sent from DoctorOnTap.</p>
            <p><strong>DoctorOnTap</strong> — caring for you, just like family</p>
            <p>&copy; 2025 DoctorOnTap. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
