<?php

namespace App\Livewire;

use App\Models\Paciente;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Auth;

class PatientEdit extends Component
{
    public Paciente $patient;

    #[Rule('required|string')]
    public $tipo_documento;

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

    public function mount(Paciente $patient)
    {
        $this->patient = $patient;
        $this->tipo_documento = $patient->tipo_documento;
        $this->documento = $patient->documento;
        $this->nombre = $patient->nombre;
        $this->apellido = $patient->apellido;
        $this->fecha_nacimiento = $patient->fecha_nacimiento?->format('Y-m-d');
        $this->sexo = $patient->sexo;
        $this->telefono = $patient->telefono;
        $this->email = $patient->email;
        $this->direccion = $patient->direccion;
        $this->consentimiento = $patient->consentimiento;
    }

    public function save()
    {
        try {
            $this->validate();

            $this->patient->update([
                'tipo_documento' => $this->tipo_documento,
                'documento' => $this->documento,
                'nombre' => $this->nombre,
                'apellido' => $this->apellido,
                'fecha_nacimiento' => $this->fecha_nacimiento,
                'sexo' => $this->sexo,
                'telefono' => $this->telefono,
                'email' => $this->email,
                'direccion' => $this->direccion,
                'consentimiento' => $this->consentimiento,
                'fecha_consentimiento' => $this->consentimiento ? now() : $this->patient->fecha_consentimiento,
            ]);

            session()->flash('message', 'Paciente actualizado exitosamente.');
            return redirect()->route('patients.show', $this->patient->id);
        } catch (\Exception $e) {
            \Log::error('Error updating patient: ' . $e->getMessage());
            session()->flash('error', 'Ocurrió un error al actualizar el paciente: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.patient-edit');
    }
}
