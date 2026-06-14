<?php

namespace App\Livewire;

use App\Models\CategoriaInsumo;
use App\Models\CategoriaInsumoAttribute;
use Livewire\Component;
use Livewire\Attributes\Rule;

class CategoryAttributeManager extends Component
{
    public $categoria_id;
    
    #[Rule('required|string|max:255')]
    public $nombre_atributo = '';
    
    #[Rule('required|in:text,number,boolean')]
    public $tipo_atributo = 'text';

    public function mount($categoria_id)
    {
        $this->categoria_id = $categoria_id;
    }

    public function saveAttribute()
    {
        $this->validate();

        CategoriaInsumoAttribute::create([
            'categoria_id' => $this->categoria_id,
            'tenant_id' => auth()->user()->tenant_id ?? 1,
            'nombre' => $this->nombre_atributo,
            'tipo' => $this->tipo_atributo,
        ]);

        $this->reset(['nombre_atributo', 'tipo_atributo']);
        session()->flash('message', 'Característica agregada correctamente.');
    }

    public function deleteAttribute($id)
    {
        CategoriaInsumoAttribute::findOrFail($id)->delete();
        session()->flash('message', 'Característica eliminada.');
    }

    public function render()
    {
        $categoria = CategoriaInsumo::findOrFail($this->categoria_id);
        $attributes = CategoriaInsumoAttribute::where('categoria_id', $this->categoria_id)->get();

        return view('livewire.category-attribute-manager', [
            'categoria' => $categoria,
            'attributes' => $attributes
        ]);
    }
}
