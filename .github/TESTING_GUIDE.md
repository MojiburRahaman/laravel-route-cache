# Testing Guide

## Overview

This guide explains how to run and fix tests for the Laravel Route Cache package.

## Running Tests

### Local Testing (with Redis)

```bash
# Make sure Redis is running
redis-cli ping  # Should return "PONG"

# Run all tests
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit --testsuite="Laravel Route Cache Test Suite"

# Run specific test file
vendor/bin/phpunit tests/Unit/CacheKeyGeneratorTest.php

# Run with verbose output
vendor/bin/phpunit --verbose
```

### Testing without Redis

If Redis is not available, some tests will be skipped gracefully. The `TestCase` class catches Redis exceptions.

## Common Test Issues & Fixes

### Issue 1: CacheKeyGenerator Returns Plain Text, Not Hashes

**Problem:** The `CacheKeyGenerator::generate()` method returns plain text keys (e.g., `"GET:api/posts"`), not MD5 hashes.

**Solution:** Tests should check for plain text format:

```php
// ❌ Wrong - expects MD5 hash
$this->assertEquals(32, strlen($key)); // MD5 hash length

// ✅ Correct - checks plain text format
$this->assertStringContainsString('GET', $key);
$this->assertStringContainsString('api/posts', $key);
```

**Why?** The hashing is done by `CacheManager::hashKey()`, not the generator.

---

### Issue 2: Redis Connection Issues

**Problem:** Tests fail with "Connection refused" or "Redis not available"

**Solution:** 
1. Start Redis: `redis-server`
2. Check Redis is running: `redis-cli ping`
3. Update `phpunit.xml` if using different Redis settings:
   ```xml
   <env name="ROUTE_CACHE_REDIS_HOST" value="127.0.0.1"/>
   <env name="ROUTE_CACHE_REDIS_PORT" value="6379"/>
   <env name="ROUTE_CACHE_REDIS_DB" value="1"/>
   ```

---

### Issue 3: Missing Dependencies

**Problem:** Class not found or autoload issues

**Solution:**
```bash
# Regenerate autoload files
composer dump-autoload

# Reinstall dependencies
rm -rf vendor composer.lock
composer install
```

---

### Issue 4: Config Not Loaded

**Problem:** Config values return null or default values

**Solution:** Check `TestCase::getEnvironmentSetUp()` properly sets all config:

```php
protected function getEnvironmentSetUp($app): void
{
    config()->set('laravel-route-cache.enabled', true);
    config()->set('laravel-route-cache.default_ttl', 3600);
    // ... more config
}
```

---

### Issue 5: Carbon Date Type Error

**Problem:** 
```
TypeError: Carbon\Carbon::setLastErrors(): Argument #1 ($lastErrors) must be of type array, false given
```

**Root Cause:** Version incompatibility between Carbon and PHP 8.1+

**Solution Applied:**
1. **Removed direct Carbon usage** from package code
2. Replaced all Carbon usages with native PHP functions:
   - `now()->toIso8601String()` → `date('Y-m-d H:i:s')` in CacheManager
   - `now()->toIso8601String()` → `date('Y-m-d H:i:s')` in test files
   - `CarbonInterval::seconds()->forHumans()` → Custom `formatSeconds()` helper in CacheStatsCommand
3. **Added Carbon as dev-dependency** with compatible version `^2.67.0|^3.0`
   - This is needed because Laravel itself depends on Carbon
   - By specifying minimum version 2.67.0, we ensure PHP 8.0-8.3 compatibility
   - Version 2.67.0+ is fully tested and stable with PHP 8.1+
4. This avoids Carbon compatibility issues and improves package performance

**Files Fixed:**
- `src/Services/CacheManager.php` (line 142)
- `src/Console/CacheStatsCommand.php` (lines 7, 106, + new helper method)
- `tests/Unit/CacheResponseBuilderTest.php` (multiple occurrences)
- `composer.json` (added Carbon ^2.67.0|^3.0 as dev dependency with version constraint)
- `.github/workflows/run-tests.yml` (added explicit Carbon upgrade step to handle prefer-lowest)

---

## Test Structure

### Unit Tests
- `tests/Unit/` - Test individual service classes in isolation
- Don't require Redis or full Laravel app
- Fast and focused

### Feature Tests
- `tests/Feature/` - Test complete workflows
- Require Redis connection
- Test middleware, facades, integration

## Writing New Tests

### Unit Test Template

