<?php

declare(strict_types=1);

namespace Mojiburrahaman\LaravelRouteCache\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Mojiburrahaman\LaravelRouteCache\Config\CacheConfig;
use Mojiburrahaman\LaravelRouteCache\Contracts\CacheManagerInterface;
use Mojiburrahaman\LaravelRouteCache\Enums\CacheStatus;

/**
 * Builds HTTP responses from cached data
 *
 * @package Mojiburrahaman\LaravelRouteCache\Services
 */
class CacheResponseBuilder
{
    protected CacheManagerInterface $cacheManager;

    /**
     * @param CacheManagerInterface $cacheManager
     */
    public function __construct(CacheManagerInterface $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * Create a response from cached data
     *
     * @param array<string, mixed> $cachedData
     * @param string $cacheKey
     * @return Response|JsonResponse
     */
    /**
     * @param array<string, mixed> $cachedData
     * @param string $cacheKey
     * @return Response|JsonResponse
     */
    public function build(array $cachedData, string $cacheKey)
    {
        $response = $this->createBaseResponse($cachedData);
        $this->setHeaders($response, $cachedData);
        $this->addCacheMetadata($response, $cachedData, $cacheKey);

        return $response;
    }

    /**
     * Create base response with content
     *
     * @param array<string, mixed> $cachedData
     * @return Response|JsonResponse
     */
    /**
     * @param array<string, mixed> $cachedData
     * @return Response|JsonResponse
     */
    protected function createBaseResponse(array $cachedData)
    {
        $content = $cachedData['content'] ?? '';
        $status = $cachedData['status'] ?? 200;
        $headers = $cachedData['headers'] ?? [];

        // Handle decompression if content was compressed
        if (isset($cachedData['compressed']) && $cachedData['compressed']) {
            $decompressed = @gzuncompress(base64_decode($content));
            if ($decompressed !== false) {
                $content = $decompressed;
            }
        }

        $isJson = $this->isJsonResponse($headers);

        if ($isJson) {
            return new JsonResponse(json_decode($content, true), $status);
        }

        return new Response($content, $status);
    }

    /**
     * Set cached headers on response
     *
     * @param Response|JsonResponse $response
     * @param array<string, mixed> $cachedData
     * @return void
     */
    /**
     * @param Response|JsonResponse $response
     * @param array<string, mixed> $cachedData
     * @return void
     */
    protected function setHeaders($response, array $cachedData): void
    {
        $headers = $cachedData['headers'] ?? [];

        foreach ($headers as $key => $value) {
            if (is_array($value)) {
                $value = $value[0] ?? '';
            }
            $response->headers->set($key, $value);
        }
    }

    /**
     * Add cache metadata headers
     *
     * @param Response|JsonResponse $response
     * @param array<string, mixed> $cachedData
     * @param string $cacheKey
     * @return void
     */
    /**
     * @param Response|JsonResponse $response
     * @param array<string, mixed> $cachedData
     * @param string $cacheKey
     * @return void
     */
    protected function addCacheMetadata($response, array $cachedData, string $cacheKey): void
    {
        if (! config(CacheConfig::CONFIG_ADD_CACHE_HEADERS, true)) {
            return;
        }

        $response->headers->set(CacheConfig::HEADER_CACHE_STATUS, CacheStatus::HIT);
        $response->headers->set(CacheConfig::HEADER_CACHED_AT, $cachedData['cached_at'] ?? '');
        $response->headers->set(CacheConfig::HEADER_CACHE_KEY, $cacheKey);

        $ttl = $this->cacheManager->ttl($cacheKey);
        if ($ttl !== null) {
            $response->headers->set(CacheConfig::HEADER_CACHE_TTL, (string) $ttl);
        }
    }

    /**
     * Check if response is JSON based on headers
     *
     * @param array<string, mixed> $headers
     * @return bool
     */
    protected function isJsonResponse(array $headers): bool
    {
        if (! isset($headers['content-type'])) {
            return false;
        }

        $contentType = is_array($headers['content-type'])
            ? ($headers['content-type'][0] ?? '')
            : $headers['content-type'];

        return strpos($contentType, 'application/json') !== false;
    }
}
