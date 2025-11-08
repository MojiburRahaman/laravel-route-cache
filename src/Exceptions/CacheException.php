<?php

declare(strict_types=1);

namespace Mojiburrahaman\LaravelRouteCache\Exceptions;

use Exception;

/**
 * Exception for cache-related errors
 *
 * @package Mojiburrahaman\LaravelRouteCache\Exceptions
 */
class CacheException extends Exception
{
    /**
     * Create exception for Redis connection failure
     *
     * @param string $message Additional error message
     * @return static
     */
    public static function connectionFailed(string $message = ''): self
    {
        return new static('Failed to connect to Redis: ' . $message);
    }

    /**
     * Create exception for cache storage failure
     *
     * @param string $key The cache key that failed to store
     * @return static
     */
    public static function storageFailed(string $key): self
    {
        return new static("Failed to store cache for key: {$key}");
    }

    /**
     * Create exception for cache retrieval failure
     *
     * @param string $key The cache key that failed to retrieve
     * @return static
     */
    public static function retrievalFailed(string $key): self
    {
        return new static("Failed to retrieve cache for key: {$key}");
    }

    /**
     * Create exception for invalid configuration
     *
     * @param string $message The configuration error message
     * @return static
     */
    public static function invalidConfig(string $message): self
    {
        return new static("Invalid configuration: {$message}");
    }

    /**
     * Create exception for invalid TTL value
     *
     * @param mixed $ttl The invalid TTL value
     * @return static
     */
    public static function invalidTTL($ttl): self
    {
        return new static("Invalid TTL value: {$ttl}. Must be a positive integer or null.");
    }

    /**
     * Create exception for Redis operation failure
     *
     * @param string $operation The operation that failed
     * @param string $details Additional details
     * @return static
     */
    public static function operationFailed(string $operation, string $details = ''): self
    {
        $message = "Redis operation '{$operation}' failed";
        if ($details) {
            $message .= ": {$details}";
        }

        return new static($message);
    }
}
