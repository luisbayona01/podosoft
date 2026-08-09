<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Thin HTTP client for the Podosoft AI microservice (Python FastAPI).
 *
 * Exposes the same public signatures as AIService so AgentService can use
 * either implementation interchangeably. When PYTHON_AI_URL is unset, the
 * legacy AIService is used instead.
 *
 * Contract with the Python service (POST /v1/agent/process):
 *   request:  {"phone": str, "message": str, "tenant_id": int}
 *   response: {"reply": str, "intent": str, "action_taken": str,
 *              "context_snapshot": {...}, "tool_calls": [...]}
 *
 * Maps to Laravel's {intent, action, message, data} by:
 *   intent   ← response.intent
 *   action   ← response.action_taken  (Python already decides the action)
 *   message  ← response.reply
 *   data     ← {phone: ..., tenant_id: ..., tool_calls: ...}
 */
class PodosoftAIService implements AIServiceInterface
{
    private string $baseUrl;
    private int $timeout;
    private ?string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.python_ai.url'), '/');
        $this->timeout = (int) config('services.python_ai.timeout', 30);
        $this->token = config('services.python_ai.token') ?: null;
    }

    /**
     * Returns true if the Python AI service is configured (URL set).
     */
    public function isConfigured(): bool
    {
        return $this->baseUrl !== '';
    }

    /**
     * Same signature as AIService::analyzeMessage().
     * Calls POST /v1/agent/process on the Python service.
     */
    public function analyzeMessage(string $message, array $context = [], array $history = []): array
    {
        return $this->callProcess($message, $context);
    }

    /**
     * Same signature as AIService::composeFromSystemResult().
     * The system result is passed inline as part of the message so the agent
     * can rephrase it in the user's voice.
     */
    public function composeFromSystemResult(string $systemInfo, array $context = [], array $history = []): array
    {
        $message = "[RESULTADO_SISTEMA] {$systemInfo}\nRedacta la respuesta al usuario basándote en esta información del sistema.";
        return $this->callProcess($message, $context);
    }

    /**
     * Performs POST /v1/agent/process and maps the response back to Laravel's
     * shape {intent, action, message, data}.
     */
    private function callProcess(string $message, array $context): array
    {
        if (!$this->isConfigured()) {
            return $this->fallback('PYTHON_AI_URL not configured');
        }

        $payload = [
            'phone'       => $context['telefono'] ?? $context['phone'] ?? '',
            'message'     => $message,
            'tenant_id'   => $context['tenant_id'] ?? 0,
            'tenant_slug' => $context['tenant_slug'] ?? '',
        ];

        Log::info('[PodosoftAI] → REQUEST al microservicio Python', [
            'url'    => "{$this->baseUrl}/v1/agent/process",
            'timeout' => $this->timeout,
            'payload' => $payload,
        ]);

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->asJson()
                ->when($this->token, fn($h) => $h->withToken($this->token))
                ->post("{$this->baseUrl}/v1/agent/process", $payload);

            Log::info('[PodosoftAI] ← RESPONSE del microservicio Python', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            if ($response->failed()) {
                Log::error('[PodosoftAI] Non-2xx response', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return $this->fallback("HTTP {$response->status()} from AI service");
            }

            $body = $response->json();
            Log::info('[PodosoftAI] ✓ RESPUESTA MAPEADA', [
                'intent'  => $body['intent'] ?? 'UNKNOWN',
                'action'  => $body['action_taken'] ?? 'UNKNOWN',
                'reply'   => $body['reply'] ?? '',
                'tool_calls_count' => count($body['tool_calls'] ?? []),
            ]);
            return $this->mapResponse($body, $context);

        } catch (Exception $e) {
            Log::error('[PodosoftAI] Request failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->fallback($e->getMessage());
        }
    }

    /**
     * Translate the Python service response into Laravel's expected shape.
     */
    private function mapResponse(array $body, array $context): array
    {
        return [
            'intent'  => $body['intent'] ?? 'UNKNOWN',
            'action'  => $body['action_taken'] ?? 'UNKNOWN',
            'message' => $body['reply'] ?? '',
            'data'    => [
                'phone'      => $context['telefono'] ?? $context['phone'] ?? '',
                'tenant_id'  => $context['tenant_id'] ?? 0,
                'tool_calls' => $body['tool_calls'] ?? [],
                'snapshot'   => $body['context_snapshot'] ?? [],
            ],
        ];
    }

    private function fallback(string $reason): array
    {
        Log::warning('[PodosoftAI] Falling back: ' . $reason);
        return [
            'intent'  => 'UNKNOWN',
            'action'  => 'UNKNOWN',
            'message' => 'Lo siento, tuve un problema procesando tu mensaje. ¿Podrías repetirlo?',
            'data'    => [],
        ];
    }
}
