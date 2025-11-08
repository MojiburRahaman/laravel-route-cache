<?php

declare(strict_types=1);

namespace Mojiburrahaman\LaravelRouteCache\Enums;

/**
 * Cache hit/miss status enumeration
 *
 * @package Mojiburrahaman\LaravelRouteCache\Enums
 */
final class CacheStatus
{
    /**
     * Cache hit - response served from cache
     */
    public const HIT = 'HIT';

    /**
     * Cache miss - response generated fresh
     */
    public const MISS = 'MISS';

    /**
     * Cache bypassed - caching disabled or excluded
     */
    public const BYPASS = 'BYPASS';

    /**
     * Prevent instantiation
     */
    private function __construct()
    {
    }
}
