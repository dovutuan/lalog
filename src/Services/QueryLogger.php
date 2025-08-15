<?php

namespace Dovutuan\Lalog\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

class QueryLogger
{
    protected string $currentLogFilename;
    protected string $logDirectoryPath;
    protected string $filesystemDisk;
    protected int $maxLogFileSize;
    protected string $logFileDateFormat;
    protected string $queryTimestampFormat;

    public function __construct()
    {
        $this->initializeConfiguration();
    }

    protected function initializeConfiguration()
    {
        $this->filesystemDisk = Config::get('lalog.filesystem_disk', 'local');
        $this->maxLogFileSize = Config::get('lalog.storage.max_file_size_bytes', 2 * 1024 * 1024);
        $this->logDirectoryPath = rtrim(Config::get('lalog.storage.directory_path', 'query-logs'), '/');
        $this->logFileDateFormat = Config::get('lalog.formatting.log_file_date_format', 'Y-m-d');
        $this->queryTimestampFormat = Config::get('lalog.formatting.query_timestamp_format', 'Y-m-d H:i:s');
    }

    public function register()
    {
        $this->prepareLogFile();
        $this->registerQueryListener();
    }

    protected function prepareLogFile()
    {
        $currentDate = Carbon::now()->format($this->logFileDateFormat);
        $baseFilename = $this->logDirectoryPath . '/sql-' . $currentDate;
        $filename = "{$baseFilename}.sql";
        $fileIndex = 0;

        // Find available filename if current is full
        while (
            Storage::disk($this->filesystemDisk)->exists($filename)
            && Storage::disk($this->filesystemDisk)->size($filename) >= $this->maxLogFileSize
        ) {
            $fileIndex++;
            $filename = "{$baseFilename}-{$fileIndex}.sql";
        }

        $this->currentLogFilename = $filename;
    }

    protected function registerQueryListener()
    {
        DB::listen(function ($query) {
            $this->recordQuery($query);
        });
    }

    public function recordQuery($query)
    {
        Storage::disk($this->filesystemDisk)->append($this->currentLogFilename, "---------- QUERY LOG START ----------");

        $processedBindings = array_map(function ($binding) {
            if (is_object($binding) && method_exists($binding, 'format')) {
                return "'" . $binding->format($this->queryTimestampFormat) . "'";
            }
            return "'" . (string)$binding . "'";
        }, $query->bindings);

        $formattedSql = str_replace(['%', '?'], ['%%', '%s'], $query->sql);
        $formattedSql = vsprintf($formattedSql, $processedBindings);

        $logEntry = "Execution Time: " . Carbon::now()->format($this->queryTimestampFormat) . "\n";
        $logEntry .= "Duration: {$query->time} ms\n";
        $logEntry .= "Query: {$formattedSql};\n";
        $logEntry .= "---------- QUERY LOG END ----------\n";

        Storage::disk($this->filesystemDisk)->append($this->currentLogFilename, $logEntry);
    }

    // Các phương thức setter để override config
    public function setFilesystemDisk(string $disk): self
    {
        $this->filesystemDisk = $disk;
        return $this;
    }

    public function setLogDirectory(string $directory): self
    {
        $this->logDirectoryPath = rtrim($directory, '/');
        return $this;
    }

    public function setMaxLogFileSize(int $bytes): self
    {
        $this->maxLogFileSize = $bytes;
        return $this;
    }

    public function setLogFileDateFormat(string $format): self
    {
        $this->logFileDateFormat = $format;
        return $this;
    }

    public function setQueryTimestampFormat(string $format): self
    {
        $this->queryTimestampFormat = $format;
        return $this;
    }
}
