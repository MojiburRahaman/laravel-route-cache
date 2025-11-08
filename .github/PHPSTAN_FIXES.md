# PHPStan Fixes Documentation

## Overview
This document outlines all the PHPStan level 5 static analysis errors that were identified and fixed in the Laravel Route Cache package.

## Fixes Applied

### 1. PHPStan Configuration (phpstan.neon.dist)

**Issue:** Deprecated configuration option `checkMissingIterableValueType`

**Fix:** Removed the deprecated option entirely. After fixing all type issues in the codebase, this ignore rule was no longer needed:
```yaml
ignoreErrors:
    - '#Unsafe usage of new static#'
```

**Note:** Initially tried replacing with `identifier: missingType.iterableValue`, but this caused an error because no such errors existed after the type fixes. PHPStan will error if you ignore patterns that don't match any actual errors.

---

### 2. Service Provider Return Type Mismatch

**File:** `src/LaravelRouteCacheServiceProvider.php`

#### Issue 2a: Missing Import
**Error:** `Access to constant DEFAULT_REDIS_PREFIX on an unknown class Mojiburrahaman\LaravelRouteCache\CacheConfig`

**Fix:** Added missing import statement:
```php
use Mojiburrahaman\LaravelRouteCache\Config\CacheConfig;
```

#### Issue 2b: Anonymous Function Return Type Mismatch (CacheKeyGenerator)
**Error:** `Anonymous function should return Mojiburrahaman\LaravelRouteCache\Services\CacheKeyGenerator but returns Mojiburrahaman\LaravelRouteCache\Contracts\CacheKeyGeneratorInterface`

**Fix:** Changed return type from concrete class to interface:
```php
// Before
$this->app->singleton(CacheKeyGenerator::class, function (Application $app): CacheKeyGenerator {
    return $app->make(CacheKeyGeneratorInterface::class);
});

// After
$this->app->singleton(CacheKeyGenerator::class, function (Application $app): CacheKeyGeneratorInterface {
    return $app->make(CacheKeyGeneratorInterface::class);
});
```

#### Issue 2c: Anonymous Function Return Type Mismatch (CacheManager)
**Error:** `Anonymous function should return Mojiburrahaman\LaravelRouteCache\Services\CacheManager but returns Mojiburrahaman\LaravelRouteCache\Contracts\CacheManagerInterface`

**Fix:** Changed return type from concrete class to interface:
```php
// Before
$this->app->singleton(CacheManager::class, function (Application $app): CacheManager {
    return $app->make(CacheManagerInterface::class);
});

// After
$this->app->singleton(CacheManager::class, function (Application $app): CacheManagerInterface {
    return $app->make(CacheManagerInterface::class);
});
```

**Rationale:** These are backward compatibility bindings. Since they resolve to interfaces, the return type should reflect that. The concrete implementations still fulfill the interface contract.

---

### 3. Middleware Type Safety

**File:** `src/Middleware/CacheResponse.php`

**Issue:** `Cannot access property $headers on class-string|object`

**Error Details:** The `addMissHeaders()` method accepted a `mixed` parameter without type checking before accessing the `headers` property.

**Fix:** 
1. Added Response import
2. Added explicit type check using `instanceof`
3. Added explicit type hint for parameter

```php
// Before
protected function addMissHeaders($response, Request $request): void
{
    if (!config('laravel-route-cache.add_cache_headers', true)) {
        return;
    }

    if (method_exists($response, 'headers')) {
        $response->headers->set(CacheConfig::HEADER_CACHE_STATUS, CacheStatus::MISS);
        $response->headers->set(CacheConfig::HEADER_CACHE_KEY, $this->keyGenerator->getReadableKey($request));
    }
}

// After
protected function addMissHeaders(mixed $response, Request $request): void
{
    if (!config('laravel-route-cache.add_cache_headers', true)) {
        return;
    }

    if ($response instanceof Response && method_exists($response, 'headers')) {
        $response->headers->set(CacheConfig::HEADER_CACHE_STATUS, CacheStatus::MISS);
        $response->headers->set(CacheConfig::HEADER_CACHE_KEY, $this->keyGenerator->getReadableKey($request));
    }
}
```

---

### 4. Response Builder Type Safety

**File:** `src/Services/CacheResponseBuilder.php`

**Issue:** `Cannot access property $headers on class-string|object` (two occurrences)

**Error Details:** Methods `setHeaders()` and `addCacheMetadata()` accepted untyped parameters that access `headers` property.

**Fix:** Added explicit union type hints using PHP 8.0+ union types:

#### Issue 4a: setHeaders() method
```php
// Before
protected function setHeaders($response, array $cachedData): void

// After
protected function setHeaders(Response|JsonResponse $response, array $cachedData): void
```

#### Issue 4b: addCacheMetadata() method
```php
// Before
protected function addCacheMetadata($response, array $cachedData, string $cacheKey): void

// After
protected function addCacheMetadata(Response|JsonResponse $response, array $cachedData, string $cacheKey): void
```

---

## Benefits

### Type Safety
- All methods now have explicit parameter and return types
- PHPStan can verify type correctness at development time
- Reduces runtime errors from type mismatches

### Code Quality
- Better IDE autocomplete support
- Self-documenting code through type hints
- Easier refactoring with confidence

### Standards Compliance
- Follows modern PHP 8+ best practices
- Uses union types where appropriate
- Explicit about interface vs implementation dependencies

---

## Testing PHPStan

Run PHPStan analysis with:
```bash
vendor/bin/phpstan analyse --error-format=github
```

Expected output:
```
[OK] No errors
```

## PHP Version Requirements

These fixes use PHP 8.0+ features:
- Union types (`Response|JsonResponse`)
- Mixed type hint
- Modern type system

All changes are compatible with the package's minimum PHP requirement of 8.0.

---

## Future Improvements

Consider for future releases:
1. Increase PHPStan level to 6 or higher for stricter analysis
2. Add PHPStan Strict Rules extension
3. Consider adding PHPDoc templates for generic types
4. Add baseline for any remaining edge cases

---

## References

- [PHPStan Documentation](https://phpstan.org/)
- [Larastan Documentation](https://github.com/larastan/larastan)
- [PHP 8.0 Union Types](https://www.php.net/manual/en/language.types.declarations.php#language.types.declarations.union)