```php
<?php

namespace Mojiburrahaman\LaravelRouteCache\Tests\Unit;

use Mojiburrahaman\LaravelRouteCache\Tests\TestCase;

class MyServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MyService();
    }

    /** @test */
    public function it_does_something()
    {
        // Arrange
        $input = 'test';
        
        // Act
        $result = $this->service->doSomething($input);
        
        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

### Feature Test Template

```php
<?php

namespace Mojiburrahaman\LaravelRouteCache\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Mojiburrahaman\LaravelRouteCache\Tests\TestCase;

class MyFeatureTest extends TestCase
{
    /** @test */
    public function it_caches_route_response()
    {
        // Define test route
        Route::get('/test', function () {
            return response()->json(['data' => 'test']);
        })->middleware('cache.response:3600');

        // First request - should cache
        $response = $this->get('/test');
        $response->assertStatus(200);
        $response->assertHeader('X-Cache-Status', 'miss');

        // Second request - should hit cache
        $response = $this->get('/test');
        $response->assertStatus(200);
        $response->assertHeader('X-Cache-Status', 'hit');
    }
}
```

## Debugging Failed Tests

### Enable Verbose Output

```bash
vendor/bin/phpunit --verbose --debug
```

### Check Redis Keys

```bash
# Connect to Redis
redis-cli

# Switch to test database
SELECT 1

# List all keys
KEYS *

# Check specific key
GET route_cache:xxxxx

# Clear test database
FLUSHDB
```

### Enable Laravel Logging

```php
use Illuminate\Support\Facades\Log;

Log::debug('Test checkpoint', ['data' => $someVariable]);
```

## CI/CD Testing

### GitHub Actions Matrix

Tests run against multiple PHP and Laravel versions:

```yaml
matrix:
  php: [8.3, 8.2, 8.1, 8.0]
  laravel: [11.*, 10.*, 9.*]
  stability: [prefer-lowest, prefer-stable]
```

### Required Services

```yaml
services:
  redis:
    image: redis:7-alpine
    ports:
      - 6379:6379
```

## Code Coverage

### Generate Coverage Report

```bash
# HTML report
vendor/bin/phpunit --coverage-html coverage/

# Text report
vendor/bin/phpunit --coverage-text

# Clover XML (for CI)
vendor/bin/phpunit --coverage-clover coverage.xml
```

### View Coverage

```bash
# Open HTML report
open coverage/index.html  # macOS
xdg-open coverage/index.html  # Linux
```

## Common Assertions

```php
// String checks
$this->assertIsString($value);
$this->assertStringContainsString('needle', $haystack);

// Array checks
$this->assertIsArray($value);
$this->assertArrayHasKey('key', $array);
$this->assertCount(5, $array);

// Boolean checks
$this->assertTrue($condition);
$this->assertFalse($condition);

// Equality checks
$this->assertEquals($expected, $actual);
$this->assertSame($expected, $actual);  // Strict comparison

// Response checks (Feature tests)
$response->assertStatus(200);
$response->assertJson(['key' => 'value']);
$response->assertHeader('X-Cache-Status', 'hit');
```

## Best Practices

1. **Use descriptive test names**: `it_caches_authenticated_user_requests`
2. **Follow AAA pattern**: Arrange, Act, Assert
3. **One assertion per test**: Focus on testing one thing
4. **Clean up after tests**: Use `tearDown()` to clear Redis
5. **Mock external dependencies**: Don't rely on external services
6. **Test edge cases**: Empty strings, null values, large data
7. **Use data providers**: For testing multiple inputs

## Example Data Provider

```php
/**
 * @test
 * @dataProvider httpMethodProvider
 */
public function it_handles_different_http_methods($method)
{
    $request = Request::create('/api/test', $method);
    $key = $this->generator->generate($request);
    
    $this->assertStringContainsString($method, $key);
}

public function httpMethodProvider()
{
    return [
        ['GET'],
        ['POST'],
        ['PUT'],
        ['DELETE'],
    ];
}
```

## Troubleshooting Checklist

- [ ] Redis is running and accessible
- [ ] Composer dependencies are installed
- [ ] Autoload files are regenerated
- [ ] Environment variables are set in `phpunit.xml`
- [ ] Test database is clean (no stale data)
- [ ] PHP version matches requirements (8.0+)
- [ ] Laravel version is compatible (9, 10, or 11)

## Getting Help

If tests continue to fail:

1. Check the error message carefully
2. Run tests with `--verbose` flag
3. Check Redis logs: `redis-cli MONITOR`
4. Enable Laravel debug mode in tests
5. Review the test expectations vs implementation
6. Check GitHub Actions logs for CI failures

## Further Reading

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Laravel Testing](https://laravel.com/docs/testing)
- [Orchestra Testbench](https://packages.tools/testbench.html)

