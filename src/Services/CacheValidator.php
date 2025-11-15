<?php

declare(strict_types=1);

namespace Mojiburrahaman\LaravelRouteCache\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mojiburrahaman\LaravelRouteCache\Config\CacheConfig;

/**
 * Validates if requests and responses are cacheable
 *
 * @package Mojiburrahaman\LaravelRouteCache\Services
 */
class CacheValidator
{
    /**
     * Check if caching is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return config('laravel-route-cache.enabled', true);
    }

    /**
     * Check if request is cacheable
     *
     * @param Request $request
     * @return bool
     */
    public function isRequestCacheable(Request $request): bool
    {
        // Only cache GET requests
        if ($request->getMethod() !== 'GET') {
            return false;
        }

        // Check if URL is excluded
        if ($this->isUrlExcluded($request->path())) {
            return false;
        }

        return true;
    }

    /**
     * Check if response is cacheable
     *
     * @param mixed $response
     * @return bool
     */
    public function isResponseCacheable($response): bool
    {
        if (! ($response instanceof Response) && ! ($response instanceof JsonResponse)) {
            return false;
        }

        $statusCode = $response->getStatusCode();
        $onlySuccess = config('laravel-route-cache.cache_only_success', true);

        if ($onlySuccess) {
            return $statusCode >= 200 && $statusCode < 300;
        }

        $cacheableCodes = config('laravel-route-cache.cacheable_status_codes', CacheConfig::SUCCESS_STATUS_CODES);

        return in_array($statusCode, $cacheableCodes, true);
    }

    /**
     * Check if URL path is excluded from caching
     *
     * @param string $path
     * @return bool
     */
    protected function isUrlExcluded(string $path): bool
    {
        $excludeUrls = config('laravel-route-cache.exclude_urls', []);

        if (empty($excludeUrls)) {
            return false;
        }

        foreach ($excludeUrls as $pattern) {
            if ($this->matchesPattern($path, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if path matches a pattern
     *
     * @param string $path
     * @param string $pattern
     * @return bool
     */
    protected function matchesPattern(string $path, string $pattern): bool
    {
        // Exact match
        if ($path === $pattern) {
            return true;
        }

        // Wildcard at end
        if ($this->endsWith($pattern, '*')) {
            $prefix = rtrim($pattern, '*');

            if ($prefix === '') {
                return true;
            }

            return strpos($path, $prefix) === 0;
        }

        // Pattern with wildcards
        $regex = '/^' . str_replace(['*', '/'], ['.*', '\/'], $pattern) . '$/';

        return preg_match($regex, $path) === 1;
    }

    /**
     * Determine if the given string ends with the provided suffix.
     *
     * @param string $value
     * @param string $suffix
     * @return bool
     */
    protected function endsWith(string $value, string $suffix): bool
    {
        if ($suffix === '') {
            return true;
        }

        $suffixLength = strlen($suffix);

        if ($suffixLength > strlen($value)) {
            return false;
        }

        return substr($value, -$suffixLength) === $suffix;
    }
}
