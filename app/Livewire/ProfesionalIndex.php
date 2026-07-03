<?php

namespace App\Livewire;

use App\Models\Profesional;
use Livewire\Component;
use Livewire\WithPagination;

class ProfesionalIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $search = '';
    public string $sortField = 'nombre';
    public bool $sortAsc = true;
    public string $filterActivo = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
        }
        $this->sortField = $field;
    }

    public function toggleActivo(Profesional $profesional): void
    {
        $profesional->update(['activo' => !$profesional->activo]);
        session()->flash('success', $profesional->activo ? 'Profesional activado.' : 'Profesional inactivado.');
    }

    public function delete(Profesional $profesional): void
    {
        if ($profesional->citas()->count() > 0) {
            session()->flash('error', 'No se puede eliminar un profesional con citas asociadas.');
            return;
        }

        $profesional->delete();
        session()->flash('success', 'Profesional eliminado.');
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id ?? 1;

        $query = Profesional::where('tenant_id', $tenantId)
            ->when($this->search, fn($q) => $q->where('nombre', 'like', '%' . $this->search . '%')
                ->orWhere('apellido', 'like', '%' . $this->search . '%')
                ->orWhere('especialidad', 'like', '%' . $this->search . '%')
                ->orWhere('documento', 'like', '%' . $this->search . '%'))
            ->when($this->filterActivo !== '', fn($q) => $q->where('activo', $this->filterActivo === '1'));

        $profesionales = $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')->paginate(10);

        return view('livewire.profesional-index', [
            'profesionales' => $profesionales,
        ]);
    }
}