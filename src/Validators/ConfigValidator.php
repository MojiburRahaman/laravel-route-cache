<?php

declare(strict_types=1);

namespace Mojiburrahaman\LaravelRouteCache\Validators;

use Mojiburrahaman\LaravelRouteCache\Exceptions\CacheException;

/**
 * Validates LaraCache configuration
 *
 * @package Mojiburrahaman\LaravelRouteCache\Validators
 */
class ConfigValidator
{
    /**
     * Validate all LaraCache configuration
     *
     * @throws CacheException If configuration is invalid
     * @return void
     */
    public static function validate(): void
    {
        self::validateRedisConfig();
        self::validateTTL();
        self::validateStatusCodes();
    }

    /**
     * Validate Redis configuration
     *
     * @throws CacheException If Redis config is invalid
     * @return void
     */
    protected static function validateRedisConfig(): void
    {
        $redis = config('laravel-route-cache.redis');

        if (! is_array($redis)) {
            throw CacheException::invalidConfig('Redis configuration must be an array');
        }

        if (empty($redis['host'])) {
            throw CacheException::invalidConfig('Redis host is required');
        }

        $port = $redis['port'] ?? null;
        if ($port && (! is_numeric($port) || $port < 1 || $port > 65535)) {
            throw CacheException::invalidConfig('Redis port must be between 1 and 65535');
        }

        $database = $redis['database'] ?? null;
        if ($database && (! is_numeric($database) || $database < 0)) {
            throw CacheException::invalidConfig('Redis database must be a non-negative integer');
        }
    }

    /**
     * Validate TTL configuration
     *
     * @throws CacheException If TTL is invalid
     * @return void
     */
    protected static function validateTTL(): void
    {
        $ttl = config('laravel-route-cache.default_ttl');

        if ($ttl !== null && (! is_numeric($ttl) || $ttl < 0)) {
            throw CacheException::invalidConfig('Default TTL must be a non-negative integer or null');
        }
    }

    /**
     * Validate cacheable status codes
     *
     * @throws CacheException If status codes are invalid
     * @return void
     */
    protected static function validateStatusCodes(): void
    {
        $codes = config('laravel-route-cache.cacheable_status_codes', []);

        if (! is_array($codes)) {
            throw CacheException::invalidConfig('Cacheable status codes must be an array');
        }

        foreach ($codes as $code) {
            if (! is_numeric($code) || $code < 100 || $code > 599) {
                throw CacheException::invalidConfig("Invalid HTTP status code: {$code}");
            }
        }
    }

    /**
     * Check if configuration is valid (non-throwing version)
     *
     * @return bool True if configuration is valid
     */
    public static function isValid(): bool
    {
        try {
            self::validate();

            return true;
        } catch (CacheException $e) {
            return false;
        }
    }
}
