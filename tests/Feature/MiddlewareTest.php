<?php

namespace Mojiburrahaman\LaravelRouteCache\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Mojiburrahaman\LaravelRouteCache\Tests\TestCase;

class MiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('laravel-route-cache.enabled', true);
    }

    /** @test */
    public function it_caches_route_responses()
    {
        Route::get('/test', function () {
            return response('test');
        })->middleware('route.cache:3600');

        $response1 = $this->get('/test');
        $response1->assertStatus(200);

        $response2 = $this->get('/test');
        $response2->assertStatus(200);
        $this->assertEquals($response1->getContent(), $response2->getContent());
    }
}
