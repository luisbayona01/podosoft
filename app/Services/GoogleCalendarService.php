<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\GoogleCalendarConnection;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Oauth2;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GoogleCalendarService
{
    public const SCOPES = [
        Calendar::CALENDAR,         // listar/leer calendarios (CalendarList.List)
        Calendar::CALENDAR_EVENTS,  // crear/actualizar/eliminar eventos
        Oauth2::USERINFO_EMAIL,
    ];

    /**
     * Build a Google client configured with the app-level OAuth credentials.
     */
    public function makeClient(): Client
    {
        $client = new Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));

        // addScope (no setScopes) para asegurar que todos los scopes
        // se incluyan en la URL de autorización.
        foreach (self::SCOPES as $scope) {
            $client->addScope($scope);
        }

        $client->setAccessType('offline');
        $client->setIncludeGrantedScopes(true);
        $client->setPrompt('select_account consent'); // siempre emite refresh_token

        return $client;
    }

    /**
     * URL to start the OAuth consent flow.
     */
    public function createAuthUrl(string $state): string
    {
        $client = $this->makeClient();
        $client->setState($state);

        return $client->createAuthUrl();
    }

    /**
     * Exchange the authorization code for tokens and return the raw token array.
     * Never log this array: it contains the refresh_token.
     */
    public function exchangeCode(string $code): array
    {
        $client = $this->makeClient();
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            Log::warning('[GoogleCalendar] Token exchange failed', [
                'error' => $token['error'],
            ]);
            throw new RuntimeException('No fue posible autorizar la cuenta de Google.');
        }

        if (empty($token['refresh_token'])) {
            // Happens when the user already granted access without prompt=consent.
            // We require a refresh_token to be able to serve the tenant long-term.
            Log::warning('[GoogleCalendar] No refresh_token returned by Google');
            throw new RuntimeException('Google no entregó un token de largo plazo. Intenta conectar de nuevo.');
        }

        return $token;
    }

    /**
     * Get the authorized account email / id using a fresh access token.
     *
     * @return array{email: ?string, id: ?string}
     */
    public function getAccountInfo(string $accessToken): array
    {
        $client = $this->makeClient();
        $client->setAccessToken($accessToken);

        $oauth2 = new Oauth2($client);
        $info = $oauth2->userinfo->get();

        return [
            'email' => $info->email,
            'id' => $info->id,
        ];
    }

    /**
     * List the user's calendars where the account has WRITE access.
     * Read-only calendars (holidays, contacts, shared read-only) are excluded.
     *
     * @return array<int, array{id: string, summary: string, primary: bool, access_role: string}>
     */
    public function listCalendars(string $accessToken): array
    {
        $client = $this->makeClient();
        $client->setAccessToken($accessToken);

        $service = new Calendar($client);
        $calendars = [];

        foreach ($service->calendarList->listCalendarList()->getItems() as $cal) {
            if (!in_array($cal->getAccessRole(), ['owner', 'writer'], true)) {
                continue; // read-only calendar (holidays, etc.) -> skip
            }

            $calendars[] = [
                'id' => $cal->getId(),
                'summary' => $cal->getSummary(),
                'primary' => (bool) $cal->getPrimary(),
                'access_role' => $cal->getAccessRole(),
            ];
        }

        return $calendars;
    }

    /**
     * Resolve the calendar to use: the primary writable calendar, or the
     * first writable one as fallback.
     *
     * @param array<int, array{id: string, summary: string, primary: bool, access_role: string}> $calendars
     */
    public function resolveDefaultCalendar(array $calendars): ?string
    {
        if (empty($calendars)) {
            return null;
        }

        foreach ($calendars as $cal) {
            if ($cal['primary']) {
                return $cal['id'];
            }
        }

        return $calendars[0]['id'];
    }

    /**
     * Get an authorized client for the tenant's stored connection,
     * refreshing the access token when expired (and persisting the new one).
     */
    public function clientFor(GoogleCalendarConnection $connection): Client
    {
        $client = $this->makeClient();

        $client->setAccessToken([
            'access_token' => $connection->access_token,
            'refresh_token' => $connection->refresh_token,
            'expires_in' => $connection->token_expires_at
                ? max(1, $connection->token_expires_at->timestamp - time())
                : 1,
        ]);

        if ($client->isAccessTokenExpired()) {
            $newToken = $client->fetchAccessTokenWithRefreshToken($connection->refresh_token);

            if (isset($newToken['error'])) {
                // refresh_token revoked / invalid -> tenant must reconnect
                Log::warning('[GoogleCalendar] Refresh token failed', [
                    'tenant_id' => $connection->tenant_id,
                    'error' => $newToken['error'],
                ]);
                throw new RuntimeException('La conexión con Google Calendar expiró. Debes conectarla nuevamente.');
            }

            $connection->update([
                'access_token' => $newToken['access_token'],
                'token_expires_at' => now()->addSeconds((int) ($newToken['expires_in'] ?? 3600)),
            ]);
        }

        return $client;
    }

    /**
     * Quick connectivity check: fetches the configured calendar metadata.
     */
    public function testConnection(GoogleCalendarConnection $connection): array
    {
        $calendar = new Calendar($this->clientFor($connection));

        $cal = $calendar->calendars->get($connection->calendar_id ?: 'primary');

        return [
            'ok' => true,
            'calendar' => $cal->getSummary(),
            'timezone' => $cal->getTimeZone(),
        ];
    }

    public function createEvent(GoogleCalendarConnection $connection, Cita $cita): string
    {
        $calendarId = $connection->calendar_id ?: 'primary';
        $calendar = new Calendar($this->clientFor($connection));

        try {
            $event = $this->mapCitaToEvent($cita);
            $created = $calendar->events->insert($calendarId, $event);

            Log::info('[GoogleCalendar] Event created', [
                'tenant_id' => $connection->tenant_id,
                'cita_id' => $cita->id,
                'calendar_id' => $calendarId,
                'google_event_id' => $created->getId(),
            ]);

            return $created->getId();
        } catch (\Throwable $e) {
            Log::error('[GoogleCalendar] Event create failed', [
                'tenant_id' => $connection->tenant_id,
                'cita_id' => $cita->id,
                'calendar_id' => $calendarId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function updateEvent(GoogleCalendarConnection $connection, Cita $cita): void
    {
        if (!$cita->google_event_id) {
            return;
        }

        $calendar = new Calendar($this->clientFor($connection));
        $calendar->events->update(
            $connection->calendar_id ?: 'primary',
            $cita->google_event_id,
            $this->mapCitaToEvent($cita)
        );
    }

    public function deleteEvent(GoogleCalendarConnection $connection, Cita $cita): void
    {
        if (!$cita->google_event_id) {
            return;
        }

        $calendar = new Calendar($this->clientFor($connection));
        $calendar->events->delete($connection->calendar_id ?: 'primary', $cita->google_event_id);
    }

    protected function mapCitaToEvent(Cita $cita): Event
    {
        $tz = config('app.timezone', 'America/Bogota');

        $servicio = $cita->servicios()->first();
        $paciente = $cita->paciente;

        $startCarbon = $cita->fecha_hora->copy()->setTimezone($tz);
        $endCarbon = $cita->fecha_hora->copy()->addMinutes(30)->setTimezone($tz);

        $event = new Event();
        $event->setSummary('Cita: ' . trim(($paciente?->nombre ?? '') . ' ' . ($paciente?->apellido ?? '')));
        $event->setDescription(implode("\n", array_filter([
            $servicio ? 'Servicio: ' . $servicio->nombre : null,
            $cita->profesional ? 'Profesional: ' . $cita->profesional->nombre . ' ' . $cita->profesional->apellido : null,
            'Origen: ' . ($cita->origen ?? 'podosoft'),
        ])));

        $start = new EventDateTime();
        $start->setDateTime($startCarbon->format('Y-m-d\TH:i:s')); // hora local, sin desplazar
        $start->setTimeZone($tz);                                   // Google aplica la zona de negocio
        $event->setStart($start);

        $end = new EventDateTime();
        $end->setDateTime($endCarbon->format('Y-m-d\TH:i:s'));
        $end->setTimeZone($tz);
        $event->setEnd($end);

        // Log temporal para validar la zona horaria en la sincronización
        Log::debug('[GoogleCalendar] mapCitaToEvent', [
            'appointment_id' => $cita->id,
            'fecha_bd' => \DB::table('citas')->where('id', $cita->id)->value('fecha_hora'),
            'carbon' => $cita->fecha_hora->toDateTimeString(),
            'carbon_tz' => $cita->fecha_hora->timezone->getName(),
            'start_enviado' => $start->getDateTime(),
            'end_enviado' => $end->getDateTime(),
            'timezone_enviado' => $tz,
        ]);

        return $event;
    }

    /**
     * Revoke the authorization on Google's side (best effort).
     */
    public function revoke(GoogleCalendarConnection $connection): void
    {
        try {
            $token = $connection->refresh_token ?: $connection->access_token;
            if ($token) {
                $this->makeClient()->revokeToken($token);
            }
        } catch (\Throwable $e) {
            // Revocation is best-effort: even if it fails, we still remove
            // the local connection so the tenant can reconnect cleanly.
            Log::warning('[GoogleCalendar] revoke failed', [
                'tenant_id' => $connection->tenant_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
