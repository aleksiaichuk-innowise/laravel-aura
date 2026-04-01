<?php

declare(strict_types=1);

namespace Aura\DTO\Metrics;

enum MetricType: string
{
    case DATABASE_QUERY = 'database_query';
    case REQUEST_DURATION = 'request_duration';
    case MEMORY_USAGE = 'memory_usage';
    case EXTERNAL_HTTP_REQUEST = 'http_request';
    case JOB_EXECUTION = 'job_execution';
    case CACHE_OPERATION = 'cache_operation';
    case INSIGHT = 'insight';
}
