<?php

namespace App\Console\Commands;

use App\Models\Cita;
use App\Models\RecordatorioCita;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:send-reminders
                            {--batch=20 : Cantidad de mensajes por lote}
                            {--delay=15 : Minutos de espera entre lotes}
                            {--date= : Fecha a la que pertenecen las citas (YYYY-MM-DD). Por defecto: hoy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía recordatorios de citas por WhatsApp en lotes espaciados para evitar spam';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppService $whatsAppService)
    {
        $batchSize = (int) $this->option('batch');
        $delayMinutes = (int) $this->option('delay');
        $date = $this->option('date') ?: now()->toDateString();

        $this->info("▶ Iniciando envío de recordatorios para el {$date} (lote de {$batchSize}, espera de {$delayMinutes} min)");

        $citas = $this->getPendingCitas($date);

        if ($citas->isEmpty()) {
            $this->info('No hay citas pendientes de recordatorio para esta fecha.');
            return self::SUCCESS;
        }

        $total = $citas->count();
        Log::info('[Recordatorios] Total de citas a recordar', ['total' => $total, 'date' => $date]);

        $batches = $citas->chunk($batchSize);
        $batchNumber = 0;
        $sent = 0;
        $failed = 0;

        foreach ($batches as $batch) {
            $batchNumber++;

            $this->info("┌─ Lote {$batchNumber}/{$batches->count()} ({$batch->count()} mensajes)");

            foreach ($batch as $cita) {
                $result = $this->sendReminder($cita, $whatsAppService);

                if ($result) {
                    $sent++;
                } else {
                    $failed++;
                    $this->warn("   ✗ Cita #{$cita->id} ({$cita->paciente?->telefono}): no enviado");
                }
            }

            $this->info("└─ Lote {$batchNumber} finalizado. Enviados hasta ahora: {$sent}, errores: {$failed}");

            $isLastBatch = $batch->last()?->id === $citas->last()?->id;
            if (!$isLastBatch) {
                $this->info("   ⏳ Esperando {$delayMinutes} minutos antes del siguiente lote...");
                Log::info('[Recordatorios] Esperando entre lotes', ['delay_minutes' => $delayMinutes, 'next_batch' => $batchNumber + 1]);
                sleep($delayMinutes * 60);
            }
        }

        $this->info("✔ Proceso finalizado. Enviados: {$sent}, errores: {$failed}.");
        return self::SUCCESS;
    }

    protected function getPendingCitas(string $date)
    {
        $recordadasCitaIds = RecordatorioCita::pluck('cita_id');

        return Cita::with(['paciente', 'profesional', 'servicios', 'sede'])
            ->whereDate('fecha_hora', $date)
            ->where('fecha_hora', '>', now())
            ->whereIn('estado', ['pendiente', 'confirmada', 'pagada'])
            ->whereHas('paciente', fn ($q) => $q->whereNotNull('telefono')->where('telefono', '!=', ''))
            ->when($recordadasCitaIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $recordadasCitaIds))
            ->orderBy('fecha_hora', 'asc')
            ->get();
    }

    protected function sendReminder(Cita $cita, WhatsAppService $whatsAppService): bool
    {
        $phone = $cita->paciente?->telefono;

        if (!$phone) {
            return false;
        }

        $profesional = $cita->profesional;
        $servicio = $cita->servicios()->first();
        $sede = $cita->sede;

        $message = "⏰ Recordatorio de cita\n\n";
        $message .= "Hola {$cita->paciente->nombre}! Te recordamos tu próxima cita:\n\n";
        $message .= "📅 Fecha: {$cita->fecha_hora->format('d/m/Y')}\n";
        $message .= "🕒 Hora: {$cita->fecha_hora->format('H:i')}\n";

        if ($profesional) {
            $message .= "👨‍⚕️ Profesional: {$profesional->nombre} {$profesional->apellido}\n";
        }
        if ($servicio) {
            $message .= "🔧 Servicio: {$servicio->nombre}\n";
        }
        if ($sede) {
            $message .= "📍 Sede: {$sede->nombre}\n";
        }

        $message .= "\nTe esperamos. Si no puedes asistir, por favor avísanos con antelación.";
        $message .= "\n\nCordialmente, {$cita->tenant?->nombre}";

        $sent = false;
        try {
            $sent = $whatsAppService
                ->forTenant($cita->tenant_id)
                ->sendText($phone, $message);
        } catch (\Exception $e) {
            Log::error('[Recordatorios] Error enviando recordatorio', [
                'cita_id' => $cita->id,
                'error' => $e->getMessage(),
            ]);
        }

        RecordatorioCita::create([
            'cita_id' => $cita->id,
            'tenant_id' => $cita->tenant_id,
            'telefono' => $phone,
            'estado' => $sent ? 'enviado' : 'error',
            'sent_at' => now(),
        ]);

        if ($sent) {
            $this->info("   ✓ Cita #{$cita->id} → {$phone}");
            Log::info('[Recordatorios] Recordatorio enviado', ['cita_id' => $cita->id, 'phone' => $phone]);
        }

        return $sent;
    }
}