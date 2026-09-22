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
 * Deletes the Google Calendar event linked to an appointment.
 * Prepared for future automatic sync. Not dispatched yet.
 */
class DeleteGoogleCalendarEvent implements ShouldQueue
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

        if (!$cita || !$cita->google_event_id) {
            return;
        }

        $connection = GoogleCalendarConnection::forTenant($cita->tenant_id)->first();

        if ($connection?->isConnected()) {
            try {
                $google->deleteEvent($connection, $cita);
            } catch (\Throwable $e) {
                Log::warning('[GoogleCalendar] Event delete failed', [
                    'tenant_id' => $cita->tenant_id,
                    'cita_id' => $cita->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $cita->update(['google_event_id' => null]);
    }
}
