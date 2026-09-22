<?php

namespace App\Jobs;

use App\Models\Cita;
use App\Models\GoogleCalendarConnection;
use App\Services\GoogleCalendarService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Creates a Google Calendar event for an appointment, using the
 * connection that belongs to the appointment's tenant.
 *
 * NOTE: prepared for future automatic sync. Not dispatched yet.
 */
class CreateGoogleCalendarEvent implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        public int $citaId,
    ) {
        $this->onQueue('google-calendar');
    }

    public function handle(GoogleCalendarService $google): void
    {
        $cita = Cita::find($this->citaId);

        if (!$cita || $cita->google_event_id) {
            return;
        }

        $connection = GoogleCalendarConnection::forTenant($cita->tenant_id)->first();

        if (!$connection?->isConnected()) {
            Log::info('[GoogleCalendar] No connection for tenant, skipping event create', [
                'tenant_id' => $cita->tenant_id,
                'cita_id' => $cita->id,
            ]);
            return;
        }

        $eventId = $google->createEvent($connection, $cita);
        $cita->update(['google_event_id' => $eventId]);
    }
}
