<?php

namespace App\Livewire;

use App\Models\HistoriaClinica;
use App\Models\Paciente;
use Livewire\Component;

class ClinicalHistoryIndex extends Component
{
    public $patientId;
    public $selectedHistoryId = null;
    public $activeTab = 'timeline';

    public function mount()
    {
        $lastHistory = HistoriaClinica::where('paciente_id', $this->patientId)->latest()->first();
        if ($lastHistory) {
            $this->selectedHistoryId = $lastHistory->id;
        }
    }

    public function selectHistory($historyId)
    {
        $this->selectedHistoryId = $historyId;
        $this->activeTab = 'timeline';
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $patient = Paciente::findOrFail($this->patientId);
        $lastHistory = HistoriaClinica::where('paciente_id', $this->patientId)->latest()->first();
        $selectedHistory = $this->selectedHistoryId
            ? HistoriaClinica::with('fotografias')->find($this->selectedHistoryId)
            : null;

        return view('livewire.clinical-history-index', [
            'patient' => $patient,
            'lastHistory' => $lastHistory,
            'selectedHistory' => $selectedHistory,
        ]);
    }
}