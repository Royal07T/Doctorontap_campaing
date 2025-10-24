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
    background: linear-gradient(135deg, #6C3EF3, #4B28A7);
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
    font-size: 26px;
    font-weight: 700;
  }

  .header p {
    font-size: 15px;
    margin-top: 8px;
    opacity: 0.9;
  }

  .section {
    padding: 25px 30px;
    border-bottom: 1px solid #eee;
  }

  .section h2 {
    font-size: 18px;
    color: #6C3EF3;
    margin: 0 0 15px;
    border-bottom: 2px solid #E4D8FF;
    padding-bottom: 6px;
  }

  .info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px 15px;
  }

  .label {
    font-weight: 600;
    color: #555;
  }

  .value {
    font-weight: 500;
    color: #222;
  }

  .highlight-box {
    background: #f9f5ff;
    border-left: 4px solid #6C3EF3;
    padding: 15px 18px;
    border-radius: 6px;
    margin: 15px 0;
    font-size: 15px;
  }

  .severity-badge {
    display: inline-block;
    padding: 5px 14px;
    border-radius: 20px;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.5px;
  }

  .mild { background: #D4EDDA; color: #155724; }
  .moderate { background: #FFF3CD; color: #856404; }
  .severe { background: #F8D7DA; color: #721C24; }

  .cta-section {
    text-align: center;
    padding: 30px 20px 40px;
  }

  .cta-section p {
    margin-bottom: 10px;
    font-size: 16px;
    color: #444;
  }

  .cta-button {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 40px;
    background: #25D366;
    color: #fff;
    border-radius: 40px;
    font-weight: bold;
    text-decoration: none;
    font-size: 15px;
    box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4);
    transition: all 0.2s ease-in-out;
  }

  .cta-button:hover {
    transform: scale(1.05);
    background: #20BA5A;
    box-shadow: 0 6px 20px rgba(37, 211, 102, 0.5);
  }

  .whatsapp-icon {
    width: 24px;
    height: 24px;
    fill: #fff;
  }

  .footer {
    background: #f8f9fa;
    padding: 20px;
    text-align: center;
    font-size: 13px;
    color: #666;
  }

  .footer p {
    margin: 5px 0;
  }

  ul {
    padding-left: 18px;
    margin: 5px 0;
  }

  li {
    margin-bottom: 4px;
  }
</style>
</head>
<body>

<div class="container">
  <!-- HEADER -->
  <div class="header">
    <img src="{{ env('APP_URL') }}/img/whitelogo.png" alt="DoctorOnTap Logo" class="logo">
    <h1>🩺 New Patient Consultation</h1>
    <p>A new patient has just requested your consultation</p>
  </div>

  <!-- ALERT -->
  <div class="section">
    <div class="highlight-box">
      <strong>⏰ Action Required:</strong> Please review the patient’s details below and reach out via WhatsApp promptly.
    </div>
  </div>

  <!-- PATIENT INFO -->
  <div class="section">
    <h2>👤 Patient Information</h2>
    <div class="info-grid">
      <div><span class="label">First Name:</span><br><span class="value">{{ $data['first_name'] }}</span></div>
      <div><span class="label">Gender:</span><br><span class="value">{{ ucfirst($data['gender']) }}</span></div>
      <div><span class="label">Age:</span><br><span class="value">{{ $data['age'] }} years</span></div>
    </div>
  </div>

  <!-- MEDICAL INFO -->
  <div class="section">
    <h2>🏥 Medical Details</h2>
    <p><strong>Problem:</strong> {{ $data['problem'] }}</p>
    <p><strong>Severity:</strong>
      <span class="severity-badge {{ strtolower($data['severity']) }}">
        {{ ucfirst($data['severity']) }}
      </span>
    </p>
    @if(isset($data['has_documents']) && $data['has_documents'])
    <p><strong>📎 Medical Documents:</strong> 
      <span style="color: #16a34a; font-weight: 600;">{{ $data['documents_count'] }} file(s) uploaded ✓</span>
    </p>
    @endif
  </div>

  @if(isset($data['emergency_symptoms']) && count($data['emergency_symptoms']) > 0)
  <div class="section">
    <div class="highlight-box" style="background:#fff5f5;border-left:4px solid #dc3545;">
      <strong>⚠️ Emergency Symptoms Detected</strong>
      <ul>
        @foreach($data['emergency_symptoms'] as $symptom)
          <li>{{ ucfirst(str_replace('_', ' ', $symptom)) }}</li>
        @endforeach
      </ul>
      <p style="margin:5px 0 0 0;">Please prioritize this consultation immediately.</p>
    </div>
  </div>
  @endif

  <!-- CONSULT DETAILS -->
  <div class="section">
    <h2>💬 Consultation Preferences</h2>
    <div class="info-grid">
      <div><span class="label">Mode:</span><br><span class="value">{{ ucfirst($data['consult_mode']) }}</span></div>
      @if(isset($data['doctor_fee']))
      <div><span class="label">Fee:</span><br><span class="value">₦{{ number_format($data['doctor_fee'], 2) }}</span></div>
      @endif
    </div>
  </div>

  <!-- CTA -->
  <div class="cta-section">
    <p><strong>Next Step:</strong> Log into your DoctorOnTap dashboard to view full patient details and start your consultation.</p>
    <a href="{{ env('APP_URL') }}/doctor/dashboard" class="cta-button" style="background: #6C3EF3; box-shadow: 0 4px 15px rgba(108, 62, 243, 0.4);">
      <svg class="whatsapp-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="fill: #fff;">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
      </svg>
      View in Dashboard
    </a>
  </div>

  <!-- FOOTER -->
  <div class="footer">
    <p><strong>DoctorOnTap</strong> — caring for you, just like family</p>
    <p>📧 {{ env('ADMIN_EMAIL', 'inquiries@doctorontap.com.ng') }}</p>
    <p>📱 08177777122 | +16178333519</p>
    <p style="color:#aaa;">This is an automated message. Please do not reply.</p>
  </div>
</div>

</body>
</html>
