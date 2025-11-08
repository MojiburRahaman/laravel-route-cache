<?php

use Mojiburrahaman\LaravelRouteCache\Config\CacheConfig;

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache driver used by the package.
    | Currently only 'redis' is supported.
    |
    */
    'driver' => env('ROUTE_CACHE_DRIVER', 'redis'),

    /*
    |--------------------------------------------------------------------------
    | Redis Connection
    |--------------------------------------------------------------------------
    |
    | The Redis connection to use for caching. This should match a connection
    | defined in your config/database.php file.
    |
    */
    'redis_connection' => env('ROUTE_CACHE_REDIS_CONNECTION', CacheConfig::DEFAULT_CONNECTION),

    /*
    |--------------------------------------------------------------------------
    | Redis Configuration
    |--------------------------------------------------------------------------
    |
    | Uses Laravel's default REDIS_* environment variables
    | Only adds a custom prefix to separate route cache keys
    |
    */
    'redis' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_DB', 0),
        'prefix' => env('ROUTE_CACHE_PREFIX', CacheConfig::DEFAULT_REDIS_PREFIX),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default TTL (Time To Live)
    |--------------------------------------------------------------------------
    |
    | Default cache TTL in seconds. This will be used if no TTL is specified
    | in the middleware. Set to null for no expiration.
    |
    */
    'default_ttl' => env('ROUTE_CACHE_TTL', CacheConfig::DEFAULT_TTL), // 1 hour

    /*
    |--------------------------------------------------------------------------
    | Ignore Query Parameters
    |--------------------------------------------------------------------------
    |
    | Query parameters that should be ignored when generating cache keys
    |
    */
    'ignore_query_params' => [
        '_',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Only Success Responses
    |--------------------------------------------------------------------------
    |
    | If true, only responses with 2xx status codes will be cached
    |
    */
    'cache_only_success' => env('ROUTE_CACHE_ONLY_SUCCESS', true),

    /*
    |--------------------------------------------------------------------------
    | Cacheable Status Codes
    |--------------------------------------------------------------------------
    |
    | HTTP status codes that should be cached
    |
    */
    'cacheable_status_codes' => [
        200, 201, 202, 203, 204, 205, 206,
    ],

    /*
    |--------------------------------------------------------------------------
    | Exclude URLs
    |--------------------------------------------------------------------------
    |
    | URL patterns that should never be cached
    |
    */
    'exclude_urls' => [
        'api/admin/*',
        'api/auth/*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Headers
    |--------------------------------------------------------------------------
    |
    | Whether to add cache-related headers to the response
    |
    */
    'add_cache_headers' => env('ROUTE_CACHE_ADD_HEADERS', true),

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Master switch to enable/disable caching
    |
    */
    'enabled' => env('CACHE_ROUTES', true),

    /*
    |--------------------------------------------------------------------------
    | Compression Threshold
    |--------------------------------------------------------------------------
    |
    | Minimum response size (in bytes) to trigger compression.
    | Responses smaller than this won't be compressed.
    |
    */
    'compression_threshold' => 1024, // 1KB

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix for cache keys to avoid collisions
    |
    */
    'cache_key_prefix' => env('ROUTE_CACHE_KEY_PREFIX', 'route_cache'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stampede Lock
    |--------------------------------------------------------------------------
    |
    | Configure a lightweight lock to prevent cache stampedes when a key
    | expires under heavy load. Adjust TTL (seconds), wait (milliseconds),
    | and sleep (milliseconds) as needed for your workload.
    |
    */
    'lock' => [
        'enabled' => env('ROUTE_CACHE_LOCK_ENABLED', true),
        'ttl' => (int) env('ROUTE_CACHE_LOCK_TTL', CacheConfig::DEFAULT_LOCK_TTL),
        'wait_ms' => (int) env('ROUTE_CACHE_LOCK_WAIT_MS', CacheConfig::DEFAULT_LOCK_WAIT_MS),
        'sleep_ms' => (int) env('ROUTE_CACHE_LOCK_SLEEP_MS', CacheConfig::DEFAULT_LOCK_SLEEP_MS),
    ],
];
