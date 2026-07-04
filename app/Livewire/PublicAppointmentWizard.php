<?php

namespace App\Livewire;

use App\Events\AppointmentCreated;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Models\Sede;
use App\Models\Tenant;
use App\Services\TenantResolverService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\URL;
use Livewire\Component;

class PublicAppointmentWizard extends Component
{
    public string $tenantSlug;
    public array $tenantConfig = [];

    public ?Paciente $patient = null;
    public ?string $documento = null;
    public ?string $phone = null;

    public int $step = 1;
    public int $totalSteps = 5;

    public array $servicios = [];
    public ?int $servicioId = null;
    public ?Servicio $selectedServicio = null;

    public array $profesionales = [];
    public ?int $profesionalId = null;
    public ?Profesional $selectedProfesional = null;

    public array $sedes = [];
    public ?int $sedeId = null;
    public ?Sede $selectedSede = null;

    public ?string $fecha = null;
    public array $horariosDisponibles = [];
    public ?string $horaSeleccionada = null;

    public bool $isLoading = false;
    public ?string $errorMessage = null;
    public ?Cita $citaCreada = null;

    protected $rules = [
        'servicioId' => 'required|integer',
        'profesionalId' => 'required|integer',
        'sedeId' => 'required|integer',
        'fecha' => 'required|date|after_or_equal:today',
        'horaSeleccionada' => 'required|string',
    ];

    public function mount(string $tenant): void
    {
        $this->tenantSlug = $tenant;

        $resolver = new TenantResolverService($tenant);
        $tenantModel = $resolver->resolve();

        if (!$tenantModel) {
            abort(404, 'Clínica no encontrada');
        }

        $this->tenantConfig = $resolver->getConfig();

        $this->documento = request()->get('documento');
        $this->phone = request()->get('phone');

        $servicioId = request()->get('servicio');
        if ($servicioId) {
            $this->servicioId = (int) $servicioId;
            $this->selectedServicio = Servicio::find($this->servicioId);
        }

        $this->loadPatientAndServices();

        if ($this->servicioId && $this->selectedServicio) {
            $this->step = 2;
        }
    }

    public function render()
    {
        return view('livewire.public-appointment-wizard')
            ->layout('layouts.public');
    }

