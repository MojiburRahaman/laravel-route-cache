<?php

declare(strict_types=1);

namespace Mojiburrahaman\LaravelRouteCache\Facades;

use Illuminate\Support\Facades\Facade;
use Mojiburrahaman\LaravelRouteCache\Contracts\CacheManagerInterface;

/**
 * RouteCache Facade for easy access to cache operations
 *
 * Simple API - just pass the path! GET method is assumed by default.
 * Keys are automatically hashed internally using MD5 with a prefix.
 *
 * Examples:
 * - RouteCache::get('/') - Home page
 * - RouteCache::get('/api/users') - API endpoint
 * - RouteCache::get('POST:/api/posts') - Specific HTTP method
 *
 * @method static array<string, mixed>|null get(string $key) Retrieve cached response. Pass path like "/" or "GET:/" for explicit method
 * @method static bool put(string $key, \Illuminate\Http\Response|\Illuminate\Http\JsonResponse $response, ?int $ttl = null) Store response in cache
 * @method static bool has(string $key) Check if cache key exists. Pass path like "/api/users"
 * @method static bool forget(string $key) Delete specific cache key. Pass path like "/posts"
 * @method static bool flush() Flush all cache entries
 * @method static int|null ttl(string $key) Get remaining TTL in seconds
 * @method static string getHashedKey(string $key) Get the hashed version of a plain text key (for debugging)
 * @method static mixed getRedisConnection() Get the Redis connection instance
 *
 * @see \Mojiburrahaman\LaravelRouteCache\Contracts\CacheManagerInterface
 * @see \Mojiburrahaman\LaravelRouteCache\Services\CacheManager
 *
 * @package Mojiburrahaman\LaravelRouteCache\Facades
 */
class RouteCache extends Facade
{
    /**
     * Get the registered name of the component
     *
     * Returns the service container binding key for the cache manager
     *
     * @return string The facade accessor
     */
    protected static function getFacadeAccessor(): string
    {
        return CacheManagerInterface::class;
    }
}
