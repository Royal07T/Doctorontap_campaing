<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f3f4f6;
    color: #333;
    margin: 0;
    padding: 0;
  }

  .container {
    max-width: 640px;
    margin: 30px auto;
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
  }

  .header {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: #fff;
    text-align: center;
    padding: 40px 20px 30px;
  }

  .header img.logo {
    max-width: 200px;
    height: auto;
    margin: 0 auto 15px;
    display: block;
  }

  .header h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
  }

  .header p {
    font-size: 16px;
    margin-top: 8px;
    opacity: 0.95;
    font-weight: 600;
  }

  .urgent-badge {
    background: #fff;
    color: #dc2626;
    padding: 8px 20px;
    border-radius: 20px;
    display: inline-block;
    font-weight: 700;
    font-size: 14px;
    margin-top: 15px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .section {
    padding: 35px 30px;
  }

  .alert-box {
    background: #fef2f2;
    border-left: 4px solid #dc2626;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
  }

  .alert-box h2 {
    color: #dc2626;
    margin: 0 0 10px 0;
    font-size: 20px;
    font-weight: 700;
  }

  .alert-box p {
    color: #7f1d1d;
    margin: 0;
    font-size: 15px;
    line-height: 1.6;
  }

  .info-box {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 20px;
    margin: 20px 0;
  }

  .info-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #e5e7eb;
  }

  .info-row:last-child {
    border-bottom: none;
  }

  .info-label {
    font-weight: 600;
    color: #6b7280;
    font-size: 14px;
  }

  .info-value {
    color: #111827;
    font-weight: 500;
    font-size: 14px;
    text-align: right;
  }

  .cta-button {
    display: block;
    text-align: center;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: #fff;
    padding: 16px 30px;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 700;
    font-size: 16px;
    margin: 30px 0;
    transition: transform 0.2s;
  }

  .cta-button:hover {
    transform: translateY(-2px);
  }

  .footer {
    background: #f9fafb;
    padding: 25px 30px;
    text-align: center;
    border-top: 1px solid #e5e7eb;
  }

  .footer p {
    color: #6b7280;
    font-size: 13px;
    margin: 5px 0;
    line-height: 1.6;
  }

  .footer a {
    color: #6C3EF3;
    text-decoration: none;
  }

  @media only screen and (max-width: 600px) {
    .container {
      margin: 0;
      border-radius: 0;
    }
    
    .section {
      padding: 25px 20px;
    }

    .header {
      padding: 30px 20px 25px;
    }

    .header h1 {
      font-size: 24px;
    }
  }
</style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>⚠️ URGENT NOTIFICATION</h1>
      <p>You are late for Appointment</p>
      <div class="urgent-badge">Action Required Immediately</div>
    </div>

    <div class="section">
      <div class="alert-box">
        <h2>🚨 Delay Query from Admin</h2>
        <p>
          <strong>Dr. {{ $data['doctor'] ?? 'Doctor' }}</strong>, you have not initiated a scheduled consultation. 
          This is an urgent request for an immediate status update.
        </p>
      </div>

      <div class="info-box">
        <div class="info-row">
          <span class="info-label">Consultation Reference:</span>
          <span class="info-value"><strong>{{ $data['consultation_reference'] ?? 'N/A' }}</strong></span>
        </div>
        <div class="info-row">
          <span class="info-label">Patient Name:</span>
          <span class="info-value">{{ ($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '') }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Patient Contact:</span>
          <span class="info-value">{{ $data['mobile'] ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Consultation Mode:</span>
          <span class="info-value">{{ ucfirst($data['consult_mode'] ?? 'N/A') }}</span>
        </div>
        @if(isset($data['problem']))
        <div class="info-row">
          <span class="info-label">Presenting Complaint:</span>
          <span class="info-value" style="max-width: 60%; word-wrap: break-word;">{{ \Illuminate\Support\Str::limit($data['problem'], 50) }}</span>
        </div>
        @endif
      </div>

      <p style="color: #111827; font-size: 15px; line-height: 1.7; margin: 25px 0;">
        <strong>Please take immediate action:</strong>
      </p>
      <ul style="color: #374151; font-size: 15px; line-height: 1.8; padding-left: 20px;">
        <li>Log into your DoctorOnTap dashboard immediately</li>
        <li>Initiate the consultation or update the consultation status</li>
        <li>Contact the patient if there are any delays or issues</li>
        <li>Respond to this query to confirm receipt</li>
      </ul>

      <a href="{{ config('app.url') }}/doctor/dashboard" class="cta-button">
        Go to Dashboard →
      </a>

      <p style="color: #6b7280; font-size: 14px; line-height: 1.6; margin-top: 25px; padding: 15px; background: #fef2f2; border-radius: 8px; border-left: 3px solid #dc2626;">
        <strong>Note:</strong> Failure to respond or initiate the consultation may result in reassignment of the patient to another doctor.
      </p>
    </div>

    <div class="footer">
      <p><strong>DoctorOnTap</strong></p>
      <p>This is an automated urgent notification. Please respond promptly.</p>
      <p>
        If you have any questions, contact us at 
        <a href="mailto:{{ config('mail.admin_email') }}">{{ config('mail.admin_email') }}</a>
      </p>
      <p style="margin-top: 15px; font-size: 12px; color: #9ca3af;">
        © {{ date('Y') }} DoctorOnTap. All rights reserved.
      </p>
    </div>
  </div>
</body>
</html>

