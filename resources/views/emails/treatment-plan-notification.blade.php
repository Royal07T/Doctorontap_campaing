<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Treatment Plan – DoctorOnTap</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9fafb;
            color: #111827;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            max-width: 120px;
        }
        .badge {
            background: #10b981;
            color: white;
            padding: 8px 14px;
            border-radius: 20px;
            display: inline-block;
            font-size: 13px;
            margin: 15px 0;
        }
        .btn {
            display: inline-block;
            background: #7e22ce;
            color: #fff;
            padding: 14px 30px;
            border-radius: 6px;
            text-decoration: none;
            margin: 20px 0;
            font-weight: bold;
        }
        .section {
            margin: 20px 0;
        }
        .footer {
            font-size: 12px;
            color: #6b7280;
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <img src="{{ asset('img/whitelogo.png') }}" alt="DoctorOnTap">
        <h2>Your Treatment Plan is Ready</h2>
        <div class="badge">Payment Confirmed</div>
    </div>

    <p>Hello <strong>{{ $consultation->first_name }}</strong>,</p>

    <p>
        Your consultation with <strong>Dr. {{ $consultation->doctor->name }}</strong> has been completed.
        Your personalized treatment plan is now available.
    </p>

    <div class="section">
        <strong>Consultation Reference:</strong><br>
        {{ $consultation->reference }}
    </div>

    <div class="section">
        📎 A detailed PDF copy of your treatment plan is attached to this email.  
        Please download and store it securely.
    </div>

    <center>
        <a href="{{ route('treatment-plan.view', $consultation->reference) }}" class="btn">
            View Treatment Plan
        </a>
    </center>

    <div class="section">
        <strong>Important:</strong><br>
        Follow your doctor’s instructions exactly.  
        If your symptoms worsen or you experience any emergency symptoms, seek immediate medical care.
    </div>

    <div class="section">
        Need help?<br>
        📧 inquiries@doctorontap.com.ng<br>
        📞 0817 777 7122
    </div>

    <div class="footer">
        This email contains confidential medical information intended only for the recipient.<br>
        © {{ date('Y') }} DoctorOnTap
    </div>
</div>

</body>
</html>
