<?php

declare(strict_types=1);

namespace Mojiburrahaman\LaravelRouteCache\Console;

use Illuminate\Console\Command;
use Mojiburrahaman\LaravelRouteCache\Config\CacheConfig;
use Mojiburrahaman\LaravelRouteCache\Contracts\CacheManagerInterface;

/**
 * Artisan command to display Laravel Route Cache statistics
 *
 * @package Mojiburrahaman\LaravelRouteCache\Console
 */
class CacheStatsCommand extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'laravel-route-cache:stats';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Display Route Cache statistics including cached routes and Redis info';

    /**
     * Cache manager instance
     *
     * @var CacheManagerInterface
     */
    protected CacheManagerInterface $cacheManager;

    /**
     * Create a new command instance
     *
     * @param CacheManagerInterface $cacheManager The cache manager
     */
    public function __construct(CacheManagerInterface $cacheManager)
    {
        parent::__construct();
        $this->cacheManager = $cacheManager;
    }

    /**
     * Execute the console command
     *
     * Displays statistics about cached routes, Redis configuration,
     * and recent cache keys with their TTL values
     *
     * @return int Exit code (0 = success, 1 = error)
     */
    public function handle(): int
    {
        $this->info('RouteCache Statistics');
        $this->line('==================');
        $this->newLine();

        try {
            $connection = config('laravel-route-cache.redis_connection', CacheConfig::DEFAULT_CONNECTION);
            $prefix = config('laravel-route-cache.redis.prefix', CacheConfig::DEFAULT_REDIS_PREFIX);
            $prefix = rtrim($prefix, ':');

            // Get Redis connection through CacheManager
            $redis = $this->cacheManager->getRedisConnection();

            // Since the connection already has the prefix, search for all keys
            // The connection prefix is already set (e.g., 'route_cache:') so we just need '*'
            $keys = $redis->keys('*');
            if (is_array($keys)) {
                sort($keys, SORT_STRING);
                $totalKeys = count($keys);
            } else {
                $keys = [];
                $totalKeys = 0;
            }

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Cached Routes', $totalKeys],
                    ['Redis Connection', $connection],
                    ['Cache Prefix', $prefix],
                    ['Default TTL', config('laravel-route-cache.default_ttl', 3600) . ' seconds'],
                    ['Cache Enabled', config('laravel-route-cache.enabled', true) ? 'Yes' : 'No'],
                ]
            );

            if ($totalKeys > 0) {
                $this->newLine();
                $this->info("Recent cached keys (showing first 10):");

                $sampleKeys = array_slice($keys, 0, 10);

                // Transform keys into table data using array_map
                $keyData = array_map(function ($key) use ($redis, $prefix) {
                    $displayKey = str_replace($prefix . ':', '', $key);
                    $ttl = $redis->ttl(str_replace($prefix . ':', '', $key));
                    // Format TTL for human-readable output
                    if ($ttl === -2) {
                        $ttlDisplay = 'Key not found';
                    } elseif ($ttl === -1) {
                        $ttlDisplay = 'No expiry';
                    } else {
                        $ttlDisplay = $this->formatSeconds($ttl);
                    }

                    return [$displayKey, $ttlDisplay];
                }, $sampleKeys);

                usort($keyData, static fn (array $a, array $b): int => strcmp($a[0], $b[0]));

                $this->table(['Cache Key', 'TTL'], $keyData);
            }

        } catch (\Exception $e) {
            $this->error('❌ Failed to retrieve cache statistics: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Tip: Check if Redis is running and accessible');

            return 1;
        }

        return 0;
    }

    /**
     * Format seconds into human-readable time
     *
     * @param int $seconds
     * @return string
     */
    protected function formatSeconds(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' second' . ($seconds !== 1 ? 's' : '');
        }

        if ($seconds < 3600) {
            $minutes = (int) floor($seconds / 60);
            $secs = $seconds % 60;
            $result = $minutes . ' minute' . ($minutes !== 1 ? 's' : '');
            if ($secs > 0) {
                $result .= ' ' . $secs . ' second' . ($secs !== 1 ? 's' : '');
            }

            return $result;
        }

        if ($seconds < 86400) {
            $hours = (int) floor($seconds / 3600);
            $minutes = (int) floor(($seconds % 3600) / 60);
            $result = $hours . ' hour' . ($hours !== 1 ? 's' : '');
            if ($minutes > 0) {
                $result .= ' ' . $minutes . ' minute' . ($minutes !== 1 ? 's' : '');
            }

            return $result;
        }

        $days = (int) floor($seconds / 86400);
        $hours = (int) floor(($seconds % 86400) / 3600);
        $result = $days . ' day' . ($days !== 1 ? 's' : '');
        if ($hours > 0) {
            $result .= ' ' . $hours . ' hour' . ($hours !== 1 ? 's' : '');
        }

        return $result;
    }
}
