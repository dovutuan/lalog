<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Query Logging Toggle
    |--------------------------------------------------------------------------
    |
    | This flag determines whether query logging is enabled.
    | Set APP_LOG_QUERY=true in your .env to turn on logging.
    |
    */
    'enabled' => env('LALOG_QUERY', false),

    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    |
    | Filesystem disk to store query log files.
    | Uses Laravel's filesystem configuration (config/filesystems.php).
    | Default: local
    |
    */
    'disk' => env('LALOG_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    |
    | - directory_path: Directory where query log files are stored.
    | - max_file_size_bytes: Maximum size of a single log file in bytes.
    |   When exceeded, you may rotate logs or overwrite.
    |
    */
    'storage' => [
        'directory_path' => env('LALOG_DIRECTORY', 'query-logs'),
        'max_file_size_bytes' => env('LALOG_MAX_SIZE', 2 * 1024 * 1024), // 2MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Formatting
    |--------------------------------------------------------------------------
    |
    | Configure the date/time formats for:
    | - log_file_date_format: The date part used in the log filename.
    | - query_timestamp_format: The timestamp stored with each query line.
    |
    | Format patterns follow PHP's date() function.
    |
    */
    'formatting' => [
        'log_file_date_format' => env('LALOG_FILE_DATE_FORMAT', 'Y-m-d'),
        'query_timestamp_format' => env('LALOG_TIMESTAMP_FORMAT', 'Y-m-d H:i:s'),
    ],
];
