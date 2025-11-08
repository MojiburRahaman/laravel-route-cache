<?php

namespace Mojiburrahaman\LaravelRouteCache\Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mojiburrahaman\LaravelRouteCache\Services\CacheValidator;
use Mojiburrahaman\LaravelRouteCache\Tests\TestCase;

class CacheValidatorTest extends TestCase
{
    protected CacheValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new CacheValidator();
    }

    /** @test */
    public function it_checks_if_caching_is_enabled()
    {
        config()->set('laravel-route-cache.enabled', true);
        $validator = new CacheValidator();
        $this->assertTrue($validator->isEnabled());

        config()->set('laravel-route-cache.enabled', false);
        $validator = new CacheValidator();
        $this->assertFalse($validator->isEnabled());
    }

    /** @test */
    public function it_allows_get_requests()
    {
        $request = Request::create('/api/posts', 'GET');
        $this->assertTrue($this->validator->isRequestCacheable($request));
    }

    /** @test */
    public function it_rejects_post_requests()
    {
        $request = Request::create('/api/posts', 'POST');
        $this->assertFalse($this->validator->isRequestCacheable($request));
    }

    /** @test */
    public function it_validates_success_responses()
    {
        $response = new Response('Success', 200);
        $this->assertTrue($this->validator->isResponseCacheable($response));
    }
}
