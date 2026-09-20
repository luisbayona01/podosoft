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

    // Modal para enviar factura por WhatsApp cuando el paciente no tiene teléfono
    public $whatsappFacturaId = null;
    public $whatsappPhone = '';

    public function enviarWhatsApp($id)
    {
        $factura = Factura::with('paciente')->findOrFail($id);
        $phone = $factura->paciente?->telefono;

        if ($phone) {
            \App\Jobs\SendFacturaPdf::dispatch($factura->id, $phone);
            session()->flash('message', 'Factura ' . $factura->numero_factura . ' enviada por WhatsApp.');
            return;
        }

        // El paciente no tiene teléfono: solicitar el número
        $this->whatsappFacturaId = $factura->id;
        $this->whatsappPhone = '';
    }

    public function confirmarEnvioWhatsApp()
    {
        $this->validate([
            'whatsappPhone' => 'required|string|min:7|max:20',
        ]);

        $factura = Factura::with('paciente')->findOrFail($this->whatsappFacturaId);

        // Si hay paciente registrado, guardar el teléfono para futuros envíos
        if ($factura->paciente) {
            $factura->paciente->update(['telefono' => $this->whatsappPhone]);
        }

        \App\Jobs\SendFacturaPdf::dispatch($factura->id, $this->whatsappPhone);

        $this->reset(['whatsappFacturaId', 'whatsappPhone']);
        session()->flash('message', 'Factura ' . $factura->numero_factura . ' enviada por WhatsApp.');
    }

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
