# Upgrade Guide

## Version 2.0.0 - Laravel 9+ Support

### What Changed

This package has been updated to support modern Laravel versions (9, 10, and 11) and PHP 8.0+.

### Breaking Changes

- **Minimum PHP version**: Now requires PHP 8.0 or higher (previously 7.4+)
- **Minimum Laravel version**: Now requires Laravel 9.0 or higher (previously 8.0+)
- **Dropped support**: PHP 7.4 and Laravel 8.x are no longer supported

### New Features

- Support for Laravel 9.x, 10.x, and 11.x
- Support for PHP 8.0, 8.1, 8.2, and 8.3
- Added PHP CS Fixer for code style consistency
- Added PHPStan for static analysis
- Added Larastan for Laravel-specific static analysis

### Migration Steps

If you're upgrading from version 1.x:

1. **Update your PHP version**
   ```bash
   # Ensure you're running PHP 8.0 or higher
   php -v
   ```

2. **Update your Laravel version**
   ```bash
   # Ensure you're running Laravel 9.0 or higher
   composer show laravel/framework
   ```

3. **Update the package**
   ```bash
   composer update mojiburrahaman/laravel-route-cache
   ```

4. **Clear caches**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   ```

### Configuration Changes

No configuration changes are required. All existing configurations remain compatible.

### Testing

If you're contributing to the package:

```bash
# Install dependencies
composer install

# Run tests
vendor/bin/phpunit

# Run code style fixer
vendor/bin/php-cs-fixer fix --allow-risky=yes

# Run static analysis
vendor/bin/phpstan analyse
```

### GitHub Actions

All CI workflows have been updated to:
- Test against PHP 8.0, 8.1, 8.2, and 8.3
- Test against Laravel 9.x, 10.x, and 11.x
- Clear composer cache on each run to prevent stale dependencies
- Include PHP CS Fixer and PHPStan checks

**Supported PHP/Laravel Version Matrix:**

| Laravel | PHP 8.0 | PHP 8.1 | PHP 8.2 | PHP 8.3 |
|---------|---------|---------|---------|---------|
| 9.x     | ✅      | ✅      | ✅      | ✅      |
| 10.x    | ❌      | ✅      | ✅      | ✅      |
| 11.x    | ❌      | ❌      | ✅      | ✅      |

**Notes:**
- Laravel 10 requires PHP 8.1+
- Laravel 11 requires PHP 8.2+

### Support

If you encounter any issues during the upgrade:
1. Check the [CHANGELOG.md](../CHANGELOG.md) for detailed changes
2. Review the [README.md](../README.md) for updated documentation
3. Open an issue on GitHub if problems persist

