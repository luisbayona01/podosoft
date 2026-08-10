<?php

namespace App\Livewire;

use App\Models\HorarioProfesional;
use App\Models\Profesional;
use Livewire\Component;
use Livewire\Attributes\Rule;

class ProfesionalCreate extends Component
{
    #[Rule('required|string|max:255')]
    public $nombre = '';

    #[Rule('required|string|max:255')]
    public $apellido = '';

    #[Rule('nullable|string|max:255')]
    public $especialidad = '';

    #[Rule('nullable|string|max:50')]
    public $numero_licencia = '';

    #[Rule('required|string|max:20')]
    public $documento = '';

    #[Rule('nullable|email|max:255')]
    public $email = '';

    #[Rule('boolean')]
    public $activo = true;

    public int $activeTab = 0;

    public array $horarios = [];

    public array $diasSemana = [
        'lunes' => 'Lunes',
        'martes' => 'Martes',
        'miercoles' => 'Miércoles',
        'jueves' => 'Jueves',
        'viernes' => 'Viernes',
        'sabado' => 'Sábado',
        'domingo' => 'Domingo',
    ];

    public function mount(): void
    {
        $this->initHorarios();
    }

    public function initHorarios(): void
    {
        $this->horarios = [];
        foreach ($this->diasSemana as $key => $label) {
            $this->horarios[$key] = [
                'dia_semana' => $key,
                'hora_inicio' => '08:00',
                'hora_fin' => '17:00',
                'activo' => false,
            ];
        }
    }

    public function setActiveTab(int $tab): void
    {
        $this->activeTab = $tab;
    }

    public function toggleDia(string $dia): void
    {
        if (isset($this->horarios[$dia])) {
            $this->horarios[$dia]['activo'] = !$this->horarios[$dia]['activo'];
        }
    }

    public function updated(string $property): void
    {
        if (str_contains($property, 'horarios')) {
            $this->activeTab = 1;
        }
    }

    protected function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'documento.required' => 'El documento es obligatorio.',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $tenantId = auth()->user()->tenant_id ?? 1;

        $exists = Profesional::where('tenant_id', $tenantId)
            ->where('documento', $this->documento)
            ->exists();

        if ($exists) {
            session()->flash('error', 'Ya existe un profesional con este documento.');
            return;
        }

        $profesional = Profesional::create([
            'tenant_id' => $tenantId,
            'nombre' => strtoupper($this->nombre),
            'apellido' => strtoupper($this->apellido),
            'especialidad' => $this->especialidad ? strtoupper($this->especialidad) : null,
            'numero_licencia' => $this->numero_licencia,
            'documento' => $this->documento,
            'email' => $this->email,
            'activo' => $this->activo,
        ]);

        foreach ($this->horarios as $h) {
            if ($h['activo'] && $h['hora_inicio'] < $h['hora_fin']) {
                HorarioProfesional::create([
                    'profesional_id' => $profesional->id,
                    'dia_semana' => $h['dia_semana'],
                    'hora_inicio' => $h['hora_inicio'],
                    'hora_fin' => $h['hora_fin'],
                    'activo' => true,
                ]);
            }
        }

        session()->flash('success', 'Profesional creado exitosamente.');
        redirect()->route('profesionales.index');
    }

    public function render()
    {
        return view('livewire.profesional-create');
    }
}