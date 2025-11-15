<?php

declare(strict_types=1);

namespace Mojiburrahaman\LaravelRouteCache\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mojiburrahaman\LaravelRouteCache\Services\CacheMiddlewareHandler;

/**
 * Middleware for caching route responses in Redis
 *
 * Thin middleware that delegates to service classes for logic
 *
 * @package Mojiburrahaman\LaravelRouteCache\Middleware
 */
class CacheResponse
{
    protected CacheMiddlewareHandler $handler;

    /**
     * @param CacheMiddlewareHandler $handler
     */
    public function __construct(CacheMiddlewareHandler $handler)
    {
        $this->handler = $handler;
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
        return $this->handler->handle($request, $next, $ttl);
    }
}
