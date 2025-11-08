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

    /**
     * Attempt to acquire a cache generation lock for the given key.
     *
     * @param string $key Plain text cache key
     * @param int $ttl Lock TTL in seconds
     * @return string|null Lock token if acquired, null otherwise
     */
    public function acquireLock(string $key, int $ttl): ?string;

    /**
     * Release a previously acquired cache generation lock.
     *
     * @param string $key Plain text cache key
     * @param string $token Lock token returned when the lock was acquired
     * @return bool True if the lock was released, false otherwise
     */
    public function releaseLock(string $key, string $token): bool;

    /**
     * Determine if a lock is currently active for the given key.
     *
     * @param string $key Plain text cache key
     * @return bool True when the lock exists, false otherwise
     */
    public function isLocked(string $key): bool;
}
