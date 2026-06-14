<?php

namespace App\Livewire;

use App\Models\Paciente;
use Livewire\Component;
use Livewire\WithPagination;

class ClinicalHistoryMain extends Component
{
    use WithPagination;

    public $search = '';

    public function getStats()
    {
        $tenantId = auth()->user()->tenant_id ?? 1;
        return [
            'total_histories' => \App\Models\HistoriaClinica::where('tenant_id', $tenantId)->count(),
            'patients_with_history' => \App\Models\Paciente::where('tenant_id', $tenantId)->whereHas('historiaClinica')->count(),
            'recent_entries' => \App\Models\HistoriaClinica::where('tenant_id', $tenantId)->where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id ?? 1;
        $patients = Paciente::where('tenant_id', $tenantId)
            ->where(function($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('apellido', 'like', '%' . $this->search . '%')
                  ->orWhere('documento', 'like', '%' . $this->search . '%');
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('livewire.clinical-history-main', [
            'patients' => $patients,
            'stats' => $this->getStats()
        ]);
    }
}
