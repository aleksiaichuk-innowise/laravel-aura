<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Aura Enabled
    |--------------------------------------------------------------------------
    */
    'enabled' => env('AURA_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Aura Storage Driver
    |--------------------------------------------------------------------------
    |
    | Supported: "database", "redis", "null"
    |
    */
    'driver' => env('AURA_DRIVER', 'database'),

    'drivers' => [
        'database' => [
            'connection' => env('DB_CONNECTION', 'mysql'),
            'table' => 'aura_metrics',
        ],
        'redis' => [
            'connection' => 'default',
            'key' => 'aura:metrics',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Collectors
    |--------------------------------------------------------------------------
    |
    | These classes will be registered to listen for application events.
    |
    */
    'collectors' => [
        \Aura\Collectors\Database\DatabaseQueryCollector::class,
        \Aura\Collectors\Requests\RequestDurationCollector::class,
        \Aura\Collectors\Resources\MemoryUsageCollector::class,
        \Aura\Collectors\Requests\HttpClientCollector::class,
        \Aura\Collectors\Jobs\JobCollector::class,
        \Aura\Collectors\Cache\CacheCollector::class,
    ],

    'slow_query_threshold' => env('AURA_SLOW_QUERY_MS', 10),
    'slow_http_threshold' => env('AURA_SLOW_HTTP_MS', 1000),

    'path' => env('AURA_PATH', 'aura'),


    'middleware' => [
        'web',
    ],
];
