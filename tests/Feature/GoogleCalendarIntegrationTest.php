<?php

namespace Tests\Feature;

use App\Models\GoogleCalendarConnection;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class GoogleCalendarIntegrationTest extends TestCase
{
    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        // Use MySQL for tests (SQLite driver not available in this environment)
        putenv('DB_CONNECTION=mysql');
        $_ENV['DB_CONNECTION'] = 'mysql';
        parent::setUp();
        Config::set('database.default', 'mysql');

        $this->tenant = Tenant::create([
            'nombre' => 'Clinica Calendar Test',
            'slug' => 'clinica-cal-' . uniqid(),
            'email' => uniqid() . '@test.com',
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin Test',
            'email' => uniqid() . '@test.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_tokens_are_encrypted_at_rest(): void
    {
        $conn = GoogleCalendarConnection::create([
            'tenant_id' => $this->tenant->id,
            'google_email' => 'clinica@gmail.com',
            'calendar_id' => 'primary',
            'access_token' => 'ya29.access-token-secreto',
            'refresh_token' => 'refresh-token-secreto',
            'connected_at' => now(),
        ]);

        $raw = \DB::table('google_calendar_connections')->where('id', $conn->id)->first();

        // En la BD nunca debe verse el token en texto plano
        $this->assertStringNotContainsString('ya29.access-token-secreto', $raw->access_token);
        $this->assertStringNotContainsString('refresh-token-secreto', $raw->refresh_token);

        // Pero el modelo lo descifra correctamente
        $this->assertSame('ya29.access-token-secreto', $conn->fresh()->access_token);
        $this->assertSame('refresh-token-secreto', $conn->fresh()->refresh_token);
    }

    public function test_tokens_are_hidden_from_serialization(): void
    {
        $conn = GoogleCalendarConnection::create([
            'tenant_id' => $this->tenant->id,
            'access_token' => 'token-a',
            'refresh_token' => 'token-b',
            'connected_at' => now(),
        ]);

        $array = $conn->toArray();

        $this->assertArrayNotHasKey('access_token', $array);
        $this->assertArrayNotHasKey('refresh_token', $array);
    }

    public function test_one_connection_per_tenant(): void
    {
        GoogleCalendarConnection::create([
            'tenant_id' => $this->tenant->id,
            'refresh_token' => 'r1',
            'connected_at' => now(),
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        GoogleCalendarConnection::create([
            'tenant_id' => $this->tenant->id,
            'refresh_token' => 'r2',
            'connected_at' => now(),
        ]);
    }

    public function test_connections_are_isolated_by_tenant(): void
    {
        $otherTenant = Tenant::create([
            'nombre' => 'Otra Clinica',
            'slug' => 'otra-' . uniqid(),
            'email' => uniqid() . '@test.com',
        ]);

        GoogleCalendarConnection::create([
            'tenant_id' => $this->tenant->id,
            'refresh_token' => 'r-a',
            'connected_at' => now(),
        ]);
        GoogleCalendarConnection::create([
            'tenant_id' => $otherTenant->id,
            'refresh_token' => 'r-b',
            'connected_at' => now(),
        ]);

        $this->assertSame('r-a', GoogleCalendarConnection::forTenant($this->tenant->id)->first()->refresh_token);
        $this->assertSame('r-b', GoogleCalendarConnection::forTenant($otherTenant->id)->first()->refresh_token);
    }

    public function test_redirect_requires_auth(): void
    {
        $this->get('/google/calendar/redirect')->assertRedirect('/login');
    }

    public function test_redirect_sets_state_and_goes_to_google(): void
    {
        Config::set('services.google.client_id', 'test-client-id');
        Config::set('services.google.redirect_uri', 'https://podosof.devsoftai.com/google/calendar/callback');

        $response = $this->actingAs($this->user)->get('/google/calendar/redirect');

        $response->assertRedirect();
        $target = $response->headers->get('Location');

        $this->assertStringStartsWith('https://accounts.google.com/o/oauth2/', $target);
        $this->assertStringContainsString('access_type=offline', urldecode($target));
        $this->assertStringContainsString('prompt=select_account consent', urldecode($target));
        $this->assertStringContainsString(urlencode('https://www.googleapis.com/auth/calendar'), $target);
        $this->assertStringContainsString(urlencode('https://www.googleapis.com/auth/calendar.events'), $target);
        $this->assertNotNull(session('google_calendar_oauth_state'));
    }

    public function test_callback_rejects_invalid_state(): void
    {
        session(['google_calendar_oauth_state' => 'estado-real']);

        $response = $this->actingAs($this->user)
            ->get('/google/calendar/callback?state=estado-falso&code=abc');

        $response->assertRedirect(route('config.google-calendar'));
        $this->assertSame(0, GoogleCalendarConnection::forTenant($this->tenant->id)->count());
    }

    public function test_callback_handles_google_error(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/google/calendar/callback?error=access_denied');

        $response->assertRedirect(route('config.google-calendar'));
    }

    public function test_status_returns_json_without_tokens(): void
    {
        GoogleCalendarConnection::create([
            'tenant_id' => $this->tenant->id,
            'google_email' => 'clinica@gmail.com',
            'calendar_id' => 'primary',
            'access_token' => 'token-secreto',
            'refresh_token' => 'refresh-secreto',
            'connected_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->getJson('/google/calendar/status');

        $response->assertOk()
            ->assertJson(['connected' => true, 'google_email' => 'clinica@gmail.com']);

        $this->assertStringNotContainsString('token-secreto', $response->getContent());
        $this->assertStringNotContainsString('refresh-secreto', $response->getContent());
    }

    public function test_disconnect_removes_connection(): void
    {
        GoogleCalendarConnection::create([
            'tenant_id' => $this->tenant->id,
            'refresh_token' => 'refresh-secreto',
            'connected_at' => now(),
        ]);

        // revoke() hará una llamada HTTP a Google: la simulamos
        \Illuminate\Support\Facades\Http::fake(['*' => \Illuminate\Support\Facades\Http::response([], 200)]);

        $response = $this->actingAs($this->user)->post('/google/calendar/disconnect');

        $response->assertRedirect(route('config.google-calendar'));
        $this->assertSame(0, GoogleCalendarConnection::forTenant($this->tenant->id)->count());
    }
}
