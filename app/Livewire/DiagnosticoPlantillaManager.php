<?php

namespace App\Livewire;

use App\Models\DiagnosticoPlantilla;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class DiagnosticoPlantillaManager extends Component
{
    public $templates;
    public $nombre, $diagnostico_base, $procedimiento_base;
    public $templateId;
    public $isEditing = false;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'diagnostico_base' => 'required|string',
        'procedimiento_base' => 'required|string',
    ];

    public function mount()
    {
        $this->loadTemplates();
    }

    public function loadTemplates()
    {
        $this->templates = DiagnosticoPlantilla::where('tenant_id', Auth::user()->tenant_id ?? 1)->get();
    }

    public function save()
    {
        $this->validate();

        $data = [
            'tenant_id' => Auth::user()->tenant_id ?? 1,
            'nombre' => $this->nombre,
            'diagnostico_base' => $this->diagnostico_base,
            'procedimiento_base' => $this->procedimiento_base,
        ];

        if ($this->isEditing && $this->templateId) {
            DiagnosticoPlantilla::find($this->templateId)->update($data);
            session()->flash('success', 'Plantilla actualizada correctamente.');
        } else {
            DiagnosticoPlantilla::create($data);
            session()->flash('success', 'Plantilla creada correctamente.');
        }

        $this->resetForm();
        $this->loadTemplates();
    }

    public function edit($id)
    {
        $template = DiagnosticoPlantilla::findOrFail($id);
        $this->templateId = $id;
        $this->nombre = $template->nombre;
        $this->diagnostico_base = $template->diagnostico_base;
        $this->procedimiento_base = $template->procedimiento_base;
        $this->isEditing = true;
    }

    public function delete($id)
    {
        DiagnosticoPlantilla::findOrFail($id)->delete();
        session()->flash('success', 'Plantilla eliminada.');
        $this->loadTemplates();
    }

    public function resetForm()
    {
        $this->reset(['nombre', 'diagnostico_base', 'procedimiento_base', 'templateId', 'isEditing']);
    }

    public function render()
    {
        return view('livewire.diagnostico-plantilla-manager');
    }
}
