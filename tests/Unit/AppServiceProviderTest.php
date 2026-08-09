<?php

namespace Tests\Unit;

use App\Services\AIServiceInterface;
use App\Services\PodosoftAIService;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    public function test_resolves_podosoft_aiservice_when_python_url_set(): void
    {
        config(['services.python_ai.url' => 'http://localhost:8006']);
        $svc = app(AIServiceInterface::class);
        $this->assertInstanceOf(PodosoftAIService::class, $svc);
        $this->assertTrue($svc->isConfigured());
    }

    public function test_resolves_legacy_aiservice_when_python_url_not_set(): void
    {
        config(['services.python_ai.url' => '']);
        $svc = app(AIServiceInterface::class);
        $this->assertNotInstanceOf(PodosoftAIService::class, $svc);
    }
}

