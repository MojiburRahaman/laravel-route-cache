<?php

namespace Mojiburrahaman\LaravelRouteCache\Tests\Unit;

use Illuminate\Http\Request;
use Mojiburrahaman\LaravelRouteCache\Services\CacheKeyGenerator;
use Mojiburrahaman\LaravelRouteCache\Tests\TestCase;

class CacheKeyGeneratorTest extends TestCase
{
    protected CacheKeyGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new CacheKeyGenerator();
    }

    /** @test */
    public function it_generates_cache_key_for_simple_request()
    {
        $request = Request::create('/api/posts', 'GET');
        $key = $this->generator->generate($request);

        $this->assertIsString($key);
        $this->assertStringContainsString('GET', $key);
        $this->assertStringContainsString('api/posts', $key);
    }

    /** @test */
    public function it_generates_consistent_keys_for_same_request()
    {
        $request = Request::create('/api/posts', 'GET');
        $key1 = $this->generator->generate($request);
        $key2 = $this->generator->generate($request);

        $this->assertEquals($key1, $key2);
    }
}
