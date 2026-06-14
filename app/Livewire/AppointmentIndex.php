<?php

namespace App\Livewire;

use App\Models\Cita;
use Livewire\Component;
use Livewire\WithPagination;

class AppointmentIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $date = '';
    public $viewMode = 'list'; // 'list' or 'calendar'

    public function updateStatus($citaId, $newStatus)
    {
        $cita = Cita::findOrFail($citaId);
        $cita->update(['estado' => $newStatus]);
        session()->flash('message', "Cita actualizada a $newStatus");
    }

    public function cancelAppointment($citaId)
    {
        $cita = Cita::findOrFail($citaId);
        $cita->update(['estado' => 'Cancelada']);
        session()->flash('message', 'Cita cancelada exitosamente');
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function getMetrics()
    {
        $tenant_id = auth()->user()->tenant_id ?? 1;
        return [
            'today' => Cita::where('tenant_id', $tenant_id)
                ->whereDate('fecha_hora', now()->toDateString())
                ->count(),
            'pending' => Cita::where('tenant_id', $tenant_id)
                ->where('estado', 'Pendiente')
                ->count(),
            'confirmed' => Cita::where('tenant_id', $tenant_id)
                ->where('estado', 'Confirmada')
                ->count(),
            'cancelled' => Cita::where('tenant_id', $tenant_id)
                ->where('estado', 'Cancelada')
                ->count(),
            'no_show' => Cita::where('tenant_id', $tenant_id)
                ->where('estado', 'No asistió')
                ->count(),
            'completed' => Cita::where('tenant_id', $tenant_id)
                ->where('estado', 'Completada')
                ->count(),
        ];
    }

    public function render()
    {
        $tenant_id = auth()->user()->tenant_id ?? 1;

        $appointments = Cita::with(['paciente', 'profesional', 'sede'])
            ->where('tenant_id', $tenant_id)
            ->when($this->search, function($q) {
                $q->whereHas('paciente', function($pq) {
                    $pq->where('nombre', 'like', '%' . $this->search . '%')
                       ->orWhere('documento', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function($q) {
                $q->where('estado', $this->status);
            })
            ->when($this->date, function($q) {
                $q->whereDate('fecha_hora', $this->date);
            })
            ->orderBy('fecha_hora', 'asc')
            ->paginate(15);

        return view('livewire.appointment-index', [
            'appointments' => $appointments,
            'metrics' => $this->getMetrics()
        ]);
    }
}
