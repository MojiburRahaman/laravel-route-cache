<?php

declare(strict_types=1);

namespace Mojiburrahaman\LaravelRouteCache\Console;

use Illuminate\Console\Command;
use Mojiburrahaman\LaravelRouteCache\Contracts\CacheManagerInterface;

/**
 * Artisan command to clear Laravel Route Cache
 *
 * @package Mojiburrahaman\LaravelRouteCache\Console
 */
class ClearCacheCommand extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'laravel-route-cache:clear
                            {--key= : Clear specific cache key (MD5 hash)}';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Clear all Route Cache entries or a specific cache key';

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
     * @return int Exit code (0 = success)
     */
    public function handle(): int
    {
        $key = $this->option('key');

        if ($key) {
            return $this->clearSpecificKey((string) $key);
        }

        return $this->clearAllCache();
    }

    /**
     * Clear a specific cache key
     *
     * @param string $key The cache key to clear
     * @return int Exit code
     */
    protected function clearSpecificKey(string $key): int
    {
        if ($this->cacheManager->forget($key)) {
            $this->info("✅ Cache cleared for key: {$key}");

            return 0;
        }

        $this->error("❌ Failed to clear cache for key: {$key}");

        return 1;
    }

    /**
     * Clear all cache entries
     *
     * @return int Exit code
     */
    protected function clearAllCache(): int
    {
        if ($this->cacheManager->flush()) {
            $this->info('✅ All route cache cleared successfully!');

            return 0;
        }

        $this->error('❌ Failed to clear cache.');

        return 1;
    }
}
