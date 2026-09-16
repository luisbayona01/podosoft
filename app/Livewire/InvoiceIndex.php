<?php

namespace App\Livewire;

use App\Models\Cita;
use App\Models\Factura;
use App\Models\Insumo;
use App\Models\Kardex;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $estado = '';

    public function anular($id)
    {
        $factura = Factura::with('items', 'pagos')->findOrFail($id);

        if ($factura->estado === 'anulada') {
            return;
        }

        DB::transaction(function () use ($factura) {
            $tenantId = auth()->user()->tenant_id ?? 1;

            // Anular pagos asociados
            $factura->pagos()->update(['estado' => 'anulado']);

            // Revertir inventario de los productos vendidos
            foreach ($factura->items as $item) {
                if (!$item->insumo_id) {
                    continue;
                }
                $insumo = Insumo::lockForUpdate()->find($item->insumo_id);
                if ($insumo) {
                    $insumo->increment('stock_actual', $item->cantidad);
                    Kardex::create([
                        'tenant_id' => $tenantId,
                        'insumo_id' => $insumo->id,
                        'tipo_movimiento' => 'entrada',
                        'cantidad' => $item->cantidad,
                        'referencia' => 'Anulación ' . $factura->numero_factura,
                        'fecha_movimiento' => now(),
                    ]);
                }
            }

            // Si la cita quedó marcada como pagada por esta factura, revertirla
            if ($factura->cita_id) {
                Cita::where('id', $factura->cita_id)
                    ->where('estado', 'pagada')
                    ->update(['estado' => 'confirmada']);
            }

            $factura->update(['estado' => 'anulada']);
        });

        session()->flash('message', 'Factura ' . $factura->numero_factura . ' anulada correctamente.');
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id ?? 1;

        $facturas = Factura::where('tenant_id', $tenantId)
            ->with(['paciente', 'cita', 'items'])
            ->when($this->estado, fn ($q) => $q->where('estado', $this->estado))
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('numero_factura', 'like', $term)
                        ->orWhere('cliente_nombre', 'like', $term)
                        ->orWhereHas('paciente', fn ($p) => $p->where('nombre', 'like', $term)->orWhere('apellido', 'like', $term));
                });
            })
            ->orderBy('fecha_emision', 'desc')
            ->paginate(15);

        return view('livewire.invoice-index', compact('facturas'));
    }
}
