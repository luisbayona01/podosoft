<?php

namespace App\Livewire;

use App\Models\Paciente;
use App\Models\PieZona;
use App\Models\ConsultaHallazgo;
use Livewire\Component;

class FootMap extends Component
{
    public $patientId;
    public $selectedZonaId = '';
    public $note = '';

    public function markZona()
    {
        if (!$this->selectedZonaId) return;

        ConsultaHallazgo::create([
            'historia_clinica_id' => 1, // Simplified for now, should be dynamic from current session/visit
            'pie_zona_id' => $this->selectedZonaId,
            'observaciones' => $this->note,
        ]);

        $this->reset(['selectedZonaId', 'note']);
        session()->flash('message', 'Zona marcada correctamente.');
    }

    public function render()
    {
        return view('livewire.foot-map', [
            'zonas' => PieZona::all()
        ]);
    }
}
