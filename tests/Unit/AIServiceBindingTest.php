<?php

namespace Tests\Unit;

use App\Services\AIServiceInterface;
use App\Services\PodosoftAIService;
use Tests\TestCase;

class AIServiceBindingTest extends TestCase
{
    public function test_resolves_podosoft_ai_when_url_configured(): void
    {
        config(['services.python_ai.url' => 'http://localhost:8006']);

        $ai = app(AIServiceInterface::class);

        $this->assertInstanceOf(PodosoftAIService::class, $ai);
        $this->assertTrue($ai->isConfigured());
    }

    public function test_is_configured_returns_true_when_url_present(): void
    {
        config(['services.python_ai.url' => 'http://localhost:8006']);

        $ai = new PodosoftAIService();

        $this->assertTrue($ai->isConfigured());
    }

    public function test_is_configured_returns_false_when_url_empty(): void
    {
        config(['services.python_ai.url' => '']);

        $ai = new PodosoftAIService();

        $this->assertFalse($ai->isConfigured());
    }
}
