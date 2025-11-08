<?php

declare(strict_types=1);

namespace Mojiburrahaman\LaravelRouteCache\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Mojiburrahaman\LaravelRouteCache\Config\CacheConfig;
use Mojiburrahaman\LaravelRouteCache\Contracts\CacheManagerInterface;

/**
 * Manages Redis cache operations for route responses
 *
 * @package Mojiburrahaman\LaravelRouteCache\Services
 */
class CacheManager implements CacheManagerInterface
{
    private const LOCK_PREFIX = 'lock:';
    /**
     * @var string
     */
    protected string $connection;

    /**
     * @var mixed
     */
    protected $redisConnection;

    public function __construct()
    {
        $this->connection = config('laravel-route-cache.redis_connection', CacheConfig::DEFAULT_CONNECTION);
    }

    /**
     * @return mixed
     */
    protected function redis()
    {
        if ($this->redisConnection === null) {
            $this->redisConnection = Redis::connection($this->connection);
        }

        return $this->redisConnection;
    }

    /**
     * Generate a hashed cache key from plain text
     * Users can now pass plain text keys and the system handles hashing
     *
     * If the key doesn't start with an HTTP method, "GET:" is prepended automatically
     * This makes it easier for users - they can just pass "/path" instead of "GET:/path"
     *
     * @param string $key Plain text cache key (e.g., "/", "/api/users", or "GET:/api/users")
     * @return string Hashed cache key
     */
    protected function hashKey(string $key): string
    {
        $prefix = config('laravel-route-cache.cache_key_prefix', CacheConfig::DEFAULT_KEY_PREFIX);

        // Auto-prepend "GET:" if no HTTP method is specified
        // This makes the API more user-friendly for the common case
        if (! preg_match('/^(GET|POST|PUT|PATCH|DELETE|HEAD|OPTIONS):/i', $key)) {
            $key = 'GET:' . $key;
        }

        return md5($prefix . ':' . $key);
    }

    /**
     * Generate the Redis key used for cache locks.
     *
     * @param string $key
     * @return string
     */
    protected function lockKey(string $key): string
    {
        return self::LOCK_PREFIX . $this->hashKey($key);
    }

    /**
     * Get the hashed version of a plain text key (for debugging)
     * This allows users to see what the actual Redis key will be
     *
     * @param string $key Plain text cache key
     * @return string Hashed cache key
     */
    public function getHashedKey(string $key): string
    {
        return $this->hashKey($key);
    }

