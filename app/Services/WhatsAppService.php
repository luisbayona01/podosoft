<?php

namespace App\Services;

use App\Models\TenantWhatsAppAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WhatsAppService
{
    protected ?TenantWhatsAppAccount $account = null;

    public function forTenant(int $tenantId): self
    {
        $service = clone $this;
        $service->account = TenantWhatsAppAccount::where('tenant_id', $tenantId)
            ->where('status', 'connected')
            ->first();
        return $service;
    }

    public function forAccount(TenantWhatsAppAccount $account): self
    {
        $service = clone $this;
        $service->account = $account;
        return $service;
    }

    public function sendText(string $number, string $text): bool
    {
        if (!$this->account) {
            Log::error('[WhatsAppService] No WhatsApp account configured for tenant');
            return false;
        }

        Log::info('[AUDIT-4] Sending text via WhatsApp', [
            'number' => $number,
            'text' => $text,
            'instance' => $this->account->instance_name,
        ]);

        $text = str_replace("\0", "", $text);

        try {
            $this->sendPresence($number, $text);

            $url = $this->account->server_url ?? config('services.evolution.url');
            $apiKey = $this->account->api_key ?? config('services.evolution.key');

            $url = "{$url}/message/sendText/{$this->account->instance_name}";

            $response = Http::withHeaders([
                'apikey' => $apiKey,
            ])->withOptions([
                'verify' => false,
            ])->post($url, [
                'number' => $number,
                'text' => $text,
            ]);

            Log::info('[AUDIT-4] Evolution API response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->failed()) {
                Log::error("Evolution API Error: " . $response->body());
                return false;
            }

            $this->account->update(['last_seen_at' => now()]);

            return true;
        } catch (Exception $e) {
            Log::error("WhatsAppService Error: " . $e->getMessage());
            return false;
        }
    }

    public function sendInteractiveButtons(string $number, string $text, string $buttonText, string $buttonId, ?string $url = null): bool
    {
        if (!$this->account) {
            Log::error('[WhatsAppService] No WhatsApp account configured for tenant');
            return false;
        }

        Log::info('[AUDIT-4] Sending interactive buttons via WhatsApp', [
            'number' => $number,
            'buttonText' => $buttonText,
            'instance' => $this->account->instance_name,
        ]);

        $text = str_replace("\0", "", $text);

        if ($url) {
            $text .= "\n\n{$url}";
        }

        try {
            $this->sendPresence($number, $text);

            $urlEndpoint = $this->account->server_url ?? config('services.evolution.url');
            $apiKey = $this->account->api_key ?? config('services.evolution.key');

            $urlEndpoint = "{$urlEndpoint}/message/sendInteractive/{$this->account->instance_name}";

            $response = Http::withHeaders([
                'apikey' => $apiKey,
            ])->withOptions([
                'verify' => false,
            ])->post($urlEndpoint, [
                'number' => $number,
                'interactive' => [
                    'type' => 'buttons',
                    'body' => [
                        'text' => $text,
                    ],
                    'footer' => [
                        'text' => 'Este enlace es seguro y expirará automáticamente.',
                    ],
                    'buttons' => [
                        [
                            'type' => 'reply',
                            'reply' => [
                                'title' => $buttonText,
                                'id' => $buttonId,
                            ],
                        ],
                    ],
                ],
            ]);

            Log::info('[AUDIT-4] Evolution API interactive response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->failed()) {
                Log::error("Evolution API Interactive Error: " . $response->body());
                return false;
            }

            $this->account->update(['last_seen_at' => now()]);

            return true;
        } catch (Exception $e) {
            Log::error("WhatsAppService Interactive Error: " . $e->getMessage());
            return false;
        }
    }

    protected function sendPresence(string $number, string $text): void
    {
        $textLength = mb_strlen($text);
        $delay = min(10000, max(1000, (int) ($textLength * 50)));

        Log::info('[EVOLUTION] Sending composing presence', [
            'instance' => $this->account->instance_name,
            'number' => $number,
            'delay' => $delay,
        ]);

        try {
            $url = $this->account->server_url ?? config('services.evolution.url');
            $apiKey = $this->account->api_key ?? config('services.evolution.key');

            $url = "{$url}/chat/sendPresence/{$this->account->instance_name}";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'apikey' => $apiKey,
            ])->withOptions([
                'verify' => false,
            ])->post($url, [
                'number' => $number,
                'delay' => $delay,
                'presence' => 'composing',
            ]);

            if ($response->failed()) {
                Log::warning('[EVOLUTION] Presence failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return;
            }

            Log::info('[EVOLUTION] Presence sent');
        } catch (Exception $e) {
            Log::warning('[EVOLUTION] Presence failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getAccount(): ?TenantWhatsAppAccount
    {
        return $this->account;
    }
}
