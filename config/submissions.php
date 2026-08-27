<?php

return [
    'rate_limits' => [
        'session_per_minute' => (int) env('SUBMISSION_SESSION_PER_MINUTE', 5),
        'session_per_hour' => (int) env('SUBMISSION_SESSION_PER_HOUR', 20),
        'ip_per_minute' => (int) env('SUBMISSION_IP_PER_MINUTE', 30),
        'ip_per_hour' => (int) env('SUBMISSION_IP_PER_HOUR', 120),
    ],
    'form_guard' => [
        'minimum_seconds' => (int) env('SUBMISSION_FORM_MINIMUM_SECONDS', 2),
        'ttl_minutes' => (int) env('SUBMISSION_FORM_TTL_MINUTES', 120),
        'lock_seconds' => (int) env('SUBMISSION_FORM_LOCK_SECONDS', 30),
    ],
    'uploads' => [
        'allowed_extensions' => [
            'jpg', 'jpeg', 'png', 'webp', 'pdf',
            'doc', 'docx', 'ppt', 'pptx', 'zip',
        ],
        'max_file_megabytes' => (int) env('SUBMISSION_MAX_FILE_MEGABYTES', 10),
        'max_files' => (int) env('SUBMISSION_MAX_FILES', 5),
        'max_total_kilobytes' => (int) env('SUBMISSION_MAX_TOTAL_KILOBYTES', 20480),
    ],
];
