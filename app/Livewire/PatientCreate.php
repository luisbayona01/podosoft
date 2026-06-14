<?php

namespace App\Livewire;

use App\Models\Paciente;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Auth;

class PatientCreate extends Component
{
    #[Rule('required|string')]
    public $tipo_documento = 'CC';

    #[Rule('required|string')]
    public $documento;

    #[Rule('required|string|min:3')]
    public $nombre;

    #[Rule('required|string|min:3')]
    public $apellido;

    #[Rule('required|date')]
    public $fecha_nacimiento;

    #[Rule('required|string')]
    public $sexo;

    #[Rule('required|string')]
    public $telefono;

    #[Rule('nullable|email')]
    public $email;

    #[Rule('nullable|string')]
    public $direccion;

    #[Rule('required|accepted')]
    public $consentimiento = false;

    public function save()
    {
        try {
            $this->validate();

            Paciente::create([
                'tipo_documento' => $this->tipo_documento,
                'documento' => $this->documento,
                'nombre' => $this->nombre,
                'apellido' => $this->apellido,
                'fecha_nacimiento' => $this->fecha_nacimiento,
                'sexo' => $this->sexo,
                'telefono' => $this->telefono,
                'email' => $this->email,
                'direccion' => $this->direccion,
                'consentimiento' => true,
                'fecha_consentimiento' => now(),
                'tenant_id' => auth()->user()->tenant_id ?? 1,
            ]);

            session()->flash('message', 'Paciente registrado exitosamente.');
            return redirect()->route('patients.index');
        } catch (\Exception $e) {
            \Log::error('Error creating patient: ' . $e->getMessage());
            session()->flash('error', 'Ocurrió un error al registrar el paciente: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.patient-create');
    }
}
