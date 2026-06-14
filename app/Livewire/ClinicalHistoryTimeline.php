<?php

namespace App\Livewire;

use App\Models\HistoriaClinica;
use App\Models\Paciente;
use Livewire\Component;
use Livewire\WithPagination;

class ClinicalHistoryTimeline extends Component
{
    public $patientId;
    public $filterProcedure = '';

    public function render()
    {
        $histories = HistoriaClinica::where('paciente_id', $this->patientId)
            ->when($this->filterProcedure, function($q) {
                $q->where('procedimiento', 'like', '%' . $this->filterProcedure . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.clinical-history-timeline', [
            'histories' => $histories,
            'patient' => Paciente::findOrFail($this->patientId)
        ]);
    }
}
