<?php

namespace App\Livewire;

use App\Models\Paciente;
use Livewire\Component;

class PatientShow extends Component
{
    public Paciente $patient;
    public $activeTab = 'general';

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function mount(Paciente $patient, array $data = [])
    {
        $this->patient = $patient;
        $this->activeTab = $data['tab'] ?? 'general';
    }

    public function render()
    {
        return view('livewire.patient-show');
    }
}
