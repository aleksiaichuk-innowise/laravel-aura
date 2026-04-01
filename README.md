# Laravel Aura

[![Latest Version on Packagist](https://img.shields.io/packagist/v/aleksiaichuk-innowise/laravel-aura.svg?style=flat-square)](https://packagist.org/packages/aleksiaichuk-innowise/laravel-aura)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/aleksiaichuk-innowise/laravel-aura/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/aleksiaichuk-innowise/laravel-aura/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/aleksiaichuk-innowise/laravel-aura.svg?style=flat-square)](https://packagist.org/packages/aleksiaichuk-innowise/laravel-aura)

High-performance monitoring tool for Laravel applications. Collects database queries, HTTP requests, memory usage, cache operations, and jobs out-of-the-box. Includes a lightweight dashboard for real-time insights and log correlation.

## Features

- **Automated Collection**: Seamlessly monitors application events without manual instrumentation.
- **Smart Filtering**: Only stores critical or slow operations (DB queries > 10ms, HTTP requests > 500ms, External API > 1000ms by default).
- **Log Correlation**: Automatically injects a unique `trace_id` into Laravel's log context for end-to-end debugging.
- **Data Masking**: Automatically masks sensitive data (passwords, tokens, keys) in metric tags before storage.
- **Insights Engine**: Analyzes collected data (e.g., detecting N+1 queries) to provide actionable performance recommendations.
- **Modern Dashboard**: High-performance, lightweight UI built with Tailwind CSS for real-time monitoring.
- **Clean Architecture**: Built with extensibility in mind using DTOs, Contracts, and a modular Collector system.

## Requirements

- PHP 8.2+
- Laravel 10.x or 11.x

## Installation

You can install the package via composer:

```bash
composer require aleksiaichuk-innowise/laravel-aura
```

Run the installation command to publish the configuration and run migrations:

```bash
php artisan aura:install
```

## Configuration

The package comes with a sensible default configuration. You can customize it in `config/aura.php`:

```php
return [
    'enabled' => env('AURA_ENABLED', true),
    'driver' => env('AURA_DRIVER', 'database'),
    
    // Thresholds (ms)
    'slow_query_threshold' => env('AURA_SLOW_QUERY_MS', 10),
    'slow_http_threshold' => env('AURA_SLOW_HTTP_MS', 1000),
    'slow_request_threshold' => env('AURA_SLOW_REQUEST_MS', 500),

    // Log Integration
    'log_integration' => [
        'enabled' => true,
        'context_key' => 'aura_trace_id',
    ],

    // Sensitive Data Masking
    'masking' => [
        'fields' => ['password', 'token', 'secret', 'key', 'authorization'],
    ],
];
```

## Collectors

Aura includes the following collectors by default:

- **Database**: Records slow SQL queries and detects patterns (N+1, duplicates).
- **App Requests**: Monitors incoming request duration and status codes.
- **External HTTP**: Tracks outgoing HTTP requests via Laravel's `Http` client.
- **Memory**: Monitors peak memory usage.
- **Jobs**: Tracks queue job execution (processed vs failed).
- **Cache**: Records cache efficiency (hit rate, writes, misses).

## Log Correlation

When `log_integration` is enabled, Aura automatically adds the current `trace_id` to Laravel's log context. Any log entry created during the request will include this ID:

```php
Log::info('Order processed'); 
// Result in logs: [2026-04-01 12:00:00] local.INFO: Order processed {"aura_trace_id":"9c842797-..."}
```

This allows you to find all logs related to a specific slow request seen on the dashboard.

## Dashboard

Access the Aura dashboard at `/aura` (default path) to view:
- **Slow App Responses**: Recent slow incoming requests.
- **Slow DB Queries**: Database performance bottlenecks.
- **Slow API Requests**: External service latency.
- **Performance Insights**: Automatic detection of N+1 and duplicate queries.
- **Resource Stats**: Real-time memory, cache, and queue activity.

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
