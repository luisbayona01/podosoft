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
        $cita->update(['estado' => strtolower($newStatus)]);
        session()->flash('message', "Cita actualizada a $newStatus");
    }

    public function cancelAppointment($citaId)
    {
        $cita = Cita::findOrFail($citaId);
        $cita->update(['estado' => 'cancelada']);
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
                ->whereNotIn('estado', ['cancelada', 'no_asistio'])
                ->count(),
            'pending' => Cita::where('tenant_id', $tenant_id)
                ->where('estado', 'pendiente')
                ->count(),
            'confirmed' => Cita::where('tenant_id', $tenant_id)
                ->where('estado', 'confirmada')
                ->count(),
            'pagada' => Cita::where('tenant_id', $tenant_id)
                ->where('estado', 'pagada')
                ->count(),
            'cancelled' => Cita::where('tenant_id', $tenant_id)
                ->where('estado', 'cancelada')
                ->count(),
            'no_show' => Cita::where('tenant_id', $tenant_id)
                ->where('estado', 'no_asistio')
                ->count(),
            'completed' => Cita::where('tenant_id', $tenant_id)
                ->where('estado', 'completada')
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

        $todayAppointments = Cita::with(['paciente', 'profesional', 'servicios'])
            ->where('tenant_id', $tenant_id)
            ->whereDate('fecha_hora', now()->toDateString())
            ->whereNotIn('estado', ['cancelada', 'no_asistio'])
            ->orderBy('fecha_hora', 'asc')
            ->get();

        return view('livewire.appointment-index', [
            'appointments' => $appointments,
            'todayAppointments' => $todayAppointments,
            'metrics' => $this->getMetrics()
        ]);
    }
}
