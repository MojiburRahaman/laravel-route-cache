<?php

declare(strict_types=1);

namespace Mojiburrahaman\LaravelRouteCache\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mojiburrahaman\LaravelRouteCache\Config\CacheConfig;
use Mojiburrahaman\LaravelRouteCache\Contracts\CacheKeyGeneratorInterface;
use Mojiburrahaman\LaravelRouteCache\Contracts\CacheManagerInterface;
use Mojiburrahaman\LaravelRouteCache\Enums\CacheStatus;
use Mojiburrahaman\LaravelRouteCache\Services\CacheResponseBuilder;
use Mojiburrahaman\LaravelRouteCache\Services\CacheValidator;

/**
 * Middleware for caching route responses in Redis
 *
 * Thin middleware that delegates to service classes for logic
 *
 * @package Mojiburrahaman\LaravelRouteCache\Middleware
 */
class CacheResponse
{
    /**
     * @var CacheKeyGeneratorInterface
     */
    protected CacheKeyGeneratorInterface $keyGenerator;

    /**
     * @var CacheManagerInterface
     */
    protected CacheManagerInterface $cacheManager;

    /**
     * @var CacheValidator
     */
    protected CacheValidator $validator;

    /**
     * @var CacheResponseBuilder
     */
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
     * Handle incoming request
     *
     * @param Request $request
     * @param Closure $next
     * @param int|null $ttl
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?int $ttl = null)
    {
        try {
            // Skip if caching is disabled or request is not cacheable
            if (! $this->validator->isEnabled() || ! $this->validator->isRequestCacheable($request)) {
                return $next($request);
            }

            // Try to get from cache
            $cacheKey = $this->keyGenerator->generate($request);
            $cachedData = $this->cacheManager->get($cacheKey);

            if ($cachedData !== null) {
                return $this->responseBuilder->build($cachedData, $cacheKey);
            }

            // Execute request and cache response
            $response = $next($request);

            if ($this->validator->isResponseCacheable($response)) {
                $this->cacheManager->put($cacheKey, $response, $ttl);
            }

            $this->addMissHeaders($response, $request);

            return $response;
        } catch (\Exception $e) {
            // If caching fails, still execute the request but log the error
            \Illuminate\Support\Facades\Log::warning('Route cache middleware failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $response = $next($request);

            // Still add MISS header even if caching failed
            $this->addMissHeaders($response, $request);

            return $response;
        }
    }

    /**
     * Add cache miss headers to response
     *
     * @param mixed $response
     * @param Request $request
     * @return void
     */
    protected function addMissHeaders($response, Request $request): void
    {
        if (! config('laravel-route-cache.add_cache_headers', true)) {
            return;
        }

        if (($response instanceof Response || $response instanceof JsonResponse) && method_exists($response, 'headers')) {
            $response->headers->set(CacheConfig::HEADER_CACHE_STATUS, CacheStatus::MISS);
            $response->headers->set(CacheConfig::HEADER_CACHE_KEY, $this->keyGenerator->getReadableKey($request));
        }
    }
}
