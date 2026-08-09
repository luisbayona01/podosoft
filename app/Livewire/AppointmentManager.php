<?php

namespace App\Livewire;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Profesional;
use App\Models\Sede;
use App\Models\Servicio;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Carbon\Carbon;

class AppointmentManager extends Component
{
    public $citaId = null;
    public $isEdit = false;

    #[Rule('required|exists:pacientes,id')]
    public $paciente_id = '';

    #[Rule('required|exists:profesionales,id')]
    public $profesional_id = '';

    #[Rule('required|exists:sedes,id')]
    public $sede_id = '';

    #[Rule('required|date')]
    public $fecha_hora = '';

    #[Rule('required|exists:servicios,id')]
    public $servicio_id = '';

    public $patientSearch = '';
    public $searchResults = [];

    public function mount($id = null)
    {
        if ($id) {
            $this->citaId = $id;
            $this->isEdit = true;
            $cita = Cita::with(['paciente', 'profesional', 'sede'])->findOrFail($id);
            
            $this->paciente_id = $cita->paciente_id;
            $this->profesional_id = $cita->profesional_id;
            $this->sede_id = $cita->sede_id;
            $this->fecha_hora = $cita->fecha_hora->format('Y-m-d\TH:i');
            
            // Get the first service associated with the appointment
            $servicio = $cita->servicios()->first();
            $this->servicio_id = $servicio ? $servicio->id : '';
        } else {
            $this->fecha_hora = now()->format('Y-m-d\TH:i');
        }
    }

    public function searchPatient()
    {
        if (strlen($this->patientSearch) < 3) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Paciente::where('tenant_id', auth()->user()->tenant_id ?? 1)
            ->where(function($q) {
                $q->where('nombre', 'like', '%' . $this->patientSearch . '%')
                  ->orWhere('apellido', 'like', '%' . $this->patientSearch . '%')
                  ->orWhere('documento', 'like', '%' . $this->patientSearch . '%');
            })
            ->limit(5)
            ->get();
    }

    public function selectPatient($id, $nombre, $apellido)
    {
        $this->paciente_id = $id;
        $this->patientSearch = "$nombre $apellido";
        $this->searchResults = [];
    }

    public function save()
    {
        $this->validate();

        // Check availability
        $exists = Cita::where('profesional_id', $this->profesional_id)
            ->where('fecha_hora', $this->fecha_hora)
            ->when($this->citaId, fn($q) => $q->where('id', '!=', $this->citaId))
            ->exists();

        if ($exists) {
            session()->flash('error', 'El horario solicitado ya está ocupado por otra cita.');
            return;
        }

        if ($this->isEdit) {
            $cita = Cita::findOrFail($this->citaId);
            $cita->update([
                'paciente_id' => $this->paciente_id,
                'profesional_id' => $this->profesional_id,
                'sede_id' => $this->sede_id,
                'fecha_hora' => $this->fecha_hora,
            ]);
            $message = 'Cita actualizada exitosamente.';
        } else {
            $cita = Cita::create([
                'tenant_id' => auth()->user()->tenant_id ?? 1,
                'paciente_id' => $this->paciente_id,
                'profesional_id' => $this->profesional_id,
                'sede_id' => $this->sede_id,
                'fecha_hora' => $this->fecha_hora,
                'estado' => 'pendiente',
            ]);
            
            $servicio = Servicio::findOrFail($this->servicio_id);
            $cita->servicios()->attach($servicio->id, [
                'precio_aplicado' => $servicio->precio
            ]);
            $message = 'Cita agendada exitosamente.';
        }

        session()->flash('message', $message);
        return redirect()->route('appointments.index');
    }

    public function render()
    {
        return view('livewire.appointment-manager', [
            'profesionales' => Profesional::where('tenant_id', auth()->user()->tenant_id ?? 1)->get(),
            'sedes' => Sede::where('tenant_id', auth()->user()->tenant_id ?? 1)->get(),
            'servicios' => Servicio::where('tenant_id', auth()->user()->tenant_id ?? 1)->where('activo', 1)->get(),
        ]);
    }
}
