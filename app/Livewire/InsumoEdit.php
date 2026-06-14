<?php

namespace App\Livewire;

use App\Models\Insumo;
use Livewire\Component;

class InsumoEdit extends Component
{
    public $insumoId;
    public $nombre;
    public $codigo_sku;
    public $categoria_id;
    public $stock_actual;
    public $stock_minimo;
    public $unidad_medida;

    public function mount($id)
    {
        $insumo = Insumo::findOrFail($id);
        $this->insumoId = $insumo->id;
        $this->nombre = $insumo->nombre;
        $this->codigo_sku = $insumo->codigo_sku;
        $this->categoria_id = $insumo->categoria_id;
        $this->stock_actual = $insumo->stock_actual;
        $this->stock_minimo = $insumo->stock_minimo;
        $this->unidad_medida = $insumo->unidad_medida;
    }

    public function save()
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'codigo_sku' => 'required|string|max:50',
            'categoria_id' => 'required|exists:categoria_insumos,id',
            'stock_actual' => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
            'unidad_medida' => 'required|string|max:20',
        ]);

        Insumo::find($this->insumoId)->update([
            'nombre' => $this->nombre,
            'codigo_sku' => $this->codigo_sku,
            'categoria_id' => $this->categoria_id,
            'stock_actual' => $this->stock_actual,
            'stock_minimo' => $this->stock_minimo,
            'unidad_medida' => $this->unidad_medida,
        ]);

        session()->flash('message', 'Insumo actualizado exitosamente.');
        return redirect()->route('insumos.index');
    }

    public function render()
    {
        return view('livewire.insumo-edit', [
            'categories' => \App\Models\CategoriaInsumo::all()
        ]);
    }
}
