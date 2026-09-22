<?php

namespace App\Http\Controllers;

use App\Models\GoogleCalendarConnection;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleCalendarController extends Controller
{
    private const SESSION_STATE_KEY = 'google_calendar_oauth_state';

    public function __construct(
        protected GoogleCalendarService $google
    ) {}

    /**
     * Starts the OAuth flow: redirects the user to Google consent screen.
     */
    public function redirect(Request $request)
    {
        if (!config('services.google.client_id')) {
            return redirect()->route('config.google-calendar')
                ->with('error', 'La integración con Google Calendar no está configurada.');
        }

        $state = Str::random(40);
        $request->session()->put(self::SESSION_STATE_KEY, $state);

        return redirect()->away($this->google->createAuthUrl($state));
    }

    /**
     * OAuth callback. Validates state, exchanges code and stores the
     * connection for the CURRENT authenticated user's tenant.
     */
    public function callback(Request $request)
    {
        if ($request->filled('error')) {
            Log::info('[GoogleCalendar] OAuth denied', [
                'error' => $request->query('error'),
                'tenant_id' => $request->user()?->tenant_id,
            ]);

            return redirect()->route('config.google-calendar')
                ->with('error', 'No se completó la autorización en Google.');
        }

        $sessionState = $request->session()->pull(self::SESSION_STATE_KEY);

        if (!$sessionState || !hash_equals($sessionState, (string) $request->query('state'))) {
            Log::warning('[GoogleCalendar] Invalid OAuth state', [
                'tenant_id' => $request->user()?->tenant_id,
            ]);

            return redirect()->route('config.google-calendar')
                ->with('error', 'La verificación de seguridad falló. Intenta conectar de nuevo.');
        }

        $code = $request->query('code');
        if (!$code) {
            return redirect()->route('config.google-calendar')
                ->with('error', 'Google no devolvió un código de autorización.');
        }

        // Tenant comes from the authenticated session, NEVER from the request.
        $tenantId = $request->user()->tenant_id;

        try {
            $token = $this->google->exchangeCode($code);
            $account = $this->google->getAccountInfo($token['access_token']);
            $calendars = $this->google->listCalendars($token['access_token']);

            // Prefer the primary calendar by default
            $calendarId = array_key_exists('primary', $calendars) ? 'primary' : array_key_first($calendars);

            GoogleCalendarConnection::updateOrCreate(
                ['tenant_id' => $tenantId],
                [
                    'google_email' => $account['email'],
                    'google_account_id' => $account['id'],
                    'calendar_id' => $calendarId,
                    'access_token' => $token['access_token'],
                    'refresh_token' => $token['refresh_token'],
                    'token_expires_at' => now()->addSeconds((int) ($token['expires_in'] ?? 3600)),
                    'scopes' => $token['scope'] ?? null,
                    'connected_at' => now(),
                ]
            );

            Log::info('[GoogleCalendar] Connected', [
                'tenant_id' => $tenantId,
                'google_email' => $account['email'],
            ]);

            return redirect()->route('config.google-calendar')
                ->with('message', 'Google Calendar conectado correctamente.');
        } catch (\Throwable $e) {
            Log::error('[GoogleCalendar] Callback failed', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('config.google-calendar')
                ->with('error', 'No fue posible completar la conexión con Google Calendar.');
        }
    }

    /**
     * Revokes Google authorization and removes the tenant's connection.
     * Appointments are untouched; google_event_id values are kept.
     */
    public function disconnect(Request $request)
    {
        $connection = GoogleCalendarConnection::forTenant($request->user()->tenant_id)->first();

        if (!$connection) {
            return redirect()->route('config.google-calendar')
                ->with('error', 'No hay una conexión de Google Calendar activa.');
        }

        $this->google->revoke($connection);
        $connection->delete();

        Log::info('[GoogleCalendar] Disconnected', [
            'tenant_id' => $request->user()->tenant_id,
        ]);

        return redirect()->route('config.google-calendar')
            ->with('message', 'Google Calendar desconectado.');
    }

    /**
     * Live status JSON (used by the "Probar conexión" button via Livewire
     * calling the service directly; this endpoint is an extra health check).
     */
    public function status(Request $request)
    {
        $connection = GoogleCalendarConnection::forTenant($request->user()->tenant_id)->first();

        return response()->json([
            'connected' => (bool) $connection?->isConnected(),
            'google_email' => $connection?->google_email,
            'calendar_id' => $connection?->calendar_id,
            'connected_at' => $connection?->connected_at?->toIso8601String(),
        ]);
    }
}
