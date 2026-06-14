<?php

namespace App\Livewire;

use App\Models\Insumo;
use App\Models\CategoriaInsumo;
use App\Models\CategoriaInsumoAttribute;
use App\Models\InsumoAttributeValue;
use Livewire\Component;
use Livewire\Attributes\Rule;

class InsumoCreate extends Component
{
    #[Rule('required|string|max:255')]
    public $nombre = '';

    #[Rule('required|string|max:50')]
    public $codigo_sku = '';

    public $categoria_id = '';
    public $newCategoryNombre = '';
    public $activeTab = 'insumo';

    #[Rule('required|numeric|min:0')]
    public $stock_actual = 0;

    #[Rule('required|numeric|min:0')]
    public $stock_minimo = 0;

    #[Rule('required|string|max:20')]
    public $unidad_medida = 'unidad';

    public $attrValues = []; // Stores values: [attribute_id => value]

    public function updatedCategoriaId($value)
    {
        $this->attrValues = [];
    }

    public function saveCategory()
    {
        $this->validate(['newCategoryNombre' => 'required|string|max:255']);

        $category = CategoriaInsumo::firstOrCreate(
            [
                'tenant_id' => auth()->user()->tenant_id ?? 1,
                'nombre' => trim($this->newCategoryNombre),
            ],
            [
                'descripcion' => 'Categoría creada desde la gestión de insumos.',
            ]
        );

        $this->newCategoryNombre = '';
        $this->activeTab = 'insumo';
        $this->categoria_id = $category->id;
        
        session()->flash('message', 'Categoría creada exitosamente.');
    }

    public function save()
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'codigo_sku' => 'required|string|max:50',
            'categoria_id' => 'required|exists:categorias_insumos,id',
            'stock_actual' => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
            'unidad_medida' => 'required|string|max:20',
        ];

        $this->validate($rules);

        $insumo = Insumo::create([
            'tenant_id' => auth()->user()->tenant_id ?? 1,
            'nombre' => $this->nombre,
            'codigo_sku' => $this->codigo_sku,
            'categoria_id' => $this->categoria_id,
            'stock_actual' => $this->stock_actual,
            'stock_minimo' => $this->stock_minimo,
            'unidad_medida' => $this->unidad_medida,
        ]);

        // Save dynamic attributes
        foreach ($this->attrValues as $attributeId => $value) {
            if (!empty($value)) {
                InsumoAttributeValue::create([
                    'insumo_id' => $insumo->id,
                    'attribute_id' => $attributeId,
                    'valor' => $value,
                ]);
            }
        }

        session()->flash('message', 'Insumo registrado exitosamente.');
        return redirect()->route('insumos.index');
    }

    public function render()
    {
        $categories = CategoriaInsumo::all();
        $categoryAttributes = [];

        if ($this->categoria_id) {
            $categoryAttributes = CategoriaInsumoAttribute::where('categoria_id', $this->categoria_id)->get();
        }

        return view('livewire.insumo-create', [
            'categories' => $categories,
            'categoryAttributes' => $categoryAttributes
        ]);
    }
}
