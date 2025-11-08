<?php

declare(strict_types=1);

namespace Mojiburrahaman\LaravelRouteCache;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Mojiburrahaman\LaravelRouteCache\Config\CacheConfig;
use Mojiburrahaman\LaravelRouteCache\Contracts\CacheKeyGeneratorInterface;
use Mojiburrahaman\LaravelRouteCache\Contracts\CacheManagerInterface;
use Mojiburrahaman\LaravelRouteCache\Middleware\CacheResponse;
use Mojiburrahaman\LaravelRouteCache\Services\CacheKeyGenerator;
use Mojiburrahaman\LaravelRouteCache\Services\CacheManager;
use Mojiburrahaman\LaravelRouteCache\Services\CacheResponseBuilder;
use Mojiburrahaman\LaravelRouteCache\Services\CacheValidator;

/**
 * Service Provider for Laravel Route Cache package
 *
 * Registers services, middleware, commands, and configuration
 *
 * @package Mojiburrahaman\LaravelRouteCache
 */
class LaravelRouteCacheServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/laravel-route-cache.php' => config_path('laravel-route-cache.php'),
        ], 'route-cache-config');

        // Auto-register middleware
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('cache.response', CacheResponse::class);
        $router->aliasMiddleware('route.cache', CacheResponse::class);

        // Add Redis connection dynamically from package config
        $this->configureRedisConnection();

        // Register commands if running in console
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Mojiburrahaman\LaravelRouteCache\Console\InstallCommand::class,
                \Mojiburrahaman\LaravelRouteCache\Console\UninstallCommand::class,
                \Mojiburrahaman\LaravelRouteCache\Console\ClearCacheCommand::class,
                \Mojiburrahaman\LaravelRouteCache\Console\CacheStatsCommand::class,
            ]);
        }
    }

    /**
     * Register any application services
     *
     * Binds interfaces to implementations and registers singletons
     *
     * @return void
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-route-cache.php',
            'laravel-route-cache'
        );

        // Register cache key generator
        $this->app->singleton(CacheKeyGeneratorInterface::class, function (Application $app): CacheKeyGeneratorInterface {
            return new CacheKeyGenerator();
        });

        // Backward compatibility binding
        $this->app->singleton(CacheKeyGenerator::class, function (Application $app): CacheKeyGeneratorInterface {
            return $app->make(CacheKeyGeneratorInterface::class);
        });

        // Register cache manager
        $this->app->singleton(CacheManagerInterface::class, function (Application $app): CacheManagerInterface {
            return new CacheManager();
        });

        // Backward compatibility binding
        $this->app->singleton(CacheManager::class, function (Application $app): CacheManagerInterface {
            return $app->make(CacheManagerInterface::class);
        });

        // Register cache validator
        $this->app->singleton(CacheValidator::class, function (Application $app): CacheValidator {
            return new CacheValidator();
        });

        // Register cache response builder
        $this->app->singleton(CacheResponseBuilder::class, function (Application $app): CacheResponseBuilder {
            return new CacheResponseBuilder($app->make(CacheManagerInterface::class));
        });

        // Bind middleware with all dependencies
        $this->app->singleton(CacheResponse::class, function (Application $app): CacheResponse {
            return new CacheResponse(
                $app->make(CacheKeyGeneratorInterface::class),
                $app->make(CacheManagerInterface::class),
                $app->make(CacheValidator::class),
                $app->make(CacheResponseBuilder::class)
            );
        });
    }

    /**
     * Configure Redis connection from package config
     *
     * @return void
     */
    protected function configureRedisConnection(): void
    {
        $redisConfig = config('laravel-route-cache.redis');

        if ($redisConfig && is_array($redisConfig)) {
            config([
                'database.redis.cache' => [
                    'url' => null,
                    'host' => $redisConfig['host'] ?? '127.0.0.1',
                    'password' => $redisConfig['password'] ?? null,
                    'port' => $redisConfig['port'] ?? 6379,
                    'database' => $redisConfig['database'] ?? 0,
                    'options' => [
                        'prefix' => $redisConfig['prefix'] ?? CacheConfig::DEFAULT_REDIS_PREFIX,
                    ],
                ],
            ]);
        }
    }

    /**
     * Get the services provided by the provider
     *
     * @return array<int, string> List of provided services
     */
    public function provides(): array
    {
        return [
            CacheKeyGeneratorInterface::class,
            CacheKeyGenerator::class,
            CacheManagerInterface::class,
            CacheManager::class,
            CacheValidator::class,
            CacheResponseBuilder::class,
            CacheResponse::class,
        ];
    }
}
