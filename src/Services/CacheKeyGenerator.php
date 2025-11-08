<?php

declare(strict_types=1);

namespace Mojiburrahaman\LaravelRouteCache\Services;

use Illuminate\Http\Request;
use Mojiburrahaman\LaravelRouteCache\Config\CacheConfig;
use Mojiburrahaman\LaravelRouteCache\Contracts\CacheKeyGeneratorInterface;

/**
 * Generates unique cache keys for HTTP requests
 *
 * @package Mojiburrahaman\LaravelRouteCache\Services
 */
class CacheKeyGenerator implements CacheKeyGeneratorInterface
{
    /**
     * Generate plain text cache key (for CacheManager which will hash it)
     *
     * @param Request $request
     * @return string Plain text key that CacheManager will hash
     */
    public function generate(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();
        $queryParams = $request->query();

        $parts = [
            $method,
            $path,
        ];

        if (! empty($queryParams)) {
            $ignoreParams = config('laravel-route-cache.ignore_query_params', []);
            $filtered = $this->fastFilterParams($queryParams, $ignoreParams);
            if (! empty($filtered)) {
                ksort($filtered);
                $parts[] = http_build_query($filtered);
            }
        }

        $user = $request->user();
        if ($user) {
            $parts[] = 'user_' . $user->id;
        }

        return implode(':', $parts);
    }

    /**
     * @param array<string, mixed> $params
     * @param array<int, string> $ignore
     * @return array<string, mixed>
     */
    protected function fastFilterParams(array $params, array $ignore): array
    {
        if (empty($ignore)) {
            return $params;
        }

        $result = [];
        foreach ($params as $key => $value) {
            if (! in_array($key, $ignore, true)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @param Request $request
     * @return string
     */
    protected function getNormalizedUrl(Request $request): string
    {
        $url = $request->path();
        $url = trim($url, '/');
        $url = (string) preg_replace('#/+#', '/', $url);

        return $url;
    }

    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    protected function getFilteredQueryParams(Request $request): array
    {
        $queryParams = $request->query();
        $ignoreParams = config('laravel-route-cache.ignore_query_params', []);

        return array_filter($queryParams, function ($key) use ($ignoreParams) {
            return ! in_array($key, $ignoreParams, true);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function getReadableKey(Request $request): string
    {
        $prefix = config('laravel-route-cache.cache_key_prefix', CacheConfig::DEFAULT_KEY_PREFIX);
        $url = $this->getNormalizedUrl($request);
        $method = strtoupper($request->method());

        return "{$prefix}:{$method}:{$url}";
    }
}
