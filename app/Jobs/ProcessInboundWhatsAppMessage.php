<?php

namespace App\Jobs;

use App\Services\EvolutionApiService;
use App\Services\PodosoftAIService;
use App\Models\ConversacionIA;
use App\Models\TenantWhatsAppAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessInboundWhatsAppMessage implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 240;

    public function __construct(
        public string $instanceName,
        public string $phone,
        public string $message,
        public int $tenantId,
        public string $tenantSlug = '',
    ) {}

    /**
     * Process the inbound message through the AI agent and reply over WhatsApp.
     *
     * This runs on a queue worker (async), so the webhook thread returns
     * immediately and never blocks the by-phone callback from the Python
     * service (no single-threaded deadlock).
     */
    public function handle(): void
    {
        Log::info('[AgentJob] Start', [
            'instance' => $this->instanceName,
            'phone' => $this->phone,
            'tenant_id' => $this->tenantId,
            'message' => $this->message,
        ]);

        $aiService = app(\App\Services\AIServiceInterface::class);

        if ($aiService instanceof PodosoftAIService && $aiService->isConfigured()) {
            $this->migrateLegacyConversationIfNeeded($this->phone, $this->tenantId);

            Log::info('[AgentJob] Routing to PodosoftAI (Python)', [
                'phone' => $this->phone,
                'tenant_id' => $this->tenantId,
                'tenant_slug' => $this->tenantSlug,
            ]);
        } else {
            Log::info('[AgentJob] Routing to legacy AgentService (MySQL state)', [
                'phone' => $this->phone,
                'tenant_id' => $this->tenantId,
            ]);
        }

        $result = $aiService->analyzeMessage($this->message, [
            'phone' => $this->phone,
            'telefono' => $this->phone,
            'tenant_id' => $this->tenantId,
            'tenant_slug' => $this->tenantSlug,
        ], []);

        $reply = $result['message'] ?? '';
        if ($reply === '') {
            Log::warning('[AgentJob] Empty agent reply', [
                'phone' => $this->phone,
                'tenant_id' => $this->tenantId,
            ]);
            return;
        }

        Log::info('[AgentJob] Agent reply generated', [
            'phone' => $this->phone,
            'tenant_id' => $this->tenantId,
            'reply' => $reply,
        ]);

        $account = TenantWhatsAppAccount::where('instance_name', $this->instanceName)->first();
        if (!$account) {
            Log::warning('[AgentJob] Account not found, cannot send reply', [
                'instance' => $this->instanceName,
            ]);
            return;
        }

        try {
            $evolutionApi = app(EvolutionApiService::class)->fromAccount($account);
            $evolutionApi->sendText($this->phone, $reply);
            Log::info('[AgentJob] Reply sent', [
                'instance' => $this->instanceName,
                'phone' => $this->phone,
                'reply' => $reply,
            ]);
        } catch (\Exception $e) {
            Log::error('[AgentJob] Failed to send reply', [
                'instance' => $this->instanceName,
                'phone' => $this->phone,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * One-shot migration: if there is an active conversation in MySQL for
     * this (phone, tenant_id) that hasn't been flagged migrated yet, close it.
     * Redis (Python) starts fresh; no state is copied because the legacy
     * schema is incompatible with Python's ConversationContext.
     */
    private function migrateLegacyConversationIfNeeded(string $phone, int $tenantId): void
    {
        $conv = ConversacionIA::where('telefono', $phone)
            ->where('tenant_id', $tenantId)
            ->where('migrated_to_python', false)
            ->where('estado', '!=', 'cerrada')
            ->first();

        if (!$conv) {
            return;
        }

        $conv->update([
            'estado' => 'cerrada',
            'migrated_to_python' => true,
            'metadata' => null,
            'ultima_interaccion' => now(),
        ]);

        Log::warning('[AgentJob] Legacy conversation migrated to Python', [
            'phone' => $phone,
            'tenant_id' => $tenantId,
            'conversation_id' => $conv->id,
        ]);
    }
}
