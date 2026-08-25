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

    public $editMode = false;
    public $editDiagnostico = '';
    public $editProcedimiento = '';
    public $editObservaciones = '';

    protected function rules()
    {
        return [
            'editDiagnostico' => 'required|string|min:5',
            'editProcedimiento' => 'required|string|min:5',
            'editObservaciones' => 'nullable|string',
        ];
    }

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
        $this->editMode = false;
    }

    public function startEdit()
    {
        $history = HistoriaClinica::findOrFail($this->selectedHistoryId);
        if ($history->bloqueo_edicion) {
            $this->addError('edit', 'Esta nota está bloqueada para edición.');
            return;
        }
        $this->editDiagnostico = $history->diagnostico;
        $this->editProcedimiento = $history->procedimiento;
        $this->editObservaciones = $history->observaciones;
        $this->editMode = true;
    }

    public function cancelEdit()
    {
        $this->editMode = false;
        $this->resetValidation();
    }

    public function saveEdit()
    {
        $this->validate();

        $history = HistoriaClinica::findOrFail($this->selectedHistoryId);
        if ($history->bloqueo_edicion) {
            $this->addError('edit', 'Esta nota está bloqueada para edición.');
            return;
        }

        $history->update([
            'diagnostico' => $this->editDiagnostico,
            'procedimiento' => $this->editProcedimiento,
            'observaciones' => $this->editObservaciones,
        ]);

        $this->editMode = false;
        session()->flash('message', 'Nota de evolución actualizada exitosamente.');
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
            ? HistoriaClinica::with(['fotografias', 'documentos'])->find($this->selectedHistoryId)
            : null;

        return view('livewire.clinical-history-index', [
            'patient' => $patient,
            'lastHistory' => $lastHistory,
            'selectedHistory' => $selectedHistory,
        ]);
    }
}