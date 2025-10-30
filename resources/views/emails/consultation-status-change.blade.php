<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation Status Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #9333EA 0%, #7E22CE 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .status-change {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .consultation-details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-label {
            font-weight: bold;
            color: #666;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-scheduled { background: #cce5ff; color: #004085; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap Logo" style="height: 50px; width: auto; margin: 0 auto 15px auto; display: block;">
        <h1>Consultation Status Update</h1>
        <p>DoctorOnTap Admin Notification</p>
    </div>
    
    <div class="content">
        <h2>Status Change Notification</h2>
        
        <div class="status-change">
            <strong>Consultation Status Updated:</strong><br>
            <span class="status-badge status-{{ strtolower($oldStatus) }}">{{ ucfirst($oldStatus) }}</span>
            <span style="margin: 0 10px;">→</span>
            <span class="status-badge status-{{ strtolower($newStatus) }}">{{ ucfirst($newStatus) }}</span>
        </div>
        
        <div class="consultation-details">
            <h3>Consultation Details</h3>
            
            <div class="detail-row">
                <span class="detail-label">Reference:</span>
                <span>{{ $consultation->reference }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Patient Name:</span>
                <span>{{ $consultation->first_name }} {{ $consultation->last_name }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span>{{ $consultation->email }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Phone:</span>
                <span>{{ $consultation->mobile }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Doctor:</span>
                <span>{{ $doctor->name }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Current Status:</span>
                <span class="status-badge status-{{ strtolower($newStatus) }}">{{ ucfirst($newStatus) }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Updated At:</span>
                <span>{{ now()->format('M d, Y h:i A') }}</span>
            </div>
            
            @if($consultation->doctor_notes)
            <div class="detail-row">
                <span class="detail-label">Doctor Notes:</span>
                <span>{{ $consultation->doctor_notes }}</span>
            </div>
            @endif
        </div>
        
        <p><strong>Action Required:</strong> Please review this consultation status change and take any necessary administrative actions.</p>
        
        <p style="margin-top: 30px;">
            <a href="{{ app_url('admin/consultation/' . $consultation->id) }}" 
               style="background: #9333EA; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">
                View Consultation Details
            </a>
        </p>
    </div>
    
    <div class="footer">
        <p>This is an automated notification from DoctorOnTap.</p>
        <p><strong>DoctorOnTap</strong> — caring for you, just like family</p>
        <p>Please do not reply to this email.</p>
    </div>
</body>
</html>
