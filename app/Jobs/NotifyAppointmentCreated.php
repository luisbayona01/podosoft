<?php

namespace App\Jobs;

use App\Events\AppointmentCreated;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyAppointmentCreated implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public AppointmentCreated $event
    ) {}

    public function handle(WhatsAppService $whatsAppService): void
    {
        $appointment = $this->event->appointment;
        $tenant = $this->event->tenant;
        $phone = $this->event->whatsappPhone;

        if (!$phone) {
            Log::warning('[NotifyAppointmentCreated] No phone number provided');
            return;
        }

        $profesional = $appointment->profesional;
        $servicio = $appointment->servicios()->first();
        $sede = $appointment->sede;

        $message = "✅ Tu cita quedó registrada.\n\n";
        $message .= "📅 Fecha: " . $appointment->fecha_hora->format('d/m/Y') . "\n";
        $message .= "🕒 Hora: " . $appointment->fecha_hora->format('H:i') . "\n";
        $message .= "👨‍⚕️ Profesional: {$profesional->nombre} {$profesional->apellido}\n";

        if ($servicio) {
            $message .= "🔧 Servicio: {$servicio->nombre}\n";
        }

        if ($sede) {
            $message .= "📍 Sede: {$sede->nombre}\n";
        }

        $message .= "\nGracias por utilizar nuestros servicios.";

        try {
            $whatsAppService->sendText($phone, $message);
            Log::info('[NotifyAppointmentCreated] WhatsApp notification sent', ['phone' => $phone]);
        } catch (\Exception $e) {
            Log::error('[NotifyAppointmentCreated] Failed to send WhatsApp notification', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
        }
    }
}