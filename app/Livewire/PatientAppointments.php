<?php

namespace App\Livewire;

use App\Models\Cita;
use Livewire\Component;
use Livewire\WithPagination;

class PatientAppointments extends Component
{
    public $patientId;

    public function render()
    {
        $appointments = Cita::where('paciente_id', $this->patientId)
            ->with(['profesional', 'sede', 'servicios'])
            ->orderBy('fecha_hora', 'desc')
            ->paginate(10);

        return view('livewire.patient-appointments', [
            'appointments' => $appointments
        ]);
    }
}
