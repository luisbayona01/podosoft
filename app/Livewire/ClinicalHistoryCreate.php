<?php

namespace App\Livewire;

use App\Models\HistoriaClinica;
use App\Models\Paciente;
use App\Models\DiagnosticoPlantilla;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ClinicalHistoryCreate extends Component
{
    public $patientId;
    public $citaId;
    public $diagnostico = '';

    public function mount()
    {
        $this->patientId = request()->query('patientId');
        $this->citaId = request()->query('citaId');
    }

    public $procedimiento = '';
    public $observaciones = '';
    public $firma_digital = '';
    public $selectedTemplate = '';

    protected function rules()
    {
        return [
            'diagnostico' => 'required|string|min:5',
            'procedimiento' => 'required|string|min:5',
            'observaciones' => 'nullable|string',
            'firma_digital' => 'required|string',
        ];
    }

    public function applyTemplate()
    {
        $template = DiagnosticoPlantilla::find($this->selectedTemplate);
        if ($template) {
            $this->diagnostico = $template->diagnostico_base;
            $this->procedimiento = $template->procedimiento_base;
        }
    }

    public function save()
    {
        $this->validate();

        HistoriaClinica::create([
            'tenant_id' => Auth::user()->tenant_id ?? 1,
            'paciente_id' => $this->patientId,
            'cita_id' => $this->citaId,
            'diagnostico' => $this->diagnostico,
            'procedimiento' => $this->procedimiento,
            'observaciones' => $this->observaciones,
            'firma_digital' => $this->firma_digital,
            'fecha_firma' => now(),
            'bloqueo_edicion' => false,
        ]);

        session()->flash('message', 'Nota de evolución registrada exitosamente.');
        return redirect()->route('patients.show', $this->patientId);
    }

    public function render()
    {
        return view('livewire.clinical-history-create', [
            'templates' => DiagnosticoPlantilla::where('tenant_id', Auth::user()->tenant_id ?? 1)->get()
        ]);
    }
}
