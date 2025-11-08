<?php

namespace Mojiburrahaman\LaravelRouteCache\Tests\Feature;

use Illuminate\Http\Response;
use Mojiburrahaman\LaravelRouteCache\Facades\RouteCache;
use Mojiburrahaman\LaravelRouteCache\Tests\TestCase;

class FacadeTest extends TestCase
{
    /** @test */
    public function it_can_manually_cache_data()
    {
        $response = new Response('Test content', 200);
        $key = 'test-key';

        RouteCache::put($key, $response, 3600);
        $this->assertTrue(RouteCache::has($key));
    }

    /** @test */
    public function it_can_retrieve_cached_data()
    {
        $response = new Response('Test content', 200);
        $key = 'retrieve-key';

        RouteCache::put($key, $response);
        $cached = RouteCache::get($key);

        $this->assertIsArray($cached);
        $this->assertEquals('Test content', $cached['content']);
    }

    /** @test */
    public function it_can_forget_cache_keys()
    {
        $response = new Response('Test', 200);
        $key = 'forget-key';

        RouteCache::put($key, $response);
        $this->assertTrue(RouteCache::has($key));

        RouteCache::forget($key);
        $this->assertFalse(RouteCache::has($key));
    }
}