    /**
     * Retrieve cached data using plain text key
     * The key will be automatically hashed internally
     *
     * @param string $key Plain text cache key
     * @return array<string, mixed>|null
     */
    public function get(string $key): ?array
    {
        try {
            $hashedKey = $this->hashKey($key);
            $cached = $this->redis()->get($hashedKey);

            if ($cached) {
                $decoded = json_decode($cached, true);

                if (! is_array($decoded)) {
                    return null;
                }

                if (isset($decoded['compressed']) && $decoded['compressed']) {
                    $decompressed = @gzuncompress(base64_decode($decoded['content']));
                    if ($decompressed !== false) {
                        $decoded['content'] = $decompressed;
                    }
                }

                return $decoded;
            }
        } catch (\Exception $e) {
            Log::error('RouteCache get failed', ['key' => $key, 'error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Store response in cache using plain text key
     * The key will be automatically hashed internally
     *
     * @param string $key Plain text cache key
     * @param Response|JsonResponse $response
     * @param int|null $ttl
     * @return bool
     */
    public function put(string $key, $response, ?int $ttl = null): bool
    {
        try {
            $ttl = $ttl ?? config('laravel-route-cache.default_ttl', CacheConfig::DEFAULT_TTL);

            if ($ttl !== null && $ttl < 0) {
                return false;
            }

            $content = $response->getContent();
            $contentSize = strlen($content);
            $compressionThreshold = config('laravel-route-cache.compression_threshold', 1024);
            $shouldCompress = $contentSize > $compressionThreshold;

            $cacheData = [
                'content' => $shouldCompress ? base64_encode(gzcompress($content, 6)) : $content,
                'status' => $response->getStatusCode(),
                'headers' => $this->filterHeaders($response->headers->all()),
                'cached_at' => date('Y-m-d H:i:s'),
                'compressed' => $shouldCompress,
            ];

            $serialized = json_encode($cacheData);
            $hashedKey = $this->hashKey($key);

            if ($ttl !== null && $ttl > 0) {
                $this->redis()->setex($hashedKey, $ttl, $serialized);
            } else {
                $this->redis()->set($hashedKey, $serialized);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('RouteCache put failed', ['key' => $key, 'error' => $e->getMessage()]);
        }

        return false;
    }

    /**
     * Check if cache key exists using plain text key
     * The key will be automatically hashed internally
     *
     * @param string $key Plain text cache key
     * @return bool
     */
    public function has(string $key): bool
    {
        try {
            $hashedKey = $this->hashKey($key);

            return (bool) $this->redis()->exists($hashedKey);
        } catch (\Exception $e) {
            Log::error('RouteCache has failed', ['key' => $key, 'error' => $e->getMessage()]);
        }

        return false;
    }

    /**
     * Delete cache entry using plain text key
     * The key will be automatically hashed internally
     *
     * @param string $key Plain text cache key
     * @return bool
     */
    public function forget(string $key): bool
    {
        try {
            $hashedKey = $this->hashKey($key);
            $result = $this->redis()->del($hashedKey);

            return $result > 0;
        } catch (\Exception $e) {
            Log::error('RouteCache forget failed', ['key' => $key, 'error' => $e->getMessage()]);
        }

        return false;
    }

    /**
     * Flush all cache entries
     *
     * @return bool
     */
    public function flush(): bool
    {
        try {
            $redis = $this->redis();
            $keys = $redis->keys('*');

            if (! empty($keys) && is_array($keys)) {
                // Delete keys in batch for better performance
                $redis->del(...$keys);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('RouteCache flush failed', ['error' => $e->getMessage()]);
        }

        return false;
    }

    /**
     * Get remaining TTL using plain text key
     * The key will be automatically hashed internally
     *
     * @param string $key Plain text cache key
     * @return int|null
     */
    public function ttl(string $key): ?int
    {
        try {
            $hashedKey = $this->hashKey($key);
            $ttl = $this->redis()->ttl($hashedKey);

            return ($ttl >= 0) ? (int) $ttl : null;
        } catch (\Exception $e) {
            Log::error('RouteCache ttl failed', ['key' => $key, 'error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $headers
     * @return array<string, mixed>
     */
    protected function filterHeaders(array $headers): array
    {
        $result = [];
        $excluded = CacheConfig::EXCLUDED_HEADERS;

        foreach ($headers as $key => $value) {
            if (! in_array(strtolower($key), $excluded, true)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function acquireLock(string $key, int $ttl): ?string
    {
        try {
            $token = bin2hex(random_bytes(16));
            $lockKey = $this->lockKey($key);
            $result = $this->redis()->set($lockKey, $token, 'EX', $ttl, 'NX');

            if ($result === true || $result === 'OK') {
                return $token;
            }
        } catch (\Exception $e) {
            Log::warning('RouteCache lock acquire failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function releaseLock(string $key, string $token): bool
    {
        try {
            $lockKey = $this->lockKey($key);
            $client = $this->getRedisClient();

            if (! method_exists($client, 'watch')) {
                // Fallback: best-effort delete without atomic guarantee
                $currentValue = $this->redis()->get($lockKey);
                if ($currentValue === $token) {
                    return (bool) $this->redis()->del($lockKey);
                }

                return false;
            }

            $client->watch($lockKey);
            $current = $client->get($lockKey);

            if ($current !== $token) {
                $client->unwatch();

                return false;
            }

            $client->multi();
            $client->del($lockKey);
            $result = $client->exec();

            if ($result === false) {
                return false;
            }

            if (is_array($result)) {
                $deleted = (int) ($result[0] ?? 0);
            } else {
                $deleted = (int) $result;
            }

            return $deleted === 1;
        } catch (\Exception $e) {
            Log::warning('RouteCache lock release failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function isLocked(string $key): bool
    {
        try {
            return (bool) $this->redis()->exists($this->lockKey($key));
        } catch (\Exception $e) {
            Log::warning('RouteCache lock status failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Get underlying Redis client instance.
     *
     * @return mixed
     */
    protected function getRedisClient()
    {
        $connection = $this->redis();

        if (is_object($connection) && method_exists($connection, 'client')) {
            return $connection->client();
        }

        return $connection;
    }

    /**
     * @return mixed
     */
    public function getRedisConnection()
    {
        return $this->redis();
    }
}
