<?php

namespace Tests\Unit;

use App\Services\PodosoftAIService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PodosoftAIServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.python_ai.url' => 'http://localhost:8006']);
        config(['services.python_ai.timeout' => 30]);
        config(['services.python_ai.token' => null]);
    }

    public function test_is_configured_returns_true_when_url_set(): void
    {
        $svc = new PodosoftAIService();
        $this->assertTrue($svc->isConfigured());
    }

    public function test_is_configured_returns_false_when_url_empty(): void
    {
        config(['services.python_ai.url' => '']);
        $svc = new PodosoftAIService();
        $this->assertFalse($svc->isConfigured());
    }

    public function test_analyze_message_posts_to_process_endpoint_and_maps_response(): void
    {
        Http::fake([
            'localhost:8006/v1/agent/process' => Http::response([
                'reply' => '¡Hola! Soy la asistente virtual.',
                'intent' => 'GREETING',
                'action_taken' => 'PRE_LLM',
                'context_snapshot' => ['tenant_id' => 1, 'phone' => '573001234567'],
                'tool_calls' => [],
            ], 200),
        ]);

        $svc = new PodosoftAIService();
        $result = $svc->analyzeMessage('hola', [
            'telefono' => '573001234567',
            'tenant_id' => 1,
        ]);

        $this->assertSame('GREETING', $result['intent']);
        $this->assertSame('PRE_LLM', $result['action']);
        $this->assertSame('¡Hola! Soy la asistente virtual.', $result['message']);
        $this->assertSame('573001234567', $result['data']['phone']);
        $this->assertSame(1, $result['data']['tenant_id']);

        Http::assertSent(function ($request) {
            return $request['message'] === 'hola'
                && $request['tenant_id'] === 1
                && $request['phone'] === '573001234567';
        });
    }

    public function test_compose_from_system_result_uses_inline_system_info(): void
    {
        Http::fake([
            'localhost:8006/v1/agent/process' => Http::response([
                'reply' => 'Servicios: Podología, Quiropodia.',
                'intent' => 'GET_SERVICES',
                'action_taken' => 'LLM',
                'context_snapshot' => [],
                'tool_calls' => [],
            ], 200),
        ]);

        $svc = new PodosoftAIService();
        $result = $svc->composeFromSystemResult('Servicios: Podología', [
            'telefono' => '573001234567',
            'tenant_id' => 1,
        ]);

        $this->assertSame('GET_SERVICES', $result['intent']);
        $this->assertStringContainsString('[RESULTADO_SISTEMA]', Http::recorded()[0][0]->data()['message']);
    }

    public function test_returns_fallback_when_response_is_5xx(): void
    {
        Http::fake([
            'localhost:8006/*' => Http::response('boom', 500),
        ]);

        $svc = new PodosoftAIService();
        $result = $svc->analyzeMessage('hola', ['telefono' => '573', 'tenant_id' => 1]);

        $this->assertSame('UNKNOWN', $result['intent']);
        $this->assertSame('UNKNOWN', $result['action']);
        $this->assertStringContainsString('problema', $result['message']);
    }

    public function test_returns_fallback_when_url_not_configured(): void
    {
        config(['services.python_ai.url' => '']);
        $svc = new PodosoftAIService();
        $result = $svc->analyzeMessage('hola', []);

        $this->assertSame('UNKNOWN', $result['intent']);
        $this->assertStringContainsString('problema', $result['message']);
    }

    public function test_attaches_token_when_configured(): void
    {
        config(['services.python_ai.token' => 'secret-shared-token']);
        Http::fake([
            'localhost:8006/*' => Http::response([
                'reply' => 'ok', 'intent' => 'GREETING', 'action_taken' => 'PRE_LLM',
                'context_snapshot' => [], 'tool_calls' => [],
            ], 200),
        ]);

        $svc = new PodosoftAIService();
        $svc->analyzeMessage('hi', ['telefono' => '573', 'tenant_id' => 1]);

        Http::assertSent(fn($r) => $r->header('Authorization')[0] === 'Bearer secret-shared-token');
    }
}
