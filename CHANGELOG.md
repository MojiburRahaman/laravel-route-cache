# Changelog

All notable changes to `laravel-route-cache` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

Nothing yet.

## [1.0.0] - 2024-10-31

### Added
- Initial release of Laravel Route Cache package
- Redis-based route response caching middleware with configurable TTL
- **GitHub Actions CI/CD Pipeline**:
  - Automated testing across PHP 7.4, 8.0, 8.1, 8.2
  - Multi-version Laravel testing (8.x, 9.x, 10.x)
  - PHP CS Fixer for automated code style fixes
  - PHPStan for static analysis
  - Dependabot for dependency updates
  - Changelog automation on releases
- **Core Services**:
  - `CacheManager` - Redis operations with plain text key support
  - `CacheKeyGenerator` - Unique cache key creation
  - `CacheValidator` - Centralized request/response validation
  - `CacheResponseBuilder` - Dedicated response building from cache
  - `CacheResponse` middleware - Thin, focused caching logic (116 lines)
- **Artisan Commands**:
  - `route-cache:clear` - Clear cache entries
  - `route-cache:stats` - View cache statistics
  - `route-cache:install` - Automated setup
  - `route-cache:uninstall` - Clean removal
- **RouteCache Facade** for manual cache management with plain text keys
- **Configuration** (`config/laravel-route-cache.php`):
  - Enable/disable caching globally
  - Default TTL settings
  - Redis connection configuration
  - URL exclusion patterns
  - Query parameter filtering
  - Status code filtering
  - Cache header debugging
- **Smart Caching Features**:
  - Plain text cache keys (automatically hashed internally)
  - Cache hit/miss headers (X-Cache-Status, X-Cache-TTL)
  - URL exclusion patterns for admin/auth routes
  - Query parameter filtering (UTM params, etc.)
  - User-specific caching for authenticated routes
  - JSON and HTML response support
  - Response compression for large content
- **Developer Experience**:
  - Issue templates for bug reports and feature requests
  - Pull request template with branch naming conventions
  - Comprehensive CONTRIBUTING.md guide
  - EditorConfig for consistent coding style
  - Git attributes for proper exports
  - Clean, concise README
- Production-ready error handling and logging
- MIT License
- PSR-4 autoloading with Laravel package auto-discovery

### Security
- Automatic exclusion of sensitive headers (Authorization, Cookie, CSRF tokens)
- Cache only GET requests by default
- Validation of response status codes before caching
- Secure key hashing (MD5 with prefix)

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover any security-related issues, please email contact@mojiburrahaman.dev instead of using the issue tracker.

## Credits

- [Mojiburrahaman](https://github.com/mojiburrahaman)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