    private function loadPatientAndServices(): void
    {
        $tenant = Tenant::where('slug', $this->tenantSlug)->first();

        if (!$tenant) {
            return;
        }

        if ($this->documento) {
            $this->patient = Paciente::where('tenant_id', $tenant->id)
                ->where('documento', $this->documento)
                ->first();
        }

        $this->servicios = Servicio::where('tenant_id', $tenant->id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->toArray();

        $this->profesionales = Profesional::where('tenant_id', $tenant->id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->toArray();

        $this->sedes = Sede::where('tenant_id', $tenant->id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->toArray();
    }

    public function selectServicio(int $servicioId): void
    {
        $this->servicioId = $servicioId;
        $this->selectedServicio = Servicio::find($servicioId);
    }

    public function selectProfesional(int $profesionalId): void
    {
        $this->profesionalId = $profesionalId;
        $this->selectedProfesional = Profesional::find($profesionalId);
    }

    public function selectSede(int $sedeId): void
    {
        $this->sedeId = $sedeId;
        $this->selectedSede = Sede::find($sedeId);
    }

    public function updatedFecha(string $fecha): void
    {
        if (empty($fecha)) {
            $this->horariosDisponibles = [];
            return;
        }

        if (!$this->profesionalId) {
            return;
        }

        $this->loadHorariosDisponibles($fecha);
    }

    public function updatedSedeId(int $sedeId): void
    {
        if ($sedeId && $this->fecha && $this->profesionalId) {
            $this->loadHorariosDisponibles($this->fecha);
        }
    }

    public function updatedProfesionalId(int $profesionalId): void
    {
        if ($profesionalId && $this->fecha && $this->sedeId) {
            $this->loadHorariosDisponibles($this->fecha);
        }
    }

    private function loadHorariosDisponibles(string $fecha): void
    {
        if (!$this->profesionalId) {
            $this->horariosDisponibles = [];
            return;
        }

        if (!$this->sedeId) {
            $this->horariosDisponibles = [];
            return;
        }

        $this->horariosDisponibles = $this->generateAvailableSlots($fecha);
    }

    private function generateAvailableSlots(string $fecha): array
    {
        $profesional = Profesional::find($this->profesionalId);
        if (!$profesional) {
            \Log::warning('[AppointmentWizard] Profesional no encontrado', ['profesional_id' => $this->profesionalId]);
            return [];
        }

        $diaIngles = strtolower(date('l', strtotime($fecha)));
        $diasSemana = [
            'monday' => 'lunes',
            'tuesday' => 'martes',
            'wednesday' => 'miercoles',
            'thursday' => 'jueves',
            'friday' => 'viernes',
            'saturday' => 'sabado',
            'sunday' => 'domingo',
        ];
        $diaSemana = $diasSemana[$diaIngles] ?? null;

        if (!$diaSemana) {
            \Log::warning('[AppointmentWizard] Día de semana no mapeado', ['dia_ingles' => $diaIngles]);
            return [];
        }

        \Log::info('[AppointmentWizard] Buscando horarios', [
            'profesional_id' => $this->profesionalId,
            'dia_semana' => $diaSemana,
            'fecha' => $fecha,
        ]);

        $horario = \App\Models\HorarioProfesional::where('profesional_id', $this->profesionalId)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->first();

        if (!$horario) {
            \Log::warning('[AppointmentWizard] No se encontró horario para el profesional', [
                'profesional_id' => $this->profesionalId,
                'dia_semana' => $diaSemana,
            ]);
            return [];
        }

        $inicio = strtotime($horario->hora_inicio);
        $fin = strtotime($horario->hora_fin);

        if (!$inicio || !$fin || $inicio >= $fin) {
            \Log::warning('[AppointmentWizard] Horario inválido', [
                'hora_inicio' => $horario->hora_inicio,
                'hora_fin' => $horario->hora_fin,
            ]);
            return [];
        }

        $duracion = $this->selectedServicio?->duracion ?? 30;

        $citasExistentes = Cita::where('profesional_id', $this->profesionalId)
            ->where('sede_id', $this->sedeId)
            ->whereDate('fecha_hora', $fecha)
            ->whereNotIn('estado', ['cancelada', 'no_asistio'])
            ->get()
            ->pluck('fecha_hora')
            ->map(fn($dt) => date('H:i', strtotime($dt)))
            ->toArray();

        $slots = [];
        for ($time = $inicio; $time < $fin; $time += $duracion * 60) {
            $slotTime = date('H:i', $time);

            if (!in_array($slotTime, $citasExistentes)) {
                $bloqueado = \App\Models\BloqueoAgenda::where('profesional_id', $this->profesionalId)
                    ->whereDate('fecha_inicio', '<=', $fecha)
                    ->whereDate('fecha_fin', '>=', $fecha)
                    ->exists();

                if (!$bloqueado) {
                    $slots[] = [
                        'hora' => $slotTime,
                        'disponible' => true,
                    ];
                }
            }
        }

        \Log::info('[AppointmentWizard] Slots generados', ['count' => count($slots), 'slots' => $slots]);
        return $slots;
    }

    public function selectHora(string $hora): void
    {
        $this->horaSeleccionada = $hora;
    }

    public function nextStep(): void
    {
        $this->isLoading = true;
        $this->errorMessage = null;

        try {
            match ($this->step) {
                1 => $this->validateStep1(),
                2 => $this->validateStep2(),
                3 => $this->validateStep3(),
                4 => $this->validateStep4(),
            };
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    private function validateStep1(): void
    {
        if (!$this->servicioId) {
            throw new \Exception('Seleccione un servicio.');
        }
        $this->step = 2;
    }

    private function validateStep2(): void
    {
        if (!$this->profesionalId) {
            throw new \Exception('Seleccione un profesional.');
        }
        $this->step = 3;
    }

    private function validateStep3(): void
    {
        if (!$this->sedeId) {
            throw new \Exception('Seleccione una sede.');
        }
        $this->step = 4;
    }

    private function validateStep4(): void
    {
        if (!$this->fecha) {
            throw new \Exception('Seleccione una fecha.');
        }
        if (!$this->horaSeleccionada) {
            throw new \Exception('Seleccione un horario.');
        }
        $this->step = 5;
    }

    public function confirmAppointment(): void
    {
        $this->isLoading = true;
        $this->errorMessage = null;

        try {
            $this->validate();

            $tenant = Tenant::where('slug', $this->tenantSlug)->first();

            $fechaHora = $this->fecha . ' ' . $this->horaSeleccionada . ':00';

            $cita = Cita::create([
                'tenant_id' => $tenant->id,
                'paciente_id' => $this->patient->id,
                'profesional_id' => $this->profesionalId,
                'sede_id' => $this->sedeId,
                'fecha_hora' => $fechaHora,
                'estado' => 'pendiente',
                'origen' => 'whatsapp',
            ]);

            $cita->servicios()->attach($this->servicioId, [
                'precio_aplicado' => $this->selectedServicio->precio,
            ]);

            $this->citaCreada = $cita;

            $whatsappPhone = $this->patient->telefono ?? $this->phone;

            if ($whatsappPhone) {
                $this->sendConfirmationWhatsApp($tenant, $whatsappPhone);
            }

        } catch (\Exception $e) {
            $this->errorMessage = 'Error al crear la cita. Intente nuevamente.';
            report($e);
        } finally {
            $this->isLoading = false;
        }
    }

    private function sendConfirmationWhatsApp(Tenant $tenant, string $whatsappPhone): void
    {
        if (!$whatsappPhone) {
            return;
        }

        try {
            $whatsappService = app(WhatsAppService::class)->forTenant($tenant->id);

            $message = "✅ Tu cita quedó registrada.\n\n";
            $message .= "📅 Fecha: " . date('d/m/Y', strtotime($this->fecha)) . "\n";
            $message .= "🕒 Hora: " . $this->horaSeleccionada . "\n";
            $message .= "👨‍⚕️ Profesional: {$this->selectedProfesional->nombre} {$this->selectedProfesional->apellido}\n";
            $message .= "📍 Sede: {$this->selectedSede->nombre}\n";
            $message .= "🔧 Servicio: {$this->selectedServicio->nombre}\n\n";
            $message .= "Gracias por utilizar nuestros servicios.";

            $whatsappService->sendText($whatsappPhone, $message);
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
            $this->errorMessage = null;
        }
    }

    public function restart(): void
    {
        $this->reset([
            'step', 'servicioId', 'selectedServicio',
            'profesionalId', 'selectedProfesional',
            'sedeId', 'selectedSede',
            'fecha', 'horariosDisponibles', 'horaSeleccionada',
            'errorMessage', 'citaCreada', 'isLoading'
        ]);
        $this->step = 1;
    }
}