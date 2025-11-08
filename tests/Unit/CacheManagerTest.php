<?php

namespace Mojiburrahaman\LaravelRouteCache\Tests\Unit;

use Illuminate\Http\Response;
use Mojiburrahaman\LaravelRouteCache\Services\CacheManager;
use Mojiburrahaman\LaravelRouteCache\Tests\TestCase;

class CacheManagerTest extends TestCase
{
    protected CacheManager $cacheManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheManager = new CacheManager();
    }

    /** @test */
    public function it_can_store_and_retrieve_cache()
    {
        $response = new Response('Test content', 200);
        $key = 'test-key';

        $this->cacheManager->put($key, $response, 3600);
        $this->assertTrue($this->cacheManager->has($key));

        $cached = $this->cacheManager->get($key);
        $this->assertIsArray($cached);
        $this->assertEquals('Test content', $cached['content']);
    }

    /** @test */
    public function it_returns_null_for_missing_cache()
    {
        $result = $this->cacheManager->get('non-existent-key');
        $this->assertNull($result);
    }

    /** @test */
    public function it_can_check_if_key_exists()
    {
        $response = new Response('Test', 200);
        $key = 'exists-key';

        $this->assertFalse($this->cacheManager->has($key));
        $this->cacheManager->put($key, $response);
        $this->assertTrue($this->cacheManager->has($key));
    }

    /** @test */
    public function it_can_forget_cache_key()
    {
        $response = new Response('Test', 200);
        $key = 'forget-key';

        $this->cacheManager->put($key, $response);
        $this->assertTrue($this->cacheManager->has($key));

        $this->cacheManager->forget($key);
        $this->assertFalse($this->cacheManager->has($key));
    }

    /** @test */
    public function it_handles_locks_correctly()
    {
        $key = 'lock-key';

        $token = $this->cacheManager->acquireLock($key, 5);
        if ($token === null) {
            $this->markTestSkipped('Redis is not available to validate lock behaviour.');
        }

        $this->assertNotNull($token);
        $this->assertTrue($this->cacheManager->isLocked($key));

        $this->assertTrue($this->cacheManager->releaseLock($key, $token));
        $this->assertFalse($this->cacheManager->isLocked($key));
    }
}
