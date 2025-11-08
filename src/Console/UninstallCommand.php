<?php

declare(strict_types=1);

namespace Mojiburrahaman\LaravelRouteCache\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Automated uninstallation command for Laravel Route Cache
 *
 * @package Mojiburrahaman\LaravelRouteCache\Console
 */
class UninstallCommand extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'laravel-route-cache:uninstall
                            {--keep-config : Keep configuration file}';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Uninstall Laravel Route Cache package and clean up';

    /**
     * Execute the console command
     *
     * @return int
     */
    public function handle(): int
    {
        $this->warn('🗑️  Laravel Route Cache Uninstallation');
        $this->line('====================================');
        $this->newLine();

        if (! $this->confirm('Are you sure you want to uninstall Laravel Route Cache?', false)) {
            $this->info('Uninstallation cancelled');

            return 0;
        }

        try {
            // Step 1: Clear all cache
            $this->step1ClearCache();

            // Step 2: Remove middleware registration
            $this->step2RemoveMiddleware();

            // Step 3: Remove environment variables
            $this->step3RemoveEnvVariables();

            // Step 4: Remove config file (optional)
            if (! $this->option('keep-config')) {
                $this->step4RemoveConfig();
            }

            $this->newLine();
            $this->info('✅ Laravel Route Cache uninstalled successfully!');
            $this->newLine();

            $this->showFinalSteps();

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Uninstallation failed: ' . $e->getMessage());

            return 1;
        }
    }

    /**
     * Step 1: Clear all cache
     */
    protected function step1ClearCache(): void
    {
        $this->info('🗑️  Step 1: Clearing all cache...');
        $this->call('laravel-route-cache:clear');
        $this->newLine();
    }

    /**
     * Step 2: Remove middleware from Kernel
     */
    protected function step2RemoveMiddleware(): void
    {
        $this->info('⚙️  Step 2: Removing middleware registration...');

        $kernelPath = app_path('Http/Kernel.php');

        if (! File::exists($kernelPath)) {
            $this->warn('Kernel.php not found');

            return;
        }

        $kernelContent = File::get($kernelPath);

        // Remove middleware line
        $kernelContent = preg_replace(
            "/\s*'cache\.response'\s*=>\s*.*CacheResponse::class,?\s*\n/",
            "",
            $kernelContent
        );

        File::put($kernelPath, $kernelContent);
        $this->line('✅ Middleware registration removed');
        $this->newLine();
    }

    /**
     * Step 3: Remove environment variables
     */
    protected function step3RemoveEnvVariables(): void
    {
        $this->info('🔧 Step 3: Removing environment variables...');

        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            $this->warn('.env file not found');

            return;
        }

        $envContent = File::get($envPath);

        // Remove Laravel Route Cache section (both old LaraCache and new Route Cache format)
        $envContent = preg_replace(
            "/\n*# (LaraCache|Laravel Route Cache).*?\n(LARA_CACHE_.*?\n|ROUTE_CACHE_.*?\n|CACHE_ROUTES.*?\n)+/s",
            "",
            $envContent
        );

        File::put($envPath, $envContent);
        $this->line('✅ Environment variables removed');
        $this->newLine();
    }

    /**
     * Step 4: Remove configuration file
     */
    protected function step4RemoveConfig(): void
    {
        $this->info('📝 Step 4: Removing configuration file...');

        $configPath = config_path('laravel-route-cache.php');

        if (File::exists($configPath)) {
            File::delete($configPath);
            $this->line('✅ Configuration file removed');
        } else {
            $this->line('Configuration file not found');
        }

        $this->newLine();
    }

    /**
     * Show final steps
     */
    protected function showFinalSteps(): void
    {
        $this->info('📋 Final Steps:');
        $this->line('==============');
        $this->newLine();

        $this->line('1. Remove package from composer.json:');
        $this->line('   "mojiburrahaman/laravel-route-cache": "@dev"');
        $this->newLine();

        $this->line('2. Run composer update:');
        $this->line('   composer update');
        $this->newLine();

        $this->line('3. Clear Laravel caches:');
        $this->line('   php artisan config:clear');
        $this->line('   php artisan route:clear');
        $this->line('   php artisan cache:clear');
        $this->newLine();
    }
}
