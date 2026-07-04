<?php

namespace App\Services;

use App\Models\TenantWhatsAppAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class EvolutionApiService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $instanceName;
    protected bool $verify;

    public function __construct()
    {
        $this->baseUrl = config('services.evolution.url', 'https://evoltionapi.devsoftai.com');
        $this->apiKey = config('services.evolution.key', '');
        $this->verify = config('services.evolution.verify', false);
    }

    public function setInstance(string $instanceName): self
    {
        $this->instanceName = $instanceName;
        return $this;
    }

    public function setCredentials(string $baseUrl, string $apiKey): self
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        return $this;
    }

    public function fromAccount(TenantWhatsAppAccount $account): self
    {
        $this->instanceName = $account->instance_name;
        if ($account->server_url) {
            $this->baseUrl = $account->server_url;
        }
        if ($account->api_key) {
            $this->apiKey = $account->api_key;
        }
        return $this;
    }

    public function createInstance(string $instanceName, ?string $webhookUrl = null): array
    {
        Log::info('[EvolutionApiService] Creating instance', ['instance' => $instanceName]);

        try {
            $payload = [
                'instanceName' => $instanceName,
                'qrcode' => true,
                'integration' => 'WHATSAPP-BAILEYS',
            ];

            $response = $this->post('/instance/create', $payload);

            Log::info('[EvolutionApiService] Instance created', ['response' => $response]);

            if ($webhookUrl) {
                $this->setWebhook($instanceName, $webhookUrl);
            }

            return [
                'success' => true,
                'instance' => $response,
            ];
        } catch (Exception $e) {
            Log::error('[EvolutionApiService] Error creating instance', [
                'error' => $e->getMessage(),
                'instance' => $instanceName,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function connect(string $instanceName): array
    {
        Log::info('[EvolutionApiService] Connecting instance', ['instance' => $instanceName]);

        try {
            $response = $this->get("/instance/connect/{$instanceName}", true);

            Log::info('[EvolutionApiService] Connect response', ['response' => $response]);

            if (isset($response['base64']) && $response['base64']) {
                return [
                    'success' => true,
                    'qrcode' => $response['code'] ?? null,
                    'base64' => $response['base64'],
                    'count' => $response['count'] ?? 1,
                ];
            }

            return [
                'success' => true,
                'data' => $response,
            ];
        } catch (Exception $e) {
            Log::error('[EvolutionApiService] Error connecting instance', [
                'error' => $e->getMessage(),
                'instance' => $instanceName,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getQRCode(string $instanceName): array
    {
        Log::info('[EvolutionApiService] Getting QR code', ['instance' => $instanceName]);

        try {
            $response = $this->get("/instance/qrcode/{$instanceName}", true);

            Log::info('[EvolutionApiService] QR code response', ['response' => $response]);

            if (isset($response['base64']) && $response['base64']) {
                return [
                    'success' => true,
                    'qrcode' => $response['code'] ?? null,
                    'base64' => $response['base64'],
                    'count' => $response['count'] ?? 1,
                ];
            }

            return [
                'success' => true,
                'qrcode' => $response['code'] ?? $response['qrcode'] ?? null,
                'base64' => $response['base64'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('[EvolutionApiService] Error getting QR code', [
                'error' => $e->getMessage(),
                'instance' => $instanceName,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getStatus(string $instanceName): array
    {
        try {
            $response = $this->get("/instance/connectionState/{$instanceName}", true);

            return [
                'success' => true,
                'status' => $response['state'] ?? 'unknown',
                'instance' => $response,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getConnectionInfo(string $instanceName): array
    {
        Log::info('[EvolutionApiService] Getting connection info', [
            'instance' => $instanceName,
            'url' => $this->baseUrl . "/instance/connectionState/{$instanceName}",
        ]);

        try {
            $response = $this->get("/instance/connectionState/{$instanceName}", true);

            Log::info('[EvolutionApiService] Connection info raw response', [
                'instance' => $instanceName,
                'response' => $response,
            ]);

            $state = $response['state']
                ?? $response['instance']['state']
                ?? $response['data']['state']
                ?? 'unknown';

            $isConnected = $state === 'open';

            Log::info('[EvolutionApiService] Connection info parsed', [
                'instance' => $instanceName,
                'state' => $state,
                'isConnected' => $isConnected,
            ]);

            return [
                'success' => true,
                'status' => $state,
                'isConnected' => $isConnected,
                'phone' => $response['phone'] ?? $response['instance']['phone'] ?? null,
                'name' => $response['pushname'] ?? $response['instance']['pushname'] ?? null,
                'picture' => $response['picture'] ?? $response['instance']['picture'] ?? null,
                'jid' => $response['wid'] ?? $response['instance']['wid'] ?? null,
                'instance' => $response,
            ];
        } catch (Exception $e) {
            Log::error('[EvolutionApiService] Error getting connection info', [
                'instance' => $instanceName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function logout(string $instanceName): array
    {
        try {
            $response = $this->delete("/instance/logout/{$instanceName}");

            return [
                'success' => true,
                'data' => $response,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function deleteInstance(string $instanceName): array
    {
        try {
            $response = $this->delete("/instance/delete/{$instanceName}");

            return [
                'success' => true,
                'data' => $response,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function restart(string $instanceName): array
    {
        try {
            $response = $this->post("/instance/restart/{$instanceName}", []);

            return [
                'success' => true,
                'data' => $response,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function sendText(string $number, string $text): bool
    {
        Log::info('[EvolutionApiService] Sending text', [
            'instance' => $this->instanceName,
            'number' => $number,
        ]);

        $text = str_replace("\0", "", $text);

        try {
            $url = "{$this->baseUrl}/message/sendText/{$this->instanceName}";

            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->withOptions([
                'verify' => $this->verify,
            ])->post($url, [
                'number' => $number,
                'text' => $text,
            ]);

            if ($response->failed()) {
                Log::error('[EvolutionApiService] Send failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            Log::info('[EvolutionApiService] Message sent successfully');
            return true;
        } catch (Exception $e) {
            Log::error('[EvolutionApiService] Error sending message', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function sendInteractiveButtons(string $number, string $text, string $buttonText, string $buttonId): bool
    {
        Log::info('[EvolutionApiService] Sending interactive buttons', [
            'instance' => $this->instanceName,
            'number' => $number,
        ]);

        $text = str_replace("\0", "", $text);

        try {
            $url = "{$this->baseUrl}/message/sendInteractive/{$this->instanceName}";

            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->withOptions([
                'verify' => $this->verify,
            ])->post($url, [
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

            if ($response->failed()) {
                Log::error('[EvolutionApiService] Send interactive failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            Log::info('[EvolutionApiService] Interactive message sent successfully');
            return true;
        } catch (Exception $e) {
            Log::error('[EvolutionApiService] Error sending interactive message', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function sendInteractiveCTAUrl(string $number, string $text, string $url, string $buttonText): bool
    {
        Log::info('[EvolutionApiService] Sending interactive CTA URL', [
            'instance' => $this->instanceName,
            'number' => $number,
        ]);

        $text = str_replace("\0", "", $text);

        try {
            $urlEndpoint = "{$this->baseUrl}/message/sendInteractive/{$this->instanceName}";

            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->withOptions([
                'verify' => $this->verify,
            ])->post($urlEndpoint, [
                'number' => $number,
                'interactive' => [
                    'type' => 'cta_url',
                    'header' => [
                        'type' => 'text',
                        'text' => 'Continuar',
                    ],
                    'body' => [
                        'text' => $text,
                    ],
                    'footer' => [
                        'text' => 'Este enlace es seguro y expirará automáticamente.',
                    ],
                    'ctaUrl' => [
                        'url' => $url,
                        'displayText' => $buttonText,
                    ],
                ],
            ]);

            if ($response->failed()) {
                Log::error('[EvolutionApiService] Send interactive CTA URL failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            Log::info('[EvolutionApiService] Interactive CTA URL sent successfully');
            return true;
        } catch (Exception $e) {
            Log::error('[EvolutionApiService] Error sending interactive CTA URL', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function setWebhook(string $instanceName, string $webhookUrl): array
    {
        Log::info('[EvolutionApiService] Setting webhook', [
            'instance' => $instanceName,
            'webhookUrl' => $webhookUrl,
        ]);

        try {
            $payload = [
                'webhook' => [
                    'enabled' => true,
                    'url' => $webhookUrl,
                    'events' => [
                        'MESSAGES_UPSERT',
                    ],
                    'base64' => false,
                    'byEvents' => false,
                ],
            ];

            Log::info('[EvolutionApiService] Webhook payload', [
                'instance' => $instanceName,
                'url' => $this->baseUrl . "/webhook/set/{$instanceName}",
                'payload' => $payload,
            ]);

            $response = $this->post("/webhook/set/{$instanceName}", $payload);

            Log::info('[EvolutionApiService] Webhook set successfully', [
                'instance' => $instanceName,
                'response' => $response,
            ]);

            return [
                'success' => true,
                'data' => $response,
            ];
        } catch (Exception $e) {
            Log::error('[EvolutionApiService] Error setting webhook', [
                'instance' => $instanceName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getSession(string $instanceName): array
    {
        try {
            $response = $this->get("/instance/session/{$instanceName}");

            return [
                'success' => true,
                'session' => $response,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function registerWebhook(string $instanceName, string $webhookUrl): array
    {
        Log::info('[EvolutionApiService] Registering webhook', [
            'instance' => $instanceName,
            'webhookUrl' => $webhookUrl,
        ]);

        try {
            $payload = [
                'webhook' => [
                    'enabled' => true,
                    'url' => $webhookUrl,
                    'events' => [
                        'MESSAGES_UPSERT',
                    ],
                    'base64' => false,
                    'byEvents' => false,
                ],
            ];

            Log::info('[EvolutionApiService] Webhook payload', [
                'instance' => $instanceName,
                'url' => $this->baseUrl . "/webhook/set/{$instanceName}",
                'payload' => $payload,
            ]);

            $response = $this->post("/webhook/set/{$instanceName}", $payload);

            Log::info('[EvolutionApiService] Webhook registered successfully', [
                'instance' => $instanceName,
                'response' => $response,
            ]);

            return [
                'success' => true,
                'data' => $response,
            ];
        } catch (Exception $e) {
            Log::error('[EvolutionApiService] Error registering webhook', [
                'instance' => $instanceName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function findWebhook(string $instanceName): array
    {
        Log::info('[EvolutionApiService] Finding webhook', [
            'instance' => $instanceName,
        ]);

        try {
            $response = $this->get("/webhook/find/{$instanceName}");

            Log::info('[EvolutionApiService] Webhook found', [
                'instance' => $instanceName,
                'response' => $response,
            ]);

            return [
                'success' => true,
                'data' => $response,
            ];
        } catch (Exception $e) {
            Log::error('[EvolutionApiService] Error finding webhook', [
                'instance' => $instanceName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function get(string $endpoint, bool $raw = false): array
    {
        $url = $this->baseUrl . $endpoint;

        Log::info('[EvolutionApiService] GET request', ['url' => $url]);

        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->withOptions([
                'verify' => $this->verify,
            ])->timeout(30)->get($url);

            if ($response->failed()) {
                $body = $response->body();
                Log::error('[EvolutionApiService] GET failed', [
                    'status' => $response->status(),
                    'body' => $body,
                ]);
                throw new Exception("Evolution API error: " . $body);
            }

            $data = $response->json();

            Log::info('[EvolutionApiService] GET response', [
                'url' => $url,
                'raw' => $raw,
                'response' => $data,
            ]);

            if ($raw) {
                if (is_array($data) && (isset($data['data']) || isset($data['response']))) {
                    return $data['data'] ?? $data['response'];
                }
                if (is_array($data) && isset($data['instance'])) {
                    return $data['instance'];
                }
            }

            return $data ?? [];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[EvolutionApiService] Connection error', ['error' => $e->getMessage()]);
            throw new Exception("Error de conexión con Evolution API: " . $e->getMessage());
        }
    }

    protected function post(string $endpoint, array $data): array
    {
        $url = $this->baseUrl . $endpoint;

        Log::info('[EvolutionApiService] POST request', [
            'url' => $url,
            'data' => $data,
        ]);

        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->withOptions([
                'verify' => $this->verify,
            ])->timeout(30)->post($url, $data);

            if ($response->failed()) {
                $body = $response->body();
                Log::error('[EvolutionApiService] POST failed', [
                    'status' => $response->status(),
                    'body' => $body,
                ]);
                throw new Exception("Evolution API error: " . $body);
            }

            return $response->json();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[EvolutionApiService] Connection error', ['error' => $e->getMessage()]);
            throw new Exception("Error de conexión con Evolution API: " . $e->getMessage());
        }
    }

    protected function delete(string $endpoint): array
    {
        $url = $this->baseUrl . $endpoint;

        Log::info('[EvolutionApiService] DELETE request', ['url' => $url]);

        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
        ])->withOptions([
            'verify' => $this->verify,
        ])->delete($url);

        if ($response->failed()) {
            throw new Exception("Evolution API error: " . $response->body());
        }

        return $response->json();
    }
}