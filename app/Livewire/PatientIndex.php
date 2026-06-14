<?php

namespace App\Livewire;

use App\Models\Paciente;
use Livewire\Component;
use Livewire\WithPagination;

class PatientIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $filter = 'all';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilter()
    {
        $this->resetPage();
    }

    public function getStats()
    {
        $tenantId = auth()->user()->tenant_id ?? 1;
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();

        return [
            'total' => Paciente::where('tenant_id', $tenantId)->count(),
            'active' => Paciente::where('tenant_id', $tenantId)->count(), // Soft deletes handle inactive by default
            'new_this_month' => Paciente::where('tenant_id', $tenantId)->where('created_at', '>=', $startOfMonth)->count(),
            'with_appointments' => Paciente::where('tenant_id', $tenantId)->whereHas('citas')->count(),
        ];
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id ?? 1;
        $query = Paciente::where('tenant_id', $tenantId)->with(['citas' => function($q) {
            $q->latest()->limit(1);
        }]);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('apellido', 'like', '%' . $this->search . '%')
                  ->orWhere('documento', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filter === 'active') {
            // Already default
        } elseif ($this->filter === 'inactive') {
            $query->onlyTrashed();
        }

        $patients = $query->latest()->paginate(10);

        return view('livewire.patient-index', [
            'patients' => $patients,
            'stats' => $this->getStats()
        ]);
    }
}
