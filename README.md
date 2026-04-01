# Laravel Aura

[![Latest Version on Packagist](https://img.shields.io/packagist/v/aleksiaichuk-innowise/laravel-aura.svg?style=flat-square)](https://packagist.org/packages/aleksiaichuk-innowise/laravel-aura)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/aleksiaichuk-innowise/laravel-aura/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/aleksiaichuk-innowise/laravel-aura/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/aleksiaichuk-innowise/laravel-aura.svg?style=flat-square)](https://packagist.org/packages/aleksiaichuk-innowise/laravel-aura)

High-performance monitoring tool for Laravel applications. Collects database queries, HTTP requests, memory usage, cache operations, and jobs out-of-the-box. Includes a lightweight dashboard for real-time insights.

## Features

- **Automated Collection**: Seamlessly monitors application events without manual instrumentation.
- **Smart Filtering**: Only stores critical or slow operations (DB queries > 10ms, HTTP requests > 1000ms by default).
- **Multiple Storage Drivers**: Support for Database, Redis, and Null storage.
- **Insights Engine**: Analyzes collected data to provide actionable performance recommendations.
- **Clean Architecture**: Built with extensibility in mind using DTOs, Contracts, and a modular Collector system.
- **Modern PHP**: Leverages PHP 8.2+ features like Readonly Classes, Enums, and Constructor Property Promotion.

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
    'slow_query_threshold' => env('AURA_SLOW_QUERY_MS', 10),
    'slow_http_threshold' => env('AURA_SLOW_HTTP_MS', 1000),
    // ...
];
```

## Collectors

Aura includes the following collectors by default:

- **Database**: Records slow SQL queries.
- **HTTP Requests**: Monitors incoming request duration.
- **External HTTP**: Tracks outgoing HTTP requests via Laravel's `Http` client.
- **Memory**: Monitors peak memory usage.
- **Jobs**: Tracks queue job execution.
- **Cache**: Records cache hits/misses and operations.

## Dashboard

Access the Aura dashboard at `/aura` (default path) to view collected metrics and insights.

## Customization

### Adding a Custom Collector

Implement the `CollectorInterface` and register it in `config/aura.php`:

```php
namespace App\Collectors;

use Aura\Contracts\CollectorInterface;
use Aura\Core\AuraManager;
use Aura\DTO\MetricData;
use Aura\DTO\MetricType;

class CustomCollector implements CollectorInterface
{
    public function __construct(protected AuraManager $manager) {}

    public function register(): void
    {
        // Listen for events and record metrics
    }
}
```

### Adding a Custom Insight

Implement the `InsightInterface` and register it in `AuraServiceProvider`:

```php
namespace App\Insights;

use Aura\Contracts\InsightInterface;
use Aura\DTO\MetricData;
use Illuminate\Support\Collection;

class CustomInsight implements InsightInterface
{
    public function check(Collection $metrics): Collection
    {
        // Analyze metrics and return processed collection
        return $metrics;
    }
}
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
