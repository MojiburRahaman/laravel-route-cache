<?php

namespace Mojiburrahaman\LaravelRouteCache\Tests;

use Illuminate\Support\Facades\Redis;
use Mojiburrahaman\LaravelRouteCache\LaravelRouteCacheServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clear Redis before each test
        $this->clearCache();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelRouteCacheServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default configuration
        $app['config']->set('laravel-route-cache.enabled', true);
        $app['config']->set('laravel-route-cache.default_ttl', 3600);
        $app['config']->set('laravel-route-cache.cache_key_prefix', 'route_cache');
        $app['config']->set('laravel-route-cache.redis_connection', 'cache');
        $app['config']->set('laravel-route-cache.add_cache_headers', true);
        $app['config']->set('laravel-route-cache.cache_only_success', true);
        $app['config']->set('laravel-route-cache.ignore_query_params', ['utm_source', 'utm_medium']);
        $app['config']->set('laravel-route-cache.exclude_urls', ['api/admin/*']);

        // Setup Redis connection
        $app['config']->set('database.redis.cache', [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
            'database' => 1,
        ]);
    }

    protected function clearCache(): void
    {
        try {
            Redis::connection('cache')->flushdb();
        } catch (\Exception $e) {
            // Ignore if Redis is not available
        }
    }

    protected function tearDown(): void
    {
        $this->clearCache();
        parent::tearDown();
    }

    /**
     * Refresh cache-related services
     * Call this after changing config in tests
     */
    protected function refreshServices(): void
    {
        $this->app->forgetInstance(\Mojiburrahaman\LaravelRouteCache\Services\CacheValidator::class);
        $this->app->forgetInstance(\Mojiburrahaman\LaravelRouteCache\Contracts\CacheKeyGeneratorInterface::class);
        $this->app->forgetInstance(\Mojiburrahaman\LaravelRouteCache\Contracts\CacheManagerInterface::class);
        $this->app->forgetInstance(\Mojiburrahaman\LaravelRouteCache\Middleware\CacheResponse::class);
    }
}
