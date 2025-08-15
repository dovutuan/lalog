<?php

namespace Dovutuan\Lalog\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QueryLogger
{
    // Current log file name where queries will be stored
    protected string $currentLogFilename;

    // Directory path to store log files
    protected string $logDirectoryPath;

    // Filesystem disk name (e.g., local, s3)
    protected string $filesystemDisk;

    // Maximum log file size in bytes
    protected int $maxLogFileSize;

    // Date format for the log file name
    protected string $logFileDateFormat;

    // Date format for query timestamps
    protected string $queryTimestampFormat;

    public function __construct()
    {
        // Load configuration values when the class is instantiated
        $this->initializeConfiguration();
    }

    /**
     * Load configuration values from config/lalog.php
     */
    private function initializeConfiguration()
    {
        $this->filesystemDisk = Config::get('lalog.disk', 'local');
        $this->maxLogFileSize = Config::get('lalog.storage.max_file_size_bytes', 2 * 1024 * 1024);
        $this->logDirectoryPath = rtrim(Config::get('lalog.storage.directory_path', 'query-logs'), '/');
        $this->logFileDateFormat = Config::get('lalog.formatting.log_file_date_format', 'Y-m-d');
        $this->queryTimestampFormat = Config::get('lalog.formatting.query_timestamp_format', 'Y-m-d H:i:s');
    }

    /**
     * Get the storage disk instance based on configuration
     */
    private function storage()
    {
        return Storage::disk($this->filesystemDisk);
    }

    /**
     * Register the query logger:
     * - Prepare the log file
     * - Listen to database queries
     */
    public function register()
    {
        $this->prepareLogFile();
        $this->registerQueryListener();
    }

    /**
     * Prepare the log file for writing queries.
     * If the current log file exceeds the maximum size,
     * a new file with an incremental index will be created.
     */
    private function prepareLogFile()
    {
        $currentDate = Carbon::now()->format($this->logFileDateFormat);
        $baseFilename = "$this->logDirectoryPath/sql-$currentDate";
        $filename = "$baseFilename.sql";
        $fileIndex = 0;

        while ($this->storage()->exists($filename) && $this->storage()->size($filename) >= $this->maxLogFileSize) {
            $fileIndex++;
            $filename = "$baseFilename-$fileIndex.sql";
        }

        $this->currentLogFilename = $filename;
    }

    /**
     * Register the database query listener.
     * Every executed query will be passed to recordQuery().
     */
    private function registerQueryListener()
    {
        DB::listen(function ($query) {
            $this->recordQuery($query);
        });
    }

    /**
     * Record a query into the current log file.
     * Includes execution time, duration, and the full SQL with bindings.
     */
    public function recordQuery($query)
    {
        // Write a start marker
        $this->storage()->append($this->currentLogFilename, "---------- QUERY LOG START ----------");

        // Process query bindings, formatting dates and wrapping values in quotes
        $processedBindings = array_map(function ($binding) {
            if (is_object($binding) && method_exists($binding, 'format')) {
                return "'" . $binding->format($this->queryTimestampFormat) . "'";
            }
            return "'" . $binding . "'";
        }, $query->bindings);

        // Replace placeholders with binding values
        $formattedSql = str_replace(['%', '?'], ['%%', '%s'], $query->sql);
        $formattedSql = vsprintf($formattedSql, $processedBindings);

        // Build the log entry
        $logEntry = "Execution Time: " . Carbon::now()->format($this->queryTimestampFormat) . "\n";
        $logEntry .= "Duration: $query->time ms\n";
        $logEntry .= "Query: $formattedSql;\n";
        $logEntry .= "---------- QUERY LOG END ----------\n";

        // Append the log entry to the file
        $this->storage()->append($this->currentLogFilename, $logEntry);
    }
}
