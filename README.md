# Lalog - Laravel Query Logger

[![Latest Version](https://img.shields.io/packagist/v/dovutuan/lalog)](https://packagist.org/packages/dovutuan/lalog)
[![License](https://img.shields.io/packagist/l/dovutuan/lalog)](https://packagist.org/packages/dovutuan/lalog)
[![Latest Stable Version](http://poser.pugx.org/dovutuan/lalog/v)](https://packagist.org/packages/dovutuan/lalog)
[![Total Downloads](http://poser.pugx.org/dovutuan/lalog/downloads)](https://packagist.org/packages/dovutuan/lalog)
[![Latest Unstable Version](http://poser.pugx.org/dovutuan/lalog/v/unstable)](https://packagist.org/packages/dovutuan/lalog)
[![License](http://poser.pugx.org/dovutuan/lalog/license)](https://packagist.org/packages/dovutuan/lalog)

A simple and efficient Laravel package for logging database queries with automatic file rotation and customizable storage options.


## Features

- 📜 Logs all executed queries with bindings and execution time.
- 📅 Daily log files with automatic rotation based on file size.
- ⚙️ Configurable log storage disk, directory, and formatting.
- 🔄 Supports Laravel versions **7.x → 12.x**.
- 🚀 Easy integration via Service Provider.

## 📦 Installation

Install via Composer:

```bash
composer require --dev dovutuan/lalog
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Dovutuan\Lalog\ServiceProvider" --tag="lalog"
```

## ⚙️ Configuration

Edit `config/lalog.php` to customize your logging preferences:

```php
<?php

return [
    'enabled' => env('LALOG_QUERY', false),
    'disk' => env('LALOG_DISK', 'local'),
    'storage' => [
        'directory_path' => env('LALOG_DIRECTORY', 'query-logs'),
        'max_file_size_bytes' => env('LALOG_MAX_SIZE', 2097152), // 2MB
    ],
    'formatting' => [
        'log_file_date_format' => 'Y-m-d',         // File name format
        'query_timestamp_format' => 'Y-m-d H:i:s', // Log entry format
    ],
];
```

## 🚀 Basic Usage

Enable query logging by setting the environment variable in your `.env` file:

```dotenv
LALOG_QUERY=true
```

That's it! Your Laravel application will now automatically log all database queries.

## 🔍 Sample Output

Query logs will be formatted like this:

```text
---------- QUERY LOG START ----------
Execution Time: 2023-05-15 14:30:45  
Duration: 5.2 ms
Query: SELECT * FROM users WHERE id = '1';
---------- QUERY LOG END ----------
```

## 📂 Log Rotation

When a log file reaches the maximum size (default 2MB), a new file is automatically created with an incrementing index:

```text
query-logs/
  ├── sql-2023-05-15.sql
  ├── sql-2023-05-15-1.sql
  └── sql-2023-05-15-2.sql
```

## 🛠 Advanced Usage

### Using the Facade

You can manually record queries using the LaLog facade:

```php
use Dovutuan\Lalog\Facades\LaLog;

LaLog::recordQuery($query);
```

### Custom Storage Disk

Configure a custom disk for query logs in `config/filesystems.php`:

```php
'disks' => [
    'query-logs' => [
        'driver' => 'local',
        'root' => storage_path('logs/queries'),
    ],
],
```

Then update your `.env` file:

```dotenv
LALOG_DISK=query-logs
```

### Environment Variables

Available environment variables for configuration:

| Variable | Default | Description |
|----------|---------|-------------|
| `LALOG_QUERY` | `false` | Enable/disable query logging |
| `LALOG_DISK` | `local` | Storage disk to use |
| `LALOG_DIRECTORY` | `query-logs` | Directory name for log files |
| `LALOG_MAX_SIZE` | `2097152` | Maximum file size in bytes (2MB) |

## 🧪 Testing

Run the test suite:

```bash
composer test
```

## 🤝 Contributing

Pull requests are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to contribute to this project.

## 📜 License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

## 🐛 Issues

If you discover any security vulnerabilities or bugs, please report them via the [GitHub Issues](https://github.com/dovutuan/lalog/issues) page.

## 📈 Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information on what has changed recently.
