<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security-related configuration options for the
    | DoctorOnTap application.
    |
    */

    'rate_limiting' => [
        'login' => [
            'max_attempts' => 5,
            'decay_minutes' => 15,
        ],
        'api' => [
            'max_attempts' => 60,
            'decay_minutes' => 1,
        ],
        'consultation' => [
            'max_attempts' => 10,
            'decay_minutes' => 1,
        ],
    ],

    'session' => [
        'lifetime' => 120, // minutes
        'timeout_warning' => 10, // minutes before timeout
        'concurrent_session_check' => true,
        'secure_cookies' => env('SESSION_SECURE_COOKIES', true),
        'http_only' => true,
        'same_site' => 'lax',
    ],

    'monitoring' => [
        'enabled' => true,
        'log_level' => 'info', // debug, info, warning, error, critical
        'alert_thresholds' => [
            'critical_events_per_hour' => 1,
            'high_events_per_hour' => 5,
            'medium_events_per_hour' => 10,
            'rapid_requests_per_minute' => 100,
        ],
        'patterns' => [
            'sql_injection' => [
                '/(\bunion\b.*\bselect\b)/i',
                '/(\bselect\b.*\bfrom\b)/i',
                '/(\binsert\b.*\binto\b)/i',
                '/(\bupdate\b.*\bset\b)/i',
                '/(\bdelete\b.*\bfrom\b)/i',
                '/(\bdrop\b.*\btable\b)/i',
                '/(\balter\b.*\btable\b)/i',
                '/(\bexec\b|\bexecute\b)/i',
                '/(\bwaitfor\b.*\bdelay\b)/i',
                '/(\bxp_cmdshell\b)/i',
                '/(\bsp_executesql\b)/i',
                '/(\bchar\b.*\b\()/i',
                '/(\bconcat\b.*\b\()/i',
                '/(\bgroup_concat\b)/i',
                '/(\binformation_schema\b)/i',
                '/(\bpg_sleep\b)/i',
                '/(\bsleep\b.*\b\()/i',
                '/(\bbenchmark\b.*\b\()/i'
            ],
            'xss' => [
                '/(<script[^>]*>.*?<\/script>)/i',
                '/(<iframe[^>]*>.*?<\/iframe>)/i',
                '/(<object[^>]*>.*?<\/object>)/i',
                '/(<embed[^>]*>.*?<\/embed>)/i',
                '/(<applet[^>]*>.*?<\/applet>)/i',
                '/(<form[^>]*>.*?<\/form>)/i',
                '/(<input[^>]*>)/i',
                '/(<textarea[^>]*>.*?<\/textarea>)/i',
                '/(<select[^>]*>.*?<\/select>)/i',
                '/(<link[^>]*>)/i',
                '/(<meta[^>]*>)/i',
                '/(<style[^>]*>.*?<\/style>)/i',
                '/(javascript:)/i',
                '/(vbscript:)/i',
                '/(onload\s*=)/i',
                '/(onerror\s*=)/i',
                '/(onclick\s*=)/i',
                '/(onmouseover\s*=)/i',
                '/(onfocus\s*=)/i',
                '/(onblur\s*=)/i',
                '/(onchange\s*=)/i',
                '/(onsubmit\s*=)/i',
                '/(onreset\s*=)/i',
                '/(onselect\s*=)/i',
                '/(onkeydown\s*=)/i',
                '/(onkeyup\s*=)/i',
                '/(onkeypress\s*=)/i',
                '/(onmousedown\s*=)/i',
                '/(onmouseup\s*=)/i',
                '/(onmousemove\s*=)/i',
                '/(onmouseout\s*=)/i',
                '/(onmouseenter\s*=)/i',
                '/(onmouseleave\s*=)/i',
                '/(oncontextmenu\s*=)/i',
                '/(ondblclick\s*=)/i',
                '/(onabort\s*=)/i',
                '/(oncanplay\s*=)/i',
                '/(oncanplaythrough\s*=)/i',
                '/(ondurationchange\s*=)/i',
                '/(onemptied\s*=)/i',
                '/(onended\s*=)/i',
                '/(onerror\s*=)/i',
                '/(onloadeddata\s*=)/i',
                '/(onloadedmetadata\s*=)/i',
                '/(onloadstart\s*=)/i',
                '/(onpause\s*=)/i',
                '/(onplay\s*=)/i',
                '/(onplaying\s*=)/i',
                '/(onprogress\s*=)/i',
                '/(onratechange\s*=)/i',
                '/(onseeked\s*=)/i',
                '/(onseeking\s*=)/i',
                '/(onstalled\s*=)/i',
                '/(onsuspend\s*=)/i',
                '/(ontimeupdate\s*=)/i',
                '/(onvolumechange\s*=)/i',
                '/(onwaiting\s*=)/i',
                '/(onbeforeunload\s*=)/i',
                '/(onhashchange\s*=)/i',
                '/(onmessage\s*=)/i',
                '/(onoffline\s*=)/i',
                '/(ononline\s*=)/i',
                '/(onpagehide\s*=)/i',
                '/(onpageshow\s*=)/i',
                '/(onpopstate\s*=)/i',
                '/(onresize\s*=)/i',
                '/(onstorage\s*=)/i',
                '/(onunload\s*=)/i'
            ],
            'sensitive_files' => [
                '/\.env/',
                '/\.git/',
                '/\.htaccess/',
                '/\.htpasswd/',
                '/config/',
                '/database/',
                '/storage\/logs/',
                '/vendor/',
                '/node_modules/',
                '/\.php$/',
                '/\.sql$/',
                '/\.log$/',
                '/backup/',
                '/adminer/',
                '/phpmyadmin/',
                '/wp-admin/',
                '/wp-login/'
            ],
            'suspicious_user_agents' => [
                'sqlmap', 'nikto', 'nmap', 'masscan', 'zap', 'burp',
                'wget', 'curl', 'python-requests', 'bot', 'crawler',
                'scanner', 'exploit', 'hack', 'attack'
            ]
        ]
    ],

    'ip_blocking' => [
        'enabled' => true,
        'cache_key' => 'blocked_ips',
        'cache_duration' => 24 * 365, // 1 year in hours
        'auto_block_threshold' => 10, // Auto-block after 10 critical events
        'auto_block_duration' => 24, // Auto-block for 24 hours
    ],

    'alerts' => [
        'enabled' => true,
        'channels' => [
            'log' => true,
            'email' => false, // Set to true and configure email settings
            'slack' => false, // Set to true and configure Slack webhook
            'webhook' => false, // Set to true and configure webhook URL
        ],
        'email' => [
            'to' => env('SECURITY_ALERT_EMAIL', 'admin@doctorontap.com'),
            'from' => env('MAIL_FROM_ADDRESS', 'noreply@doctorontap.com'),
        ],
        'slack' => [
            'webhook_url' => env('SLACK_SECURITY_WEBHOOK'),
            'channel' => '#security-alerts',
        ],
        'webhook' => [
            'url' => env('SECURITY_WEBHOOK_URL'),
            'timeout' => 5,
        ]
    ],

    'headers' => [
        'security' => [
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        ],
        'cors' => [
            'Access-Control-Allow-Origin' => env('CORS_ALLOWED_ORIGINS', '*'),
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
        ]
    ],

    'file_upload' => [
        'max_size' => 10240, // 10MB in KB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'scan_uploads' => true,
        'quarantine_suspicious' => true,
    ],

    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => true,
        'max_age_days' => 90,
        'history_count' => 5,
    ],

    'two_factor' => [
        'enabled' => false,
        'issuer' => 'DoctorOnTap',
        'window' => 1, // Allow 1 window of tolerance
    ],

    'encryption' => [
        'key' => env('APP_KEY'),
        'cipher' => 'AES-256-CBC',
    ],

    'backup' => [
        'enabled' => true,
        'frequency' => 'daily',
        'retention_days' => 30,
        'encrypt' => true,
        'compress' => true,
    ]
];
