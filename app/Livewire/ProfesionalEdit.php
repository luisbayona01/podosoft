<?php

namespace App\Livewire;

use App\Models\HorarioProfesional;
use App\Models\Profesional;
use Livewire\Component;
use Livewire\Attributes\Rule;

class ProfesionalEdit extends Component
{
    public $profesionalId;

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

    protected function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'documento.required' => 'El documento es obligatorio.',
        ];
    }

    public function mount($profesional): void
    {
        $profesional = Profesional::findOrFail($profesional);
        $this->profesionalId = $profesional->id;
        $this->nombre = $profesional->nombre;
        $this->apellido = $profesional->apellido;
        $this->especialidad = $profesional->especialidad;
        $this->numero_licencia = $profesional->numero_licencia;
        $this->documento = $profesional->documento;
        $this->email = $profesional->email;
        $this->activo = $profesional->activo;

        $this->initHorarios();
        $this->loadHorarios($profesional->id);
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
                'existing_id' => null,
            ];
        }
    }

    public function setActiveTab(int $tab): void
    {
        $this->activeTab = $tab;
    }

    public function loadHorarios(int $profesionalId): void
    {
        $horariosDb = HorarioProfesional::where('profesional_id', $profesionalId)->get();

        $this->horarios = [];
        foreach ($this->diasSemana as $key => $label) {
            $horario = $horariosDb->firstWhere('dia_semana', $key);
            $this->horarios[$key] = [
                'dia_semana' => $key,
                'hora_inicio' => $horario?->hora_inicio ?? '08:00',
                'hora_fin' => $horario?->hora_fin ?? '17:00',
                'activo' => (bool) $horario?->activo,
                'existing_id' => $horario?->id,
            ];
        }
    }

    public function toggleDia(string $dia): void
    {
        if (isset($this->horarios[$dia])) {
            $this->horarios[$dia]['activo'] = !$this->horarios[$dia]['activo'];
        }
    }

    public function updateHoraInicio(string $dia, string $hora): void
    {
        if (isset($this->horarios[$dia])) {
            $this->horarios[$dia]['hora_inicio'] = $hora;
        }
    }

    public function updateHoraFin(string $dia, string $hora): void
    {
        if (isset($this->horarios[$dia])) {
            $this->horarios[$dia]['hora_fin'] = $hora;
        }
    }

    public function save(): void
    {
        $this->validate();

        $profesional = Profesional::find($this->profesionalId);

        $existsOther = Profesional::where('tenant_id', $profesional->tenant_id)
            ->where('documento', $this->documento)
            ->where('id', '!=', $this->profesionalId)
            ->exists();

        if ($existsOther) {
            session()->flash('error', 'Ya existe otro profesional con este documento.');
            return;
        }

        $profesional->update([
            'nombre' => strtoupper($this->nombre),
            'apellido' => strtoupper($this->apellido),
            'especialidad' => $this->especialidad ? strtoupper($this->especialidad) : null,
            'numero_licencia' => $this->numero_licencia,
            'documento' => $this->documento,
            'email' => $this->email,
            'activo' => $this->activo,
        ]);

        foreach ($this->horarios as $h) {
            $data = [
                'dia_semana' => $h['dia_semana'],
                'hora_inicio' => $h['hora_inicio'],
                'hora_fin' => $h['hora_fin'],
                'activo' => $h['activo'],
            ];

            if (isset($h['existing_id'])) {
                $horario = HorarioProfesional::find($h['existing_id']);
                if ($horario) {
                    if (!$h['activo'] || $h['hora_inicio'] >= $h['hora_fin']) {
                        $horario->delete();
                    } else {
                        $horario->update($data);
                    }
                }
            } elseif ($h['activo'] && $h['hora_inicio'] < $h['hora_fin']) {
                HorarioProfesional::create(array_merge(['profesional_id' => $this->profesionalId], $data));
            }
        }

        session()->flash('success', 'Profesional actualizado exitosamente.');
        redirect()->route('profesionales.index');
    }

    public function render()
    {
        return view('livewire.profesional-edit');
    }
}