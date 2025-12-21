<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Treatment Plan</title>
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        color: #111;
        line-height: 1.6;
        padding: 25px;
    }

    .header {
        border-bottom: 1px solid #ccc;
        padding-bottom: 10px;
        margin-bottom: 20px;
        text-align: center;
    }

    .logo {
        max-width: 150px;
        margin-bottom: 5px;
    }

    .title {
        font-size: 16px;
        font-weight: bold;
        margin: 15px 0;
        text-align: center;
    }

    .section {
        margin-bottom: 18px;
    }

    .section-title {
        font-weight: bold;
        margin-bottom: 6px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 3px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    td {
        padding: 6px;
        vertical-align: top;
    }

    td.label {
        width: 35%;
        font-weight: bold;
        color: #444;
    }

    .content {
        white-space: pre-line;
    }

    .med {
        margin-bottom: 8px;
    }

    .notice {
        font-size: 10px;
        color: #444;
        background: #f9f9f9;
        padding: 8px;
        border-left: 3px solid #999;
    }

    .footer {
        margin-top: 30px;
        font-size: 9px;
        color: #666;
        text-align: center;
        border-top: 1px solid #ddd;
        padding-top: 10px;
    }
</style>
</head>

<body>

<div class="header">
    <img src="{{ public_path('img/sitelogo.png') }}" class="logo">
    <div style="font-size:10px;">DoctorOnTap – Treatment Plan</div>
</div>

<div class="title">Treatment Plan</div>

<div class="section">
    <table>
        <tr>
            <td class="label">Patient Name</td>
            <td>{{ $consultation->first_name }} {{ $consultation->last_name }}</td>
        </tr>
        <tr>
            <td class="label">Age / Gender</td>
            <td>{{ $consultation->age }} / {{ ucfirst($consultation->gender) }}</td>
        </tr>
        <tr>
            <td class="label">Date</td>
            <td>{{ $consultation->created_at->format('F d, Y') }}</td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="section-title">Diagnosis</div>
    <div class="content">{{ $consultation->diagnosis ?? 'As discussed during consultation.' }}</div>
</div>

@if($consultation->treatment_plan)
<div class="section">
    <div class="section-title">Treatment Plan</div>
    <div class="content">{{ $consultation->treatment_plan }}</div>
</div>
@endif

@if($consultation->prescribed_medications)
<div class="section">
    <div class="section-title">Prescribed Medications</div>
    @foreach($consultation->prescribed_medications as $med)
        <div class="med">
            <strong>{{ $med['name'] ?? 'Medication' }}</strong><br>
            {{ $med['dosage'] ?? '' }} – {{ $med['frequency'] ?? '' }} – {{ $med['duration'] ?? '' }}
        </div>
    @endforeach
</div>
@endif

@if($consultation->follow_up_instructions)
<div class="section">
    <div class="section-title">Follow-Up Instructions</div>
    <div class="content">{{ $consultation->follow_up_instructions }}</div>
</div>
@endif

<div class="section">
    <div class="section-title">When to Seek Help</div>
    <div class="notice">
        Contact your doctor or visit the nearest hospital if symptoms worsen,
        severe pain occurs, or new concerning symptoms develop.
    </div>
</div>

<div class="footer">
    Reference: {{ $consultation->reference }} <br>
    Generated on {{ now()->format('F d, Y h:i A') }} <br>
    © {{ date('Y') }} DoctorOnTap
</div>

</body>
</html>
