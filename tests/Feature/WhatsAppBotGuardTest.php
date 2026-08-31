<?php

namespace Tests\Feature;

use App\Jobs\ProcessInboundWhatsAppMessage;
use App\Models\BotBlockedContact;
use App\Models\Tenant;
use App\Models\TenantWhatsAppAccount;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WhatsAppBotGuardTest extends TestCase
{
    private string $instanceName;

    protected function setUp(): void
    {
        // Use MySQL for tests (SQLite driver not available in this environment)
        putenv('DB_CONNECTION=mysql');
        $_ENV['DB_CONNECTION'] = 'mysql';
        parent::setUp();
        Config::set('database.default', 'mysql');

        // Evita llamadas HTTP reales a Evolution API (markMessageAsRead).
        Http::fake(['*' => Http::response(['status' => 'ok'])]);

        $this->instanceName = 'inst-test-' . uniqid();

        $tenant = Tenant::create([
            'nombre' => 'Clinica Test Guard',
            'slug' => 'clinica-guard-' . uniqid(),
            'email' => uniqid() . '@test.com',
            'telefono' => '111',
            'direccion' => 'Calle 1',
        ]);

        TenantWhatsAppAccount::create([
            'tenant_id' => $tenant->id,
            'provider' => 'evolution',
            'instance_name' => $this->instanceName,
            'status' => 'connected',
        ]);
    }

    protected function tearDown(): void
    {
        TenantWhatsAppAccount::where('instance_name', $this->instanceName)->forceDelete();
        BotBlockedContact::where('phone', '573001234567')->delete();
        parent::tearDown();
    }

    private function makePayload(string $remoteJid): array
    {
        return [
            'event' => 'messages.upsert',
            'instance' => $this->instanceName,
            'data' => [
                'key' => [
                    'remoteJid' => $remoteJid,
                    'fromMe' => false,
                    'id' => 'MSG123',
                ],
                'message' => ['conversation' => 'Hola'],
                'pushName' => 'Test',
            ],
        ];
    }

    public function test_private_message_is_queued_for_python(): void
    {
        Queue::fake();

        $this->postJson('/api/webhooks/evolution', $this->makePayload('573001234567@s.whatsapp.net'))
            ->assertOk();

        Queue::assertPushed(ProcessInboundWhatsAppMessage::class);
    }

    public function test_group_message_never_reaches_python(): void
    {
        Queue::fake();

        $this->postJson('/api/webhooks/evolution', $this->makePayload('120363012345678@g.us'))
            ->assertOk();

        Queue::assertNotPushed(ProcessInboundWhatsAppMessage::class);
    }

    public function test_blocked_number_never_reaches_python(): void
    {
        Queue::fake();

        BotBlockedContact::updateOrCreate(
            ['phone' => '573001234567'],
            ['reason' => 'Feature test', 'active' => true],
        );

        $this->postJson('/api/webhooks/evolution', $this->makePayload('573001234567@s.whatsapp.net'))
            ->assertOk();

        Queue::assertNotPushed(ProcessInboundWhatsAppMessage::class);
    }

    public function test_inactive_blocked_number_is_processed(): void
    {
        Queue::fake();

        BotBlockedContact::updateOrCreate(
            ['phone' => '573001234567'],
            ['reason' => 'Feature test', 'active' => false],
        );

        $this->postJson('/api/webhooks/evolution', $this->makePayload('573001234567@s.whatsapp.net'))
            ->assertOk();

        Queue::assertPushed(ProcessInboundWhatsAppMessage::class);
    }
}
