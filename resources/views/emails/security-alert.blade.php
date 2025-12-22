<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Alert - DoctorOnTap</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            border-bottom: 3px solid;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .severity-critical { border-color: #dc2626; }
        .severity-high { border-color: #ea580c; }
        .severity-medium { border-color: #ca8a04; }
        .severity-low { border-color: #16a34a; }
        .severity-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .badge-critical { background-color: #fee2e2; color: #991b1b; }
        .badge-high { background-color: #fed7aa; color: #9a3412; }
        .badge-medium { background-color: #fef3c7; color: #854d0e; }
        .badge-low { background-color: #dcfce7; color: #166534; }
        h1 {
            margin: 0;
            font-size: 24px;
            color: #1f2937;
        }
        .event-type {
            font-size: 18px;
            font-weight: 600;
            color: #374151;
            margin: 20px 0;
        }
        .details {
            background-color: #f9fafb;
            border-left: 4px solid #6366f1;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .detail-row {
            margin: 12px 0;
            display: flex;
            flex-wrap: wrap;
        }
        .detail-label {
            font-weight: 600;
            color: #4b5563;
            min-width: 120px;
            margin-right: 10px;
        }
        .detail-value {
            color: #1f2937;
            word-break: break-word;
        }
        .timestamp {
            color: #6b7280;
            font-size: 14px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
        .action-button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background-color: #6366f1;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header severity-{{ $severity }}">
            <span class="severity-badge badge-{{ $severity }}">
                {{ strtoupper($severity) }} SEVERITY
            </span>
            <h1>Security Alert</h1>
        </div>

        <div class="event-type">
            Event Type: {{ ucwords(str_replace('_', ' ', $eventType)) }}
        </div>

        <div class="details">
            <div class="detail-row">
                <span class="detail-label">IP Address:</span>
                <span class="detail-value">{{ $data['ip'] ?? 'N/A' }}</span>
            </div>
            
            @if(isset($data['url']))
            <div class="detail-row">
                <span class="detail-label">URL:</span>
                <span class="detail-value">{{ $data['url'] }}</span>
            </div>
            @endif

            @if(isset($data['user_agent']))
            <div class="detail-row">
                <span class="detail-label">User Agent:</span>
                <span class="detail-value">{{ $data['user_agent'] }}</span>
            </div>
            @endif

            @if(isset($data['input_key']) && isset($data['input_value']))
            <div class="detail-row">
                <span class="detail-label">Suspicious Input:</span>
                <span class="detail-value">
                    <strong>{{ $data['input_key'] }}:</strong> {{ Str::limit($data['input_value'], 200) }}
                </span>
            </div>
            @endif

            @if(isset($data['pattern']))
            <div class="detail-row">
                <span class="detail-label">Detected Pattern:</span>
                <span class="detail-value"><code>{{ $data['pattern'] }}</code></span>
            </div>
            @endif

            @if(isset($data['request_count']))
            <div class="detail-row">
                <span class="detail-label">Request Count:</span>
                <span class="detail-value">{{ $data['request_count'] }} requests</span>
            </div>
            @endif

            @if(isset($data['method']))
            <div class="detail-row">
                <span class="detail-label">HTTP Method:</span>
                <span class="detail-value">{{ $data['method'] }}</span>
            </div>
            @endif
        </div>

        <div class="timestamp">
            <strong>Timestamp:</strong> {{ \Carbon\Carbon::parse($data['timestamp'] ?? now())->format('F j, Y g:i:s A T') }}
        </div>

        <div style="margin-top: 30px;">
            <a href="{{ config('app.url') }}/admin/security" class="action-button">
                View Security Dashboard
            </a>
        </div>

        <div class="footer">
            <p>This is an automated security alert from DoctorOnTap.</p>
            <p>If you believe this is a false positive, please contact the security team.</p>
            <p>&copy; {{ date('Y') }} DoctorOnTap. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

