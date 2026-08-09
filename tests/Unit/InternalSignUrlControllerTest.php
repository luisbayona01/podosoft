<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\InternalSignUrlController;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class InternalSignUrlControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_returns_422_for_invalid_route(): void
    {
        $controller = new InternalSignUrlController(app(\App\Services\ShortLinkService::class));
        $request = Request::create('/api/v1/internal/sign-url', 'POST', [
            'route' => 'admin.delete_everything',
            'tenant_slug' => 'clinica-y',
        ]);

        $response = $controller($request);
        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_returns_422_for_missing_route(): void
    {
        $controller = new InternalSignUrlController(app(\App\Services\ShortLinkService::class));
        $request = Request::create('/api/v1/internal/sign-url', 'POST', [
            'tenant_slug' => 'clinica-y',
        ]);

        $response = $controller($request);
        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_validates_tenant_slug_with_max_64_chars(): void
    {
        $controller = new InternalSignUrlController(app(\App\Services\ShortLinkService::class));
        $request = Request::create('/api/v1/internal/sign-url', 'POST', [
            'route' => 'public.appointment',
            'tenant_slug' => str_repeat('a', 65),
        ]);

        $response = $controller($request);
        $this->assertSame(422, $response->getStatusCode());
    }
}
