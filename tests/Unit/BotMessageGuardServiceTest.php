<?php

namespace Tests\Unit;

use App\Models\BotBlockedContact;
use App\Services\BotMessageGuardService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BotMessageGuardServiceTest extends TestCase
{
    private BotMessageGuardService $guard;

    protected function setUp(): void
    {
        // Use MySQL for tests (SQLite driver not available in this environment)
        putenv('DB_CONNECTION=mysql');
        $_ENV['DB_CONNECTION'] = 'mysql';
        parent::setUp();
        Config::set('database.default', 'mysql');

        $this->guard = new BotMessageGuardService();

        // Ensure the table exists and clean up test entries
        if (!Schema::hasTable('bot_blocked_contacts')) {
            $this->artisan('migrate');
        }
    }

    protected function tearDown(): void
    {
        BotBlockedContact::where('reason', 'like', 'test-guard%')->delete();
        parent::tearDown();
    }

    private function blockNumber(string $phone, bool $active = true): BotBlockedContact
    {
        return BotBlockedContact::updateOrCreate(
            ['phone' => $phone],
            ['reason' => 'test-guard-' . uniqid(), 'active' => $active],
        );
    }

    public function test_normal_private_chat_is_not_discarded(): void
    {
        $this->assertFalse($this->guard->isGroup('573001234567@s.whatsapp.net'));
        // Asegura que este número de prueba no esté bloqueado por datos previos
        BotBlockedContact::where('phone', '573001234567')->where('active', true)->update(['active' => false]);
        $this->assertNull($this->guard->blockedReason('573001234567@s.whatsapp.net'));
    }

    public function test_group_message_is_discarded(): void
    {
        $this->assertTrue($this->guard->isGroup('120363012345678@g.us'));
        $this->assertSame('mensaje de grupo', $this->guard->blockedReason('120363012345678@g.us'));
    }

    public function test_blocked_number_is_discarded(): void
    {
        $this->blockNumber('573001234567');

        $this->assertSame('número bloqueado', $this->guard->blockedReason('573001234567@s.whatsapp.net'));
    }

    public function test_inactive_blocked_number_is_not_discarded(): void
    {
        $this->blockNumber('573001234567', active: false);

        $this->assertNull($this->guard->blockedReason('573001234567@s.whatsapp.net'));
    }

    public function test_phone_normalization_variants_match_blocked_entry(): void
    {
        $this->blockNumber('573001234567');

        $this->assertTrue(BotBlockedContact::isBlocked('573001234567'));
        $this->assertTrue(BotBlockedContact::isBlocked('+573001234567'));
        $this->assertTrue(BotBlockedContact::isBlocked('573001234567@s.whatsapp.net'));
        $this->assertFalse(BotBlockedContact::isBlocked('573009999999@s.whatsapp.net'));
    }

    public function test_phone_from_jid_extracts_digits_only(): void
    {
        $this->assertSame('573001234567', $this->guard->phoneFromJid('573001234567@s.whatsapp.net'));
        $this->assertSame('573001234567', $this->guard->phoneFromJid('573001234567'));
        $this->assertNull($this->guard->phoneFromJid(null));
    }
}
