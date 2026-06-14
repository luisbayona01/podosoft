<?php

namespace App\Livewire;

use App\Models\HistoriaClinica;
use App\Models\Paciente;
use Livewire\Component;

class ClinicalHistoryIndex extends Component
{
    public $patientId;

    public function render()
    {
        return view('livewire.clinical-history-index', [
            'patient' => Paciente::findOrFail($this->patientId),
            'lastHistory' => HistoriaClinica::where('paciente_id', $this->patientId)->latest()->first()
        ]);
    }
}
