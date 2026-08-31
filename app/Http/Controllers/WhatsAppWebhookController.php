<?php

namespace App\Http\Controllers;

use App\Models\TenantWhatsAppAccount;
use App\Services\BotMessageGuardService;
use App\Services\EvolutionApiService;
use App\Services\PodosoftKnowledgeSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function handleEvolution(Request $request)
    {
        Log::emergency('[WhatsAppWebhook] ===========================================', []);
        Log::error('[WhatsAppWebhook] handleEvolution() iniciado', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'body' => $request->all(),
        ]);

        $instance = $request->input('instance')
            ?? $request->input('instanceName')
            ?? $request->input('data.instance');

        Log::error('[WhatsAppWebhook] Instancia extraída', [
            'instance' => $instance,
        ]);

        if (!$instance) {
            Log::warning('[WhatsAppWebhook] Instance not provided in Evolution webhook');
            return response()->json(['error' => 'Instance not provided'], 400);
        }

        return $this->handle($request, $instance);
    }

    public function handle(Request $request, string $instance)
    {
        Log::info('[WhatsAppWebhook] ===========================================', []);
        Log::info('[WhatsAppWebhook] handle() iniciado', [
            'instance' => $instance,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ]);

        $account = TenantWhatsAppAccount::where('instance_name', $instance)->first();

        if (!$account) {
            Log::warning('[WhatsAppWebhook] Instance not found', ['instance' => $instance]);
            return response()->json(['error' => 'Instance not found'], 404);
        }

        $data = $request->all();

        if (isset($data['event'])) {
            match ($data['event']) {
                'CONNECTION_UPDATE' => $this->handleConnectionUpdate($account, $data),
                'QRCODE_UPDATED' => $this->handleQrCodeUpdate($account, $data),
                'messages.upsert' => $this->handleMessageUpsert($account, $data),
                'SESSIONS_EVENT' => $this->handleSessionEvent($account, $data),
                default => Log::info('[WhatsAppWebhook] Unknown event', ['event' => $data['event']]),
            };
        }

        return response()->json(['status' => 'ok']);
    }

    protected function handleConnectionUpdate(TenantWhatsAppAccount $account, array $data): void
    {
        /**
         * EVENTO: CONNECTION_UPDATE
         *
         * Evolution API notifica cambios en el estado de conexión
         *
         * Estados posibles (del API de Evolution):
         * - open: Conexión establecida (WhatsApp escaneado y activo)
         * - close: Conexión cerrada
         * - connecting: En proceso de conexión
         * - waiting: Esperando escaneo del QR
         *
         * Mapeo interno:
         * 'open' -> 'connected'
         * 'close' -> 'disconnected'
         * 'connecting' -> 'connecting'
         * 'waiting' -> 'connecting'
         *
         * Estructura típica del webhook:
         * {
         *   "event": "CONNECTION_UPDATE",
         *   "data": {
         *     "state": "open",
         *     "phone": "573001234567",
         *     "pushname": "Nombre"
         *   }
         * }
         */
        $state = $data['data']['state'] ?? $data['state'] ?? null;

        Log::info('[WhatsAppWebhook] Connection update', [
            'instance' => $account->instance_name,
            'tenant_id' => $account->tenant_id,
            'state' => $state,
            'data' => $data,
        ]);

        $statusMap = [
            'open' => 'connected',
            'close' => 'disconnected',
            'connecting' => 'connecting',
            'waiting' => 'connecting',
        ];

        $newStatus = $statusMap[$state] ?? $account->status;

        $updateData = ['status' => $newStatus];

        if ($state === 'open') {
            $updateData['connected_at'] = now();
            $updateData['last_seen_at'] = now();

            $phone = $data['data']['phone'] ?? $data['phone'] ?? $data['data']['wid'] ?? null;

            Log::info('[WhatsAppWebhook] Extracting phone', [
                'instance' => $account->instance_name,
                'phone_source' => $phone,
                'data' => $data,
            ]);

            $updateData['phone'] = $phone ?? $account->phone;
        }

        if (in_array($state, ['close', 'disconnected'])) {
            $updateData['qr_code'] = null;
            $updateData['qr_code_base64'] = null;
        }

        $account->update($updateData);

        if ($newStatus === 'connected') {
            $this->syncKnowledgeBase($account->tenant_id);
        }

        Log::info('[WhatsAppWebhook] Account updated', [
            'instance' => $account->instance_name,
            'tenant_id' => $account->tenant_id,
            'state' => $state,
            'new_status' => $newStatus,
            'update_data' => $updateData,
        ]);
    }

    protected function handleQrCodeUpdate(TenantWhatsAppAccount $account, array $data): void
    {
        /**
         * EVENTO: QRCODE_UPDATED
         *
         * Evolution API notifica que el QR ha sido actualizado
         * Esto sucede cuando se genera un nuevo código QR
         */
        Log::info('[WhatsAppWebhook] QR code update', [
            'instance' => $account->instance_name,
            'tenant_id' => $account->tenant_id,
        ]);

        if (isset($data['data']['qrcode'])) {
            $account->update([
                'qr_code' => $data['data']['qrcode'],
                'qr_code_base64' => $data['data']['base64'] ?? null,
                'status' => 'connecting',
            ]);
        }
    }

    protected function handleMessageUpsert(TenantWhatsAppAccount $account, array $data): void
    {
        /**
         * EVENTO: MESSAGES_UPSERT
         *
         * Evolution API notifica que arrived un nuevo mensaje de WhatsApp
         *
         * Extracción de datos:
         * - phone: remoteJid del remitente (sin @s.whatsapp.net)
         * - message: contenido del mensaje (puede venir en diferentes formatos)
         *
         * FLUJO DE PROCESAMIENTO:
         * 1. Extraer phone y message del webhook
         * 2. Validar que no sea un mensaje saliente (fromMe = true)
         * 3. Obtener tenant_id desde la cuenta (account->tenant_id)
         * 4. Invocar AgentService->handleMessage(phone, message, tenant_id)
         * 5. AgentService procesa el mensaje y responde
         *
         * Estructura típica del webhook:
         * {
         *   "event": "MESSAGES_UPSERT",
         *   "instance": "nombre_instancia",
         *   "data": {
         *     "key": {
         *       "remoteJid": "573001234567@s.whatsapp.net",
         *       "fromMe": false,
         *       "id": "xxx"
         *     },
         *     "message": {
         *       "conversation": "texto del mensaje"
         *     },
         *     "pushName": "Nombre del contacto"
         *   }
         * }
         */
        $messageData = $data['data'] ?? $data;
        $key = $messageData['key'] ?? [];

        $remoteJid = $key['remoteJid'] ?? null;
        $fromMe = $key['fromMe'] ?? false;

        if (!$remoteJid || $fromMe) {
            Log::debug('[WhatsAppWebhook] Skipping message', [
                'instance' => $account->instance_name,
                'tenant_id' => $account->tenant_id,
                'reason' => $fromMe ? 'outgoing message' : 'no remoteJid',
            ]);
            return;
        }

        // Filtros del bot ANTES de cualquier procesamiento:
        // - mensajes de grupos (@g.us) se descartan;
        // - números bloqueados (bot_blocked_contacts, activos) se descartan.
        // Nunca llegan a Python ni se responden vía Evolution API.
        $discardReason = app(BotMessageGuardService::class)->blockedReason($remoteJid);

        if ($discardReason !== null) {
            Log::info('[WhatsAppWebhook] Message discarded by bot guard', [
                'instance' => $account->instance_name,
                'tenant_id' => $account->tenant_id,
                'remoteJid' => $remoteJid,
                'reason' => $discardReason,
            ]);
            return;
        }

        $phone = preg_replace('/@s\.whatsapp\.net$/', '', $remoteJid);

        $message = $this->extractMessageText($messageData);

        if (!$message) {
            Log::debug('[WhatsAppWebhook] Empty message', [
                'instance' => $account->instance_name,
                'tenant_id' => $account->tenant_id,
                'phone' => $phone,
            ]);
            return;
        }

        Log::info('[WhatsAppWebhook] Processing message', [
            'instance' => $account->instance_name,
            'tenant_id' => $account->tenant_id,
            'phone' => $phone,
            'message' => $message,
            'from' => $messageData['pushName'] ?? 'unknown',
        ]);

        $updateData = ['last_seen_at' => now()];

        if (!$account->phone) {
            $updateData['phone'] = $phone;
            Log::info('[WhatsAppWebhook] Setting account phone from message', [
                'instance' => $account->instance_name,
                'tenant_id' => $account->tenant_id,
                'phone' => $phone,
            ]);
        }

        $account->update($updateData);

        $messageData = $data['data'] ?? $data;

        try {
            $evolutionApi = app(EvolutionApiService::class)->fromAccount($account);
            $evolutionApi->markMessageAsRead($account->instance_name, $messageData);

            $tenantSlug = $account->tenant?->slug ?? '';

            // Async: enqueue the agent processing so the webhook returns
            // immediately. The worker handles the (potentially slow) LLM call
            // and sends the reply — the single-threaded dev server never blocks
            // the Python service's by-phone callback (no deadlock).
            \App\Jobs\ProcessInboundWhatsAppMessage::dispatch(
                $account->instance_name,
                $phone,
                $message,
                $account->tenant_id,
                $tenantSlug,
            );

            Log::info('[WhatsAppWebhook] Message queued for agent', [
                'instance' => $account->instance_name,
                'tenant_id' => $account->tenant_id,
                'phone' => $phone,
                'queue' => config('queue.default'),
            ]);
        } catch (\Exception $e) {
            Log::error('[WhatsAppWebhook] Error queueing message', [
                'instance' => $account->instance_name,
                'tenant_id' => $account->tenant_id,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function handleSessionEvent(TenantWhatsAppAccount $account, array $data): void
    {
        /**
         * EVENTO: SESSIONS_EVENT
         *
         * Evolution API notifica eventos de sesión
         *
         * Eventos comunes:
         * - session_connected: Sesión establecida exitosamente
         * - session_logged_out: Sesión cerrada por el usuario
         */
        Log::info('[WhatsAppWebhook] Session event', [
            'instance' => $account->instance_name,
            'tenant_id' => $account->tenant_id,
            'event' => $data['data']['event'] ?? 'unknown',
        ]);

        $event = $data['data']['event'] ?? null;

        if ($event === 'session_connected') {
            $account->update([
                'status' => 'connected',
                'connected_at' => now(),
                'last_seen_at' => now(),
            ]);
        } elseif ($event === 'session_logged_out') {
            $account->update([
                'status' => 'disconnected',
            ]);
        }
    }

    protected function extractMessageText(array $data): ?string
    {
        /**
         * Extrae el texto del mensaje de diversas estructuras posibles
         *
         * Evolution API puede enviar el mensaje en diferentes formatos:
         * - {message: {conversation: "texto"}}
         * - {message: {extendedTextMessage: {text: "texto"}}}
         * - {message: {imageMessage: {caption: "texto"}}}
         * - {text: "texto"} (formato simplificado)
         */
        if (isset($data['message']['conversation'])) {
            return $data['message']['conversation'];
        }

        if (isset($data['message']['extendedTextMessage']['text'])) {
            return $data['message']['extendedTextMessage']['text'];
        }

        if (isset($data['message']['imageMessage']['caption'])) {
            return $data['message']['imageMessage']['caption'];
        }

        if (isset($data['text'])) {
            return $data['text'];
        }

        if (isset($data['message'])) {
            return is_string($data['message']) ? $data['message'] : null;
        }

        return null;
    }

    /**
     * Push the tenant's knowledge base to the Python AI service.
     *
     * Called whenever the WhatsApp account is (re)connected so the assistant
     * always has fresh static data (services, hours, locations) even when the
     * Redis copy in Python has expired.
     */
    protected function syncKnowledgeBase(int $tenantId): void
    {
        try {
            $sync = app(PodosoftKnowledgeSyncService::class);
            if ($sync->configured()) {
                $sync->push($tenantId);
            }
        } catch (\Exception $e) {
            Log::warning('[WhatsAppWebhook] Knowledge sync skipped', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}