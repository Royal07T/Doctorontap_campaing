<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Weekly Health Report – {{ $patient->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.5; padding: 30px; }
        .header { background: linear-gradient(135deg, #9333EA, #7E22CE); color: #fff; padding: 20px 25px; border-radius: 8px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin-bottom: 2px; }
        .header p  { font-size: 10px; opacity: .85; }
        .meta-row { display: flex; justify-content: space-between; margin-bottom: 16px; font-size: 10px; color: #6b7280; }
        .section   { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin-bottom: 14px; }
        .section h2 { font-size: 13px; color: #7c3aed; border-bottom: 2px solid #ede9fe; padding-bottom: 6px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th { background: #f5f3ff; color: #6d28d9; text-align: left; padding: 6px 8px; font-weight: 600; }
        td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 9px; font-weight: 600; }
        .badge-critical { background: #fef2f2; color: #dc2626; }
        .badge-warning  { background: #fffbeb; color: #d97706; }
        .badge-normal   { background: #f0fdf4; color: #16a34a; }
        .badge-plan     { background: #ede9fe; color: #7c3aed; }
        .stat-grid { display: flex; gap: 12px; flex-wrap: wrap; }
        .stat-card { flex: 1; min-width: 110px; background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 6px; padding: 10px; text-align: center; }
        .stat-card .num { font-size: 20px; font-weight: 700; color: #7c3aed; }
        .stat-card .lbl { font-size: 9px; color: #6b7280; text-transform: uppercase; margin-top: 2px; }
        .compliance-bar { height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden; margin-top: 6px; }
        .compliance-fill { height: 100%; border-radius: 4px; }
        .mood-grid { display: flex; gap: 8px; flex-wrap: wrap; }
        .mood-chip { padding: 4px 10px; border-radius: 12px; background: #f3f4f6; font-size: 10px; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <h1>Weekly Health Report</h1>
        <p>{{ $patient->name }} &middot; {{ $from->format('M d') }} – {{ $to->format('M d, Y') }}</p>
    </div>

    {{-- Meta --}}
    <div class="meta-row">
        <span>Generated: {{ $generatedAt->format('M d, Y h:i A') }}</span>
        @if($carePlan)
            <span class="badge badge-plan">{{ ucfirst($carePlan->plan_type) }} Plan</span>
        @endif
    </div>

    {{-- Summary Stats --}}
    <div class="section">
        <h2>Summary</h2>
        <div class="stat-grid">
            <div class="stat-card">
                <div class="num">{{ $vitals->count() }}</div>
                <div class="lbl">Vitals Recorded</div>
            </div>
            <div class="stat-card">
                <div class="num">{{ $criticalCount }}</div>
                <div class="lbl" style="color:#dc2626;">Critical Flags</div>
            </div>
            <div class="stat-card">
                <div class="num">{{ $warningCount }}</div>
                <div class="lbl" style="color:#d97706;">Warnings</div>
            </div>
            <div class="stat-card">
                <div class="num">{{ $compliance }}%</div>
                <div class="lbl">Med Compliance</div>
            </div>
            <div class="stat-card">
                <div class="num">{{ $observations->count() }}</div>
                <div class="lbl">Observations</div>
            </div>
        </div>
    </div>

    {{-- Vital Signs Table --}}
    <div class="section">
        <h2>Vital Signs</h2>
        @if($vitals->count())
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>BP</th>
                    <th>HR</th>
                    <th>SpO2</th>
                    <th>Temp</th>
                    <th>Sugar</th>
                    <th>Flag</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vitals as $v)
                <tr>
                    <td>{{ $v->created_at->format('M d h:i A') }}</td>
                    <td>{{ $v->blood_pressure ?? '—' }}</td>
                    <td>{{ $v->heart_rate ?? '—' }}</td>
                    <td>{{ $v->oxygen_saturation ? $v->oxygen_saturation.'%' : '—' }}</td>
                    <td>{{ $v->temperature ? $v->temperature.'°C' : '—' }}</td>
                    <td>{{ $v->blood_sugar ?? '—' }}</td>
                    <td>
                        @if($v->flag_status === 'critical')
                            <span class="badge badge-critical">Critical</span>
                        @elseif($v->flag_status === 'warning')
                            <span class="badge badge-warning">Warning</span>
                        @else
                            <span class="badge badge-normal">Normal</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <p style="color:#9ca3af;">No vital signs recorded this week.</p>
        @endif
    </div>

    {{-- Medication Compliance --}}
    <div class="section">
        <h2>Medication Compliance</h2>
        <p><strong>{{ $givenMeds }}</strong> of <strong>{{ $totalMeds }}</strong> doses administered ({{ $compliance }}%)</p>
        <div class="compliance-bar">
            <div class="compliance-fill" style="width:{{ $compliance }}%; background:{{ $compliance >= 80 ? '#16a34a' : ($compliance >= 50 ? '#d97706' : '#dc2626') }};"></div>
        </div>
        @if($medications->where('status', 'missed')->count())
            <p style="margin-top:8px; color:#dc2626; font-size:10px;">
                ⚠ {{ $medications->where('status', 'missed')->count() }} dose(s) missed this week.
            </p>
        @endif
    </div>

    {{-- Mood & Observations --}}
    <div class="section">
        <h2>Mood & Observations</h2>
        @if($moodCounts->count())
            <div class="mood-grid">
                @foreach($moodCounts as $emoji => $count)
                    <span class="mood-chip">{{ $emoji }} × {{ $count }}</span>
                @endforeach
            </div>
            <p style="margin-top:8px;">Average pain level: <strong>{{ $avgPain }}/10</strong></p>
        @else
            <p style="color:#9ca3af;">No mood observations recorded this week.</p>
        @endif
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>DoctorOnTap &middot; Confidential Patient Health Information &middot; Generated automatically</p>
    </div>

</body>
</html>
