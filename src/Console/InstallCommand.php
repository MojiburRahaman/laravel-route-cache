<?php

declare(strict_types=1);

namespace Mojiburrahaman\LaravelRouteCache\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Mojiburrahaman\LaravelRouteCache\Config\CacheConfig;

/**
 * Automated installation command for Laravel Route Cache
 *
 * @package Mojiburrahaman\LaravelRouteCache\Console
 */
class InstallCommand extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'laravel-route-cache:install
                            {--force : Overwrite existing configuration}';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Automatically install and configure Laravel Route Cache package';

    /**
     * Execute the console command
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('🚀 Laravel Route Cache Installation');
        $this->line('==================================');
        $this->newLine();

        try {
            // Step 1: Publish configuration
            $this->step1PublishConfig();

            // Step 2: Add environment variables
            $this->step2AddEnvVariables();

            // Step 3: Register middleware
            $this->step3RegisterMiddleware();

            // Step 4: Update database config for Redis
            $this->step4UpdateDatabaseConfig();

            // Step 5: Test Redis connection
            $this->step5TestRedisConnection();

            $this->newLine();
            $this->info('✅ Laravel Route Cache installed successfully!');
            $this->newLine();

            $this->showNextSteps();

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Installation failed: ' . $e->getMessage());

            return 1;
        }
    }

    /**
     * Step 1: Publish configuration file
     */
    protected function step1PublishConfig(): void
    {
        $this->info('📝 Step 1: Publishing configuration...');

        $this->call('vendor:publish', [
            '--tag' => 'route-cache-config',
            '--force' => $this->option('force'),
        ]);

        $this->line('✅ Configuration published to config/laravel-route-cache.php');
        $this->newLine();
    }

    /**
     * Step 2: Add environment variables
     */
    protected function step2AddEnvVariables(): void
    {
        $this->info('🔧 Step 2: Adding environment variables...');

        $envPath = base_path('.env');
        $envContent = File::exists($envPath) ? File::get($envPath) : '';

        // Check if already configured
        if (strpos($envContent, 'CACHE_ROUTES') !== false) {
            $this->warn('RouteCache variables already exist in .env');

            if (! $this->option('force') && ! $this->confirm('Overwrite existing configuration?', false)) {
                $this->line('Skipping environment setup');
                $this->newLine();

                return;
            }
        }

        // Detect Redis configuration
        $redisHost = $this->detectRedisHost();

        $envVars = $this->getEnvVariables($redisHost);

        // Add to .env
        if (strpos($envContent, 'CACHE_ROUTES') !== false) {
            // Update existing
            foreach ($envVars as $key => $value) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
            }
        } else {
            // Add new section
            $envContent .= "\n\n# Laravel Route Cache - Route Response Caching\n";
            foreach ($envVars as $key => $value) {
                $envContent .= "{$key}={$value}\n";
            }
        }

        File::put($envPath, $envContent);

        $this->line('✅ Environment variables added to .env');
        $this->table(['Variable', 'Value'], array_map(function ($k, $v) {
            return [$k, $v ?: '(empty)'];
        }, array_keys($envVars), $envVars));
        $this->newLine();
    }

    /**
     * Step 3: Register middleware in Kernel
     */
    protected function step3RegisterMiddleware(): void
    {
        $this->info('⚙️  Step 3: Registering middleware...');

        $kernelPath = app_path('Http/Kernel.php');

        if (File::exists($kernelPath)) {
            $this->registerMiddlewareInKernel($kernelPath);
        } else {
            $this->registerMiddlewareInBootstrap();
        }

        $this->newLine();
    }

    protected function registerMiddlewareInKernel(string $kernelPath): void
    {
        $kernelContent = File::get($kernelPath);

        // Check if already registered
        if (strpos($kernelContent, 'cache.response') !== false) {
            $this->line('✅ Middleware already registered');

            return;
        }

        // Add middleware to routeMiddleware array
        $search = "protected \$routeMiddleware = [";
        $middlewareLine = "\n        'cache.response' => \\Mojiburrahaman\\LaravelRouteCache\\Middleware\\CacheResponse::class,";

        if (strpos($kernelContent, $search) !== false) {
            // Find the first line after the opening bracket
            $pattern = '/(protected\s+\$routeMiddleware\s*=\s*\[\s*\n)/';
            $replacement = '$1' . $middlewareLine;
            $kernelContent = preg_replace($pattern, $replacement, $kernelContent, 1);

            File::put($kernelPath, $kernelContent);
            $this->line('✅ Middleware registered in app/Http/Kernel.php');
        } else {
            $this->warn('Could not auto-register middleware. Please add manually:');
            $this->line("'cache.response' => \\Mojiburrahaman\\LaravelRouteCache\\Middleware\\CacheResponse::class,");
        }
    }

    protected function registerMiddlewareInBootstrap(): void
    {
        $bootstrapPath = base_path('bootstrap/app.php');

        if (! File::exists($bootstrapPath)) {
            throw new \RuntimeException('File does not exist at path ' . $bootstrapPath . '.');
        }

        $bootstrapContent = File::get($bootstrapPath);

        if (strpos($bootstrapContent, 'cache.response') !== false) {
            $this->line('✅ Middleware already registered');

            return;
        }

        if ($updated = $this->injectIntoExistingAlias($bootstrapContent)) {
            File::put($bootstrapPath, $updated);
            $this->line('✅ Middleware registered in bootstrap/app.php');

            return;
        }

        $search = '->withMiddleware(function (Middleware $middleware): void {' . PHP_EOL;
        $snippet = $search .
            "        \$aliases = \$middleware->getMiddlewareAliases();\n" .
            "        \$aliases['cache.response'] = \\Mojiburrahaman\\LaravelRouteCache\\Middleware\\CacheResponse::class;\n" .
            "        \$middleware->alias(\$aliases);\n";

        if (strpos($bootstrapContent, $search) !== false) {
            $bootstrapContent = str_replace($search, $snippet, $bootstrapContent);
            File::put($bootstrapPath, $bootstrapContent);
            $this->line('✅ Middleware registered in bootstrap/app.php');

            return;
        }

        $this->warn('Could not auto-register middleware. Please add manually inside bootstrap/app.php middleware configuration:');
        $this->line("\$aliases = \$middleware->getMiddlewareAliases();");
        $this->line("\$aliases['cache.response'] = \\Mojiburrahaman\\LaravelRouteCache\\Middleware\\CacheResponse::class;");
        $this->line("\$middleware->alias(\$aliases);");
    }

    protected function injectIntoExistingAlias(string $bootstrapContent): ?string
    {
        $aliasPos = strpos($bootstrapContent, '$middleware->alias(');

        if ($aliasPos === false) {
            return null;
        }

        $arrayStart = strpos($bootstrapContent, '[', $aliasPos);
        if ($arrayStart === false) {
            return null;
        }

        $offset = $arrayStart + 1;
        $depth = 1;
        $length = strlen($bootstrapContent);

        while ($offset < $length && $depth > 0) {
            $char = $bootstrapContent[$offset];

            if ($char === '[') {
                $depth++;
            } elseif ($char === ']') {
                $depth--;
                if ($depth === 0) {
                    break;
                }
            }

            $offset++;
        }

        if ($depth !== 0) {
            return null;
        }

        $insertionPoint = $offset;
        $existingBlock = substr($bootstrapContent, $arrayStart, $insertionPoint - $arrayStart);

        if (strpos($existingBlock, 'cache.response') !== false) {
            return null;
        }

        $indentation = $this->detectIndentation($existingBlock) ?? '        ';
        $insertion = PHP_EOL . $indentation . "'cache.response' => \\Mojiburrahaman\\LaravelRouteCache\\Middleware\\CacheResponse::class," . PHP_EOL;

        return substr($bootstrapContent, 0, $insertionPoint)
            . $insertion
            . substr($bootstrapContent, $insertionPoint);
    }

    protected function detectIndentation(string $block): ?string
    {
        $lines = preg_split('/\r\n|\r|\n/', $block);

        if (! $lines) {
            return null;
        }

        foreach ($lines as $line) {
            if (trim($line) === '' || strpos($line, ']') !== false) {
                continue;
            }

            preg_match('/^\s*/', $line, $matches);

            if (! empty($matches[0])) {
                return $matches[0];
            }
        }

        return null;
    }

    /**
     * Step 4: Update database config for Redis
     */
    protected function step4UpdateDatabaseConfig(): void
    {
        $this->info('🗄️  Step 4: Configuring Redis connection...');

        $dbConfigPath = config_path('database.php');
        $dbContent = File::get($dbConfigPath);

        // Check if using predis
        if (strpos($dbContent, "'client' => env('REDIS_CLIENT', 'predis')") === false &&
            strpos($dbContent, "'client' => env('REDIS_CLIENT', 'phpredis')") === false) {

            // Update to use phpredis/predis
            $dbContent = preg_replace(
                "/'client'\s*=>\s*env\('REDIS_CLIENT',\s*'[^']+'\)/",
                "'client' => env('REDIS_CLIENT', 'phpredis')",
                $dbContent
            );
        }

        // Check if cache connection has proper config
        if (strpos($dbContent, 'ROUTE_CACHE_REDIS_HOST') === false && strpos($dbContent, 'REDIS_HOST') === false) {
            $this->warn('Redis cache connection not fully configured');
            $this->line('You may need to update config/database.php manually');
        } else {
            $this->line('✅ Redis configuration looks good');
        }

        $this->newLine();
    }

    /**
     * Step 5: Test Redis connection
     */
    protected function step5TestRedisConnection(): void
    {
        $this->info('🔌 Step 5: Testing Redis connection...');

        try {
            $redis = \Illuminate\Support\Facades\Redis::connection('cache');
            $result = $redis->ping();

            if ($result) {
                $this->line('✅ Redis connection successful!');
                $this->line('   Host: ' . env('REDIS_HOST', '127.0.0.1'));
                $this->line('   Port: ' . env('REDIS_PORT', 6379));
                $this->line('   Prefix: ' . env('ROUTE_CACHE_PREFIX', CacheConfig::DEFAULT_REDIS_PREFIX));
            } else {
                $this->warn('⚠️  Redis responded but ping failed');
            }
        } catch (\Exception $e) {
            $this->error('❌ Redis connection failed: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Please check your Redis configuration:');
            $this->line('  - REDIS_HOST=' . env('REDIS_HOST', 'not set'));
            $this->line('  - REDIS_PORT=' . env('REDIS_PORT', 'not set'));
        }

        $this->newLine();
    }

    /**
     * Detect Redis host
     */
    protected function detectRedisHost(): string
    {
        // Check if in Docker
        $inDocker = File::exists('/.dockerenv');

        if ($inDocker || env('REDIS_HOST') === 'redis') {
            return 'redis';
        }

        return '127.0.0.1';
    }

    /**
     * Get environment variables to add
     *
     * Uses Laravel's default REDIS_* variables, only adds Route Cache-specific ones
     *
     * @param string $redisHost
     * @return array<string, string>
     */
    protected function getEnvVariables(string $redisHost): array
    {
        return [
            'CACHE_ROUTES' => 'true',
            'ROUTE_CACHE_TTL' => (string) CacheConfig::DEFAULT_TTL,
            'ROUTE_CACHE_PREFIX' => CacheConfig::DEFAULT_REDIS_PREFIX,
        ];
    }

    /**
     * Show next steps
     */
    protected function showNextSteps(): void
    {
        $this->info('🎯 Next Steps:');
        $this->line('=============');
        $this->newLine();

        $this->line('1. Apply caching to your routes:');
        $this->line('   Route::get(\'/blog\', [Controller::class, \'index\'])');
        $this->line('       ->middleware(\'cache.response:3600\');');
        $this->newLine();

        $this->line('2. View cache statistics:');
        $this->line('   php artisan laravel-route-cache:stats');
        $this->newLine();

        $this->line('3. Clear cache when needed:');
        $this->line('   php artisan laravel-route-cache:clear');
        $this->newLine();

        $this->line('4. Test your cached routes:');
        $this->line('   Visit your routes and check response headers');
        $this->line('   First request: X-Cache-Status: MISS');
        $this->line('   Second request: X-Cache-Status: HIT');
        $this->newLine();

        $this->info('📚 Documentation: packages/laravel-route-cache/README.md');
        $this->newLine();
    }
}
