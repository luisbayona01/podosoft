<?php

namespace App\Livewire;

use App\Models\Servicio;
use Livewire\Component;
use Livewire\Attributes\Rule;

class ServicioEdit extends Component
{
    public $servicioId;

    #[Rule('required|string|max:255')]
    public $nombre = '';

    public $descripcion = '';

    #[Rule('required|numeric|min:0')]
    public $precio = 0;

    #[Rule('required|integer|min:1|max:480')]
    public $duracion = 30;

    #[Rule('boolean')]
    public $activo = true;

    protected function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del servicio es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 255 caracteres.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número.',
            'precio.min' => 'El precio no puede ser negativo.',
            'duracion.required' => 'La duración es obligatoria.',
            'duracion.integer' => 'La duración debe ser un número entero.',
            'duracion.min' => 'La duración mínima es 1 minuto.',
            'duracion.max' => 'La duración máxima es 480 minutos (8 horas).',
        ];
    }

    public function mount($servicio)
    {
        $servicio = Servicio::findOrFail($servicio);
        $this->servicioId = $servicio->id;
        $this->nombre = $servicio->nombre;
        $this->descripcion = $servicio->descripcion;
        $this->precio = $servicio->precio;
        $this->duracion = $servicio->duracion;
        $this->activo = $servicio->activo;
    }

    public function save()
    {
        $this->validate();

        $servicio = Servicio::find($this->servicioId);
        $servicio->update([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'duracion' => $this->duracion,
            'activo' => $this->activo,
        ]);

        session()->flash('success', 'Servicio "' . $servicio->nombre . '" actualizado exitosamente.');
        return redirect()->route('servicios.index');
    }

    public function render()
    {
        return view('livewire.servicio-edit');
    }
}