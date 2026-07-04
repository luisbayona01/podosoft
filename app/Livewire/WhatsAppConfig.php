<?php

namespace App\Livewire;

use App\Models\Tenant;
use App\Models\TenantWhatsAppAccount;
use App\Services\EvolutionApiService;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class WhatsAppConfig extends Component
{
    public Tenant $tenant;

    public ?TenantWhatsAppAccount $account = null;

    public string $statusText = '';

    public string $statusColor = 'gray';

    public bool $showQrModal = false;

    public string $qrCodeBase64 = '';

    public string $errorMessage = '';

    public bool $isLoading = false;

    public string $connectionStatus = 'unknown';

    public function mount(): void
    {
        $this->tenant = auth()->user()->tenant;
        $this->loadAccount();
    }

    public function loadAccount(): void
    {
        $this->account = TenantWhatsAppAccount::where('tenant_id', $this->tenant->id)->first();

        if ($this->account) {
            $this->updateStatusDisplay();
            $this->refreshConnectionStatus();
        }
    }

    protected function updateStatusDisplay(): void
    {
        match ($this->account->status) {
            'connected' => [
                $this->statusText = 'Conectado',
                $this->statusColor = 'green',
            ],
            'connecting' => [
                $this->statusText = 'Conectando...',
                $this->statusColor = 'yellow',
            ],
            'pending' => [
                $this->statusText = 'Pendiente',
                $this->statusColor = 'gray',
            ],
            'disconnected' => [
                $this->statusText = 'Desconectado',
                $this->statusColor = 'red',
            ],
            'error' => [
                $this->statusText = 'Error',
                $this->statusColor = 'red',
            ],
            default => [
                $this->statusText = 'Desconocido',
                $this->statusColor = 'gray',
            ],
        };
    }

    public function refreshConnectionStatus(): void
    {
        if (!$this->account) {
            return;
        }

        $evolution = app(EvolutionApiService::class)->fromAccount($this->account);
        $result = $evolution->getConnectionInfo($this->account->instance_name);

        if ($result['success']) {
            $state = $result['status'] ?? 'unknown';

            $statusMap = [
                'open' => 'connected',
                'close' => 'disconnected',
                'connecting' => 'connecting',
                'waiting' => 'connecting',
            ];

            $newStatus = $statusMap[$state] ?? $this->account->status;
            $isConnected = $newStatus === 'connected';

            $updateData = [
                'status' => $newStatus,
                'last_seen_at' => $isConnected ? now() : $this->account->last_seen_at,
            ];

            if ($isConnected) {
                $updateData['phone'] = $result['phone'] ?? $this->account->phone;
                $updateData['connected_at'] = $this->account->connected_at ?? now();
            }

            if (in_array($state, ['close', 'disconnected'])) {
                $updateData['qr_code'] = null;
                $updateData['qr_code_base64'] = null;
            }

            $this->account->update($updateData);

            $this->connectionStatus = $state;
            $this->updateStatusDisplay();
        }
    }

    public function createAndConnect(): void
    {
        $this->isLoading = true;
        $this->errorMessage = '';

        try {
            if ($this->account) {
                $this->deleteInstance();
            }

            $instanceName = 'tenant_' . $this->tenant->id . '_' . Str::lower(Str::random(8));
            $webhookUrl = route('api.webhook.whatsapp', ['instance' => $instanceName]);

            $evolution = new EvolutionApiService();
            $result = $evolution->createInstance($instanceName, $webhookUrl);

            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'Error al crear instancia');
            }

            $this->account = TenantWhatsAppAccount::create([
                'tenant_id' => $this->tenant->id,
                'instance_name' => $instanceName,
                'provider' => 'evolution',
                'status' => 'pending',
                'server_url' => config('services.evolution.url'),
                'api_key' => config('services.evolution.key'),
                'webhook_url' => $webhookUrl,
            ]);

            $this->requestQr();

        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    public function requestQr(): void
    {
        if (!$this->account) {
            return;
        }

        $this->isLoading = true;
        $this->errorMessage = '';

        try {
            $evolution = app(EvolutionApiService::class)->fromAccount($this->account);

            $connectResult = $evolution->connect($this->account->instance_name);

            if (!$connectResult['success']) {
                throw new \Exception($connectResult['error'] ?? 'Error al conectar');
            }

            $qrBase64 = $connectResult['base64'] ?? null;
            $qrCode = $connectResult['qrcode'] ?? null;

            if (!$qrBase64) {
                $qrResult = $evolution->getQRCode($this->account->instance_name);

                if (!$qrResult['success']) {
                    throw new \Exception($qrResult['error'] ?? 'Error al obtener QR');
                }

                $qrBase64 = $qrResult['base64'] ?? null;
                $qrCode = $qrResult['qrcode'] ?? null;
            }

            if (!$qrBase64) {
                throw new \Exception('No se pudo obtener el código QR');
            }

            $this->account->update([
                'status' => 'connecting',
                'qr_code' => $qrCode,
                'qr_code_base64' => $qrBase64,
            ]);

            $this->qrCodeBase64 = $qrBase64;
            $this->showQrModal = true;
            $this->updateStatusDisplay();

        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    public function refreshQr(): void
    {
        $this->requestQr();
    }

    public function closeQrModal(): void
    {
        if ($this->account) {
            $this->refreshConnectionStatus();

            $evolution = app(EvolutionApiService::class)->fromAccount($this->account);
            $connectionInfo = $evolution->getConnectionInfo($this->account->instance_name);

            if ($connectionInfo['success'] && ($connectionInfo['status'] ?? '') === 'open') {
                $this->registerWebhookIfNeeded();
                $this->showQrModal = false;
                session()->flash('success', 'WhatsApp conectado correctamente');
                return;
            }
        }

        $this->showQrModal = false;
    }

    protected function registerWebhookIfNeeded(): void
    {
        if (!$this->account || !$this->account->webhook_configured) {
            $evolution = app(EvolutionApiService::class)->fromAccount($this->account);

            $existingWebhook = $evolution->findWebhook($this->account->instance_name);

            if ($existingWebhook['success'] && !empty($existingWebhook['data'])) {
                Log::info('[WhatsAppConfig] Webhook already configured', [
                    'instance' => $this->account->instance_name,
                    'webhook' => $existingWebhook['data'],
                ]);

                $this->account->update(['webhook_configured' => true]);
                return;
            }

            $webhookUrl = config('app.url') . '/api/webhooks/evolution';

            Log::info('[WhatsAppConfig] Registering webhook', [
                'instance' => $this->account->instance_name,
                'webhookUrl' => $webhookUrl,
            ]);

            $result = $evolution->registerWebhook($this->account->instance_name, $webhookUrl);

            if ($result['success']) {
                $this->account->update(['webhook_configured' => true]);
                Log::info('[WhatsAppConfig] Webhook registered successfully', [
                    'instance' => $this->account->instance_name,
                ]);
            } else {
                Log::warning('[WhatsAppConfig] Failed to register webhook', [
                    'instance' => $this->account->instance_name,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);
            }
        }
    }

    public function checkConnection(): void
    {
        $this->refreshConnectionStatus();
    }

    public function disconnect(): void
    {
        if (!$this->account) {
            return;
        }

        $this->isLoading = true;

        try {
            $evolution = app(EvolutionApiService::class)->fromAccount($this->account);
            $evolution->logout($this->account->instance_name);

            $this->account->update([
                'status' => 'disconnected',
                'qr_code' => null,
                'qr_code_base64' => null,
            ]);

            $this->updateStatusDisplay();
            $this->showQrModal = false;

        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    public function reconnect(): void
    {
        if (!$this->account) {
            return;
        }

        $this->isLoading = true;

        try {
            $evolution = app(EvolutionApiService::class)->fromAccount($this->account);
            $evolution->restart($this->account->instance_name);

            $this->requestQr();

        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    public function deleteInstance(): void
    {
        if (!$this->account) {
            return;
        }

        $this->isLoading = true;

        try {
            $evolution = app(EvolutionApiService::class)->fromAccount($this->account);
            $evolution->deleteInstance($this->account->instance_name);

            $this->account->delete();
            $this->account = null;
            $this->statusText = '';
            $this->statusColor = 'gray';

        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.whatsapp-config');
    }
}