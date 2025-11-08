<?php

declare(strict_types=1);

namespace Mojiburrahaman\LaravelRouteCache\Contracts;

use Illuminate\Http\Request;

/**
 * Interface for cache key generation
 *
 * @package Mojiburrahaman\LaravelRouteCache\Contracts
 */
interface CacheKeyGeneratorInterface
{
    /**
     * Generate a unique cache key for the given request
     *
     * @param Request $request The HTTP request
     * @return string The generated cache key
     */
    public function generate(Request $request): string;

    /**
     * Get a human-readable cache key (for debugging)
     *
     * @param Request $request The HTTP request
     * @return string The readable cache key
     */
    public function getReadableKey(Request $request): string;
}
