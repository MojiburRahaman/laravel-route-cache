<?php

namespace Mojiburrahaman\LaravelRouteCache\Tests\Unit;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Mojiburrahaman\LaravelRouteCache\Services\CacheManager;
use Mojiburrahaman\LaravelRouteCache\Services\CacheResponseBuilder;
use Mojiburrahaman\LaravelRouteCache\Tests\TestCase;

class CacheResponseBuilderTest extends TestCase
{
    protected CacheResponseBuilder $builder;
    protected CacheManager $cacheManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheManager = new CacheManager();
        $this->builder = new CacheResponseBuilder($this->cacheManager);
    }

    /** @test */
    public function it_builds_html_response_from_cache()
    {
        $cachedData = [
            'content' => '<h1>Hello World</h1>',
            'status' => 200,
            'headers' => ['content-type' => ['text/html']],
            'cached_at' => date('Y-m-d H:i:s'),
            'compressed' => false,
        ];

        $response = $this->builder->build($cachedData, 'test-key');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('<h1>Hello World</h1>', $response->getContent());
    }

    /** @test */
    public function it_builds_json_response_from_cache()
    {
        $data = ['name' => 'John', 'age' => 30];
        $cachedData = [
            'content' => json_encode($data),
            'status' => 200,
            'headers' => ['content-type' => ['application/json']],
            'cached_at' => date('Y-m-d H:i:s'),
            'compressed' => false,
        ];

        $response = $this->builder->build($cachedData, 'test-key');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($data, $response->getData(true));
    }
}
