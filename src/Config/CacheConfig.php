<?php

declare(strict_types=1);

namespace Mojiburrahaman\LaravelRouteCache\Config;

/**
 * Configuration constants for LaraCache
 *
 * @package Mojiburrahaman\LaravelRouteCache\Config
 */
final class CacheConfig
{
    /**
     * Default cache TTL in seconds
     */
    public const DEFAULT_TTL = 3600;

    /**
     * Default Redis connection name
     */
    public const DEFAULT_CONNECTION = 'cache';

    /**
     * Default cache key prefix
     */
    public const DEFAULT_KEY_PREFIX = 'route_cache';

    /**
     * Default Redis prefix (includes colon for Redis namespace)
     */
    public const DEFAULT_REDIS_PREFIX = 'route_cache:';

    /**
     * HTTP methods that can be cached
     */
    public const CACHEABLE_METHODS = ['GET', 'HEAD'];

    /**
     * Successful HTTP status codes (2xx)
     */
    public const SUCCESS_STATUS_CODES = [200, 201, 202, 203, 204, 205, 206];

    /**
     * Headers to exclude from caching (sensitive data)
     */
    public const EXCLUDED_HEADERS = [
        'set-cookie',
        'cookie',
        'authorization',
        'x-csrf-token',
    ];

    /**
     * Cache header names
     */
    public const HEADER_CACHE_STATUS = 'X-Cache-Status';
    public const HEADER_CACHE_KEY = 'X-Cache-Key';
    public const HEADER_CACHED_AT = 'X-Cached-At';
    public const HEADER_CACHE_TTL = 'X-Cache-TTL';

    /**
     * Lock defaults (in seconds / milliseconds)
     */
    public const DEFAULT_LOCK_TTL = 10;
    public const DEFAULT_LOCK_WAIT_MS = 3000;
    public const DEFAULT_LOCK_SLEEP_MS = 50;

    /**
     * Prevent instantiation
     */
    private function __construct()
    {
    }
}
