<?php

return [
    'enabled' => env('APP_LOG_QUERY', false),

    // Disk configuration (default to 'local')
    'disk' => env('LALOG_DISK', 'local'),

    'storage' => [
        'directory_path' => env('QUERY_LOG_DIRECTORY', 'query-logs'),
        'max_file_size_bytes' => env('QUERY_LOG_MAX_SIZE', 2 * 1024 * 1024), // 2MB
    ],

    // Date/time formatting
    'formatting' => [
        'log_file_date_format' => env('QUERY_LOG_FILE_DATE_FORMAT', 'Y-m-d'), // For log file names
        'query_timestamp_format' => env('QUERY_LOG_TIMESTAMP_FORMAT', 'Y-m-d H:i:s'), // For query timestamps
    ],
];
