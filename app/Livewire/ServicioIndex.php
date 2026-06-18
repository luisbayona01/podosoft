<?php

namespace App\Livewire;

use App\Models\Servicio;
use Livewire\Component;
use Livewire\WithPagination;

class ServicioIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    public function getStats()
    {
        $tenantId = auth()->user()->tenant_id ?? 1;
        return [
            'total' => Servicio::where('tenant_id', $tenantId)->count(),
            'active' => Servicio::where('tenant_id', $tenantId)->where('activo', true)->count(),
            'inactive' => Servicio::where('tenant_id', $tenantId)->where('activo', false)->count(),
        ];
    }

    public function toggleActive($id)
    {
        $servicio = Servicio::find($id);
        if ($servicio) {
            $servicio->activo = !$servicio->activo;
            $servicio->save();
            session()->flash('message', $servicio->activo ? 'Servicio activado.' : 'Servicio desactivado.');
        }
    }

    public function delete($id)
    {
        $servicio = Servicio::find($id);
        if ($servicio) {
            $servicio->delete();
            session()->flash('success', 'Servicio eliminado exitosamente.');
        }
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id ?? 1;

        $servicios = Servicio::where('tenant_id', $tenantId)
            ->when($this->search, function ($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.servicio-index', [
            'servicios' => $servicios,
            'stats' => $this->getStats()
        ]);
    }
}