<?php

namespace App\Livewire;

use App\Models\Insumo;
use Livewire\Component;

class StockAlerts extends Component
{
    public function render()
    {
        $lowStockItems = Insumo::where('tenant_id', auth()->user()->tenant_id ?? 1)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->get();

        return view('livewire.stock-alerts', [
            'lowStockItems' => $lowStockItems
        ]);
    }
}
