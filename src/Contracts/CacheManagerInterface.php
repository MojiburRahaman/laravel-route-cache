<?php

declare(strict_types=1);

namespace Mojiburrahaman\LaravelRouteCache\Contracts;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Interface for cache management operations
 *
 * @package Mojiburrahaman\LaravelRouteCache\Contracts
 */
interface CacheManagerInterface
{
    /**
     * Retrieve cached response data using plain text key
     * The key will be automatically hashed internally
     *
     * @param string $key Plain text cache key
     * @return array<string, mixed>|null The cached data or null if not found
     */
    public function get(string $key): ?array;

    /**
     * Store response in cache using plain text key
     * The key will be automatically hashed internally
     *
     * @param string $key Plain text cache key
     * @param Response|JsonResponse $response The response to cache
     * @param int|null $ttl Time to live in seconds
     * @return bool True if stored successfully, false otherwise
     */
    public function put(string $key, $response, ?int $ttl = null): bool;

    /**
     * Check if cache key exists using plain text key
     * The key will be automatically hashed internally
     *
     * @param string $key Plain text cache key
     * @return bool True if key exists, false otherwise
     */
    public function has(string $key): bool;

    /**
     * Delete specific cache key using plain text key
     * The key will be automatically hashed internally
     *
     * @param string $key Plain text cache key
     * @return bool True if deleted successfully, false otherwise
     */
    public function forget(string $key): bool;

    /**
     * Flush all cache entries
     *
     * @return bool True if flushed successfully, false otherwise
     */
    public function flush(): bool;

    /**
     * Get remaining time-to-live for a cache key using plain text key
     * The key will be automatically hashed internally
     *
     * @param string $key Plain text cache key
     * @return int|null TTL in seconds, or null if key doesn't exist
     */
    public function ttl(string $key): ?int;

    /**
     * Get the hashed version of a plain text key (for debugging purposes)
     * This allows users to see what the actual Redis key will be
     *
     * @param string $key Plain text cache key
     * @return string The hashed cache key (MD5 of prefix + key)
     */
    public function getHashedKey(string $key): string;

    /**
     * Get the Redis connection instance
     *
     * @return mixed The Redis connection
     */
    public function getRedisConnection();
}
