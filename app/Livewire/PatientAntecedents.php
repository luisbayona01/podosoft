<?php

namespace App\Livewire;

use App\Models\Paciente;
use App\Models\AntecedentType;
use Livewire\Component;

class PatientAntecedents extends Component
{
    public $patientId;
    public $selectedType = '';
    public $notes = '';

    protected function rules()
    {
        return [
            'selectedType' => 'required',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function addAntecedent()
    {
        $this->validate();

        $patient = Paciente::findOrFail($this->patientId);
        $patient->antecedents()->attach($this->selectedType, [
            'notes' => $this->notes
        ]);

        $this->reset(['selectedType', 'notes']);
        session()->flash('message', 'Antecedente registrado correctamente.');
    }

    public function removeAntecedent($typeId)
    {
        $patient = Paciente::findOrFail($this->patientId);
        $patient->antecedents()->detach($typeId);
    }

    public function render()
    {
        $patient = Paciente::findOrFail($this->patientId);
        return view('livewire.patient-antecedents', [
            'patient' => $patient,
            'types' => AntecedentType::all(),
            'patientAntecedents' => $patient->antecedents()->withPivot('notes')->get()
        ]);
    }
}
