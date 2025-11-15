# Laravel Route Cache

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mojiburrahaman/laravel-route-cache.svg?style=flat-square)](https://packagist.org/packages/mojiburrahaman/laravel-route-cache)
[![Total Downloads](https://img.shields.io/packagist/dt/mojiburrahaman/laravel-route-cache.svg?style=flat-square)](https://packagist.org/packages/mojiburrahaman/laravel-route-cache)
[![License](https://img.shields.io/packagist/l/mojiburrahaman/laravel-route-cache.svg?style=flat-square)](https://packagist.org/packages/mojiburrahaman/laravel-route-cache)

A simple and powerful Laravel package for caching route responses in Redis. Speed up your API and web routes with automatic response caching.

## ✨ Features

- 🚀 **Easy Setup** - One command installation
- ⚡ **Fast** - Redis-powered caching with configurable TTL
- 🎯 **Flexible** - Per-route or route group caching
- 🔑 **Smart Keys** - Plain text cache keys with automatic hashing
- 👤 **User-Aware** - Different cache for authenticated users
- 🔒 **Stampede Safe** - Redis locks prevent cache storms under load
- 🎨 **Developer Friendly** - Debug headers and artisan commands
- 🛡️ **Production Ready** - Secure and battle-tested

## 📋 Requirements

- PHP 7.4 or higher (Laravel 8.x works on PHP 7.4+, Laravel 9+ require PHP 8.0+)
- Laravel 8.x, 9.x, 10.x, or 11.x
- Redis server

**Version Compatibility Matrix:**

| Laravel | PHP 7.4 | PHP 8.0 | PHP 8.1 | PHP 8.2 | PHP 8.3 |
|---------|---------|---------|---------|---------|---------|
| 8.x     | ✅      | ✅      | ✅      | ✅*     | ✅*     |
| 9.x     | ❌      | ✅      | ✅      | ✅      | ✅      |
| 10.x    | ❌      | ❌      | ✅      | ✅      | ✅      |
| 11.x    | ❌      | ❌      | ❌      | ✅      | ✅      |

> \* Laravel 8 officially supports PHP up to 8.1. Later PHP 8 releases work in practice but depend on corresponding framework updates. Use the Laravel version that matches your PHP runtime.

## 📦 Installation

Install via Composer:

```bash
composer require mojiburrahaman/laravel-route-cache
```

### Option 1: Auto Installation (Recommended)

```bash
php artisan laravel-route-cache:install
```

This will automatically:
- Publish the configuration file
- Add environment variables to `.env`
- Register middleware (if needed)
- Configure Redis connection
- Test Redis connectivity

### Option 2: Manual Installation

Publish the configuration:

```bash
php artisan vendor:publish --tag=route-cache-config
```

Add to your `.env`:

```env
CACHE_ROUTES=true
ROUTE_CACHE_TTL=3600
ROUTE_CACHE_PREFIX=route_cache:
```

That's it! The package automatically uses your existing Redis configuration.

## 🚀 Quick Start

### Cache a Single Route

```php
Route::get('/api/posts', [PostController::class, 'index'])
    ->middleware('route.cache:3600'); // Cache for 1 hour
```

### Cache Multiple Routes

```php
Route::middleware(['route.cache:7200'])->group(function () {
    Route::get('/api/posts', [PostController::class, 'index']);
    Route::get('/api/posts/{id}', [PostController::class, 'show']);
});
```

### Manual Cache Control

```php
use Mojiburrahaman\LaravelRouteCache\Facades\RouteCache;

// Simple format - just use the path (GET is assumed)
if (RouteCache::has('/')) {
    $homeData = RouteCache::get('/');
}

// Check other routes
$projectsData = RouteCache::get('/projects');
$apiData = RouteCache::get('/api/users');

// Clear specific cache
RouteCache::forget('/posts');

// Get TTL (time remaining)
$ttl = RouteCache::ttl('/api/posts');

// For non-GET requests, specify the method
$postData = RouteCache::get('POST:/api/posts');
RouteCache::forget('DELETE:/api/posts/1');

// Flush all cache
RouteCache::flush();
```

## 🎛️ Configuration

The `config/laravel-route-cache.php` file contains all settings:

```php
return [
    // Enable/disable caching
    'enabled' => env('CACHE_ROUTES', true),

    // Default cache lifetime (seconds)
    'default_ttl' => env('ROUTE_CACHE_TTL', 3600),

    // URLs to exclude from caching
    'exclude_urls' => [
        'api/admin/*',
        'api/auth/*',
    ],

    // Query params to ignore (tracking params)
    'ignore_query_params' => [
        'utm_source',
        'utm_medium',
        'utm_campaign',
    ],

    // Cache only successful responses
    'cache_only_success' => true,

    // Add debug headers
    'add_cache_headers' => true,

    // Prevent cache stampedes
    'lock' => [
        'enabled' => true,
        'ttl' => 10,        // lock lifetime (seconds)
        'wait_ms' => 3000,  // total wait time while another request warms the cache
        'sleep_ms' => 50,   // wait between retries
    ],
];
```

## 🛠️ Artisan Commands

### Clear Cache

```bash
# Clear all cache
php artisan laravel-route-cache:clear

# Clear specific key
php artisan laravel-route-cache:clear --key=my-key
```

### View Statistics

```bash
php artisan laravel-route-cache:stats
```

Shows:
- Total cached entries
- Redis connection info
- Cache configuration
- Sample cached keys with TTL

## 💡 Advanced Usage

### Per-Environment TTL

```php
// Short cache for dev, long for production
$ttl = app()->environment('production') ? 7200 : 300;

Route::get('/api/data', [DataController::class, 'index'])
    ->middleware("route.cache:$ttl");
```

### Clear Cache on Model Updates

```php
use Mojiburrahaman\LaravelRouteCache\Facades\RouteCache;

class PostController extends Controller
{
    public function update(Request $request, Post $post)
    {
        $post->update($request->validated());
        
        // Clear cache after update
        RouteCache::flush();
        
        return response()->json($post);
    }
}
```

### Debug Cache Status

Response headers show cache status:

```bash
curl -I https://yourapp.com/api/posts

# Response includes:
X-Cache-Status: HIT
X-Cache-Key: route_cache:GET:api/posts
X-Cached-At: 2024-10-31T10:30:00Z
X-Cache-TTL: 3456
```

## 🔒 Security

The package automatically:
- Excludes sensitive headers (Authorization, Cookie, CSRF tokens)
- Caches only GET requests by default
- Supports per-user cache for authenticated routes
- Validates status codes before caching

## 📊 Performance

Typical results:
- **Without cache**: ~200-500ms response time
- **With cache**: ~10-30ms response time
- **Improvement**: 10-50x faster ⚡

## 🧪 Testing

```bash
composer test
```

## 📝 Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## 🔐 Security

If you discover any security issues, please email contact@mojiburrahaman.dev instead of using the issue tracker.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## 👨‍💻 Author

**Mojiburrahaman**
- Website: [mojiburrahaman.dev](https://mojiburrahaman.dev)
- Email: contact@mojiburrahaman.dev

## ⭐ Show Your Support

Give a ⭐️ if this project helped you!

---

Made with ❤️ for the Laravel community
