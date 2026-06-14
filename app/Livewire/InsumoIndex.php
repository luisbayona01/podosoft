<?php

namespace App\Livewire;

use App\Models\Insumo;
use App\Models\CategoriaInsumo;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class InsumoIndex extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $category_id = '';
    public $csvFile;

    public function getStats()
    {
        $tenantId = auth()->user()->tenant_id ?? 1;
        return [
            'total_items' => Insumo::where('tenant_id', $tenantId)->count(),
            'low_stock' => Insumo::where('tenant_id', $tenantId)->whereColumn('stock_actual', '<=', 'stock_minimo')->count(),
            'categories' => CategoriaInsumo::where('tenant_id', $tenantId)->count(),
        ];
    }

    public function importCsv()
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt|max:1024',
        ]);

        $file = $this->csvFile->getRealPath();
        $handle = fopen($file, 'r');
        
        fgetcsv($handle);

        $importedCount = 0;
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            if (count($data) >= 6) {
                Insumo::updateOrCreate(
                    ['codigo_sku' => $data[1]],
                    [
                        'tenant_id' => auth()->user()->tenant_id ?? 1,
                        'nombre' => $data[0],
                        'categoria_id' => $data[2],
                        'stock_actual' => $data[3],
                        'stock_minimo' => $data[4],
                        'unidad_medida' => $data[5],
                    ]
                );
                $importedCount++;
            }
        }
        fclose($handle);

        session()->flash('message', "Se han importado/actualizado {$importedCount} insumos correctamente.");
        $this->reset('csvFile');
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id ?? 1;
        $insumos = Insumo::where('tenant_id', $tenantId)
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('codigo_sku', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->category_id, function($q) {
                $q->where('categoria_id', $this->category_id);
            })
            ->paginate(10);

        return view('livewire.insumo-index', [
            'insumos' => $insumos,
            'categories' => CategoriaInsumo::where('tenant_id', $tenantId)->get(),
            'stats' => $this->getStats()
        ]);
    }
}
