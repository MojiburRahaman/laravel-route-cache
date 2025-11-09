<?php

declare(strict_types=1);

namespace Mojiburrahaman\LaravelRouteCache\Services;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Mojiburrahaman\LaravelRouteCache\Config\CacheConfig;
use Mojiburrahaman\LaravelRouteCache\Contracts\CacheKeyGeneratorInterface;
use Mojiburrahaman\LaravelRouteCache\Contracts\CacheManagerInterface;
use Mojiburrahaman\LaravelRouteCache\Enums\CacheStatus;

/**
 * Coordinates the cache workflow for HTTP requests handled by the middleware.
 *
 * @package Mojiburrahaman\LaravelRouteCache\Services
 */
class CacheMiddlewareHandler
{
    protected CacheKeyGeneratorInterface $keyGenerator;

    protected CacheManagerInterface $cacheManager;

    protected CacheValidator $validator;

    protected CacheResponseBuilder $responseBuilder;

    /**
     * @param CacheKeyGeneratorInterface $keyGenerator
     * @param CacheManagerInterface $cacheManager
     * @param CacheValidator $validator
     * @param CacheResponseBuilder $responseBuilder
     */
    public function __construct(
        CacheKeyGeneratorInterface $keyGenerator,
        CacheManagerInterface $cacheManager,
        CacheValidator $validator,
        CacheResponseBuilder $responseBuilder
    ) {
        $this->keyGenerator = $keyGenerator;
        $this->cacheManager = $cacheManager;
        $this->validator = $validator;
        $this->responseBuilder = $responseBuilder;
    }

    /**
     * Execute the caching workflow for the given request.
     *
     * @param Request $request
     * @param Closure $next
     * @param int|null $ttl
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?int $ttl = null)
    {
        try {
            if (! $this->validator->isEnabled() || ! $this->validator->isRequestCacheable($request)) {
                return $next($request);
            }

            $cacheKey = $this->keyGenerator->generate($request);
            $cachedData = $this->cacheManager->get($cacheKey);
            if ($cachedData !== null) {
                return $this->responseBuilder->build($cachedData, $cacheKey);
            }

            $resolvedTtl = $this->resolveTtl($ttl);
            $lockToken = null;
            $acquiredLock = false;

            if ($this->shouldUseLock()) {
                $lockToken = $this->attemptLock($cacheKey, $resolvedTtl, $acquiredLock);

                if (! $acquiredLock) {
                    $cachedData = $this->cacheManager->get($cacheKey);
                    if ($cachedData !== null) {
                        return $this->responseBuilder->build($cachedData, $cacheKey);
                    }
                }
            }

            $response = null;

            try {
                $response = $next($request);

                if ($this->validator->isResponseCacheable($response)) {
                    $this->cacheManager->put($cacheKey, $response, $resolvedTtl);
                }

                $this->addMissHeaders($response, $request);

                return $response;
            } finally {
                if ($acquiredLock && $lockToken !== null) {
                    $this->cacheManager->releaseLock($cacheKey, $lockToken);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Route cache middleware failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $response = $next($request);
            $this->addMissHeaders($response, $request);

            return $response;
        }
    }

    /**
     * Normalize TTL from middleware or config.
     *
     * @param int|string|null $ttl
     * @return int|null
     */
    protected function resolveTtl($ttl): ?int
    {
        if ($ttl === null) {
            return $this->normalizeTtlValue(config('laravel-route-cache.default_ttl', CacheConfig::DEFAULT_TTL));
        }

        return $this->normalizeTtlValue($ttl);
    }

    /**
     * Determine if lock handling should run.
     *
     * @return bool
     */
    protected function shouldUseLock(): bool
    {
        $lockConfig = config('laravel-route-cache.lock', []);

        return (bool) ($lockConfig['enabled'] ?? true);
    }

    /**
     * Attempt to acquire a cache generation lock.
     *
     * @param string $cacheKey
     * @param int|null $ttl
     * @param bool $lockAcquired
     * @return string|null
     */
    protected function attemptLock(string $cacheKey, ?int $ttl, bool &$lockAcquired): ?string
    {
        $lockConfig = config('laravel-route-cache.lock', []);
        $lockTtl = (int) ($lockConfig['ttl'] ?? CacheConfig::DEFAULT_LOCK_TTL);

        if ($ttl !== null && $ttl > 0) {
            $lockTtl = (int) min($lockTtl, $ttl);
        }

        $lockTtl = max($lockTtl, 1);
        $token = $this->cacheManager->acquireLock($cacheKey, $lockTtl);

        if ($token !== null) {
            $lockAcquired = true;

            return $token;
        }

        $waitMs = (int) ($lockConfig['wait_ms'] ?? CacheConfig::DEFAULT_LOCK_WAIT_MS);
        $sleepMs = (int) ($lockConfig['sleep_ms'] ?? CacheConfig::DEFAULT_LOCK_SLEEP_MS);
        $sleepMs = max(1, $sleepMs);
        $elapsed = 0;

        while ($elapsed < $waitMs) {
            usleep($sleepMs * 1000);

            $cachedData = $this->cacheManager->get($cacheKey);
            if ($cachedData !== null) {
                return null;
            }

            $token = $this->cacheManager->acquireLock($cacheKey, $lockTtl);
            if ($token !== null) {
                $lockAcquired = true;

                return $token;
            }

            $elapsed += $sleepMs;
        }

        return null;
    }

    /**
     * Normalize TTL input into a positive integer or null.
     *
     * @param mixed $value
     * @return int|null
     */
    protected function normalizeTtlValue($value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) && is_numeric($value)) {
            $value = (int) $value;
        }

        if (is_int($value) && $value > 0) {
            return $value;
        }

        return null;
    }

    /**
     * Add cache miss headers to the given response instance.
     *
     * @param mixed $response
     * @param Request $request
     * @return void
     */
    protected function addMissHeaders($response, Request $request): void
    {
        if (! config(CacheConfig::CONFIG_ADD_CACHE_HEADERS, true)) {
            return;
        }

        if (($response instanceof Response || $response instanceof JsonResponse) && method_exists($response, 'headers')) {
            $response->headers->set(CacheConfig::HEADER_CACHE_STATUS, CacheStatus::MISS);
            $response->headers->set(CacheConfig::HEADER_CACHE_KEY, $this->keyGenerator->getReadableKey($request));
        }
    }
}


