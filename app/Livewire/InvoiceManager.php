<?php

namespace App\Livewire;

use App\Models\Cita;
use App\Models\Factura;
use App\Models\Insumo;
use App\Models\Kardex;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\Servicio;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class InvoiceManager extends Component
{
    public $facturaId = null;

    // Cliente (opcional)
    public $buscarCliente = '';
    public $paciente_id = null;
    public $sin_paciente = false;
    public $cliente_nombre = '';
    public $cliente_documento = '';
    public $cliente_telefono = '';

    // Origen (opcional)
    public $cita_id = null;

    // Ítems de la factura
    public $items = [];
    public $nuevoServicioId = '';
    public $nuevoInsumoId = '';
    public $impuestos = 0;

    // Pago
    public $registrar_pago = true;
    public $metodo_pago = 'Efectivo';
    public $observaciones = '';

    public function mount($citaId = null)
    {
        $citaId = $citaId ?? request()->query('citaId');

        if ($citaId) {
            $cita = Cita::with(['paciente', 'servicios'])->findOrFail($citaId);
            $this->cita_id = $cita->id;
            $this->paciente_id = $cita->paciente_id;

            foreach ($cita->servicios as $servicio) {
                $this->items[] = $this->makeItem('servicio', $servicio->id);
            }
        } elseif ($pacienteId = request()->query('pacienteId')) {
            $this->paciente_id = Paciente::where('tenant_id', auth()->user()->tenant_id ?? 1)
                ->findOrFail($pacienteId)->id;
        }
    }

    // ------------------------------------------------------------------
    // Cliente
    // ------------------------------------------------------------------

    public function selectPaciente($id)
    {
        $this->paciente_id = $id;
        $this->sin_paciente = false;
        $this->buscarCliente = '';
        $this->cliente_nombre = '';
        $this->cliente_documento = '';
    }

    public function clearPaciente()
    {
        $this->paciente_id = null;
    }

    public function updatedSinPaciente($value)
    {
        if ($value) {
            $this->paciente_id = null;
            $this->cita_id = null;
        }
    }

    // ------------------------------------------------------------------
    // Origen: cita (solo referencia, autofila pero no obliga)
    // ------------------------------------------------------------------

    public function updatedCitaId($value)
    {
        if (!$value) {
            return;
        }

        $cita = Cita::with(['paciente', 'servicios'])->find($value);
        if (!$cita) {
            return;
        }

        // Autocompletar paciente si la factura no tiene uno
        if (!$this->paciente_id && !$this->sin_paciente) {
            $this->paciente_id = $cita->paciente_id;
        }

        // Agregar los servicios de la cita como ítems (precargue editable)
        foreach ($cita->servicios as $servicio) {
            $item = $this->makeItem('servicio', $servicio->id);
            $item['precio'] = (float) ($servicio->pivot->precio_aplicado ?? $servicio->precio);
            $this->items[] = $item;
        }
    }

    // ------------------------------------------------------------------
    // Ítems
    // ------------------------------------------------------------------

    private function makeItem(string $tipo, $id): array
    {
        if ($tipo === 'servicio') {
            $servicio = Servicio::findOrFail($id);
            return [
                'tipo' => 'servicio',
                'id' => $servicio->id,
                'descripcion' => $servicio->nombre,
                'cantidad' => 1,
                'precio' => (float) $servicio->precio,
                'descuento' => 0,
            ];
        }

        $insumo = Insumo::findOrFail($id);
        return [
            'tipo' => 'insumo',
            'id' => $insumo->id,
            'descripcion' => $insumo->nombre,
            'cantidad' => 1,
            'precio' => (float) ($insumo->precio_venta ?? 0),
            'descuento' => 0,
        ];
    }

    public function updatedNuevoServicioId($value)
    {
        if ($value) {
            $this->items[] = $this->makeItem('servicio', $value);
            $this->nuevoServicioId = '';
        }
    }

    public function updatedNuevoInsumoId($value)
    {
        if ($value) {
            $this->items[] = $this->makeItem('insumo', $value);
            $this->nuevoInsumoId = '';
        }
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    // ------------------------------------------------------------------
    // Totales (calculados en memoria; la fuente de verdad es la DB)
    // ------------------------------------------------------------------

    public function subtotal(): float
    {
        return collect($this->items)->sum(fn ($i) => (float) $i['cantidad'] * (float) $i['precio']);
    }

    public function descuentoTotal(): float
    {
        return collect($this->items)->sum(fn ($i) => (float) $i['descuento']);
    }

    public function total(): float
    {
        return max(0, $this->subtotal() - $this->descuentoTotal() + (float) $this->impuestos);
    }

    // ------------------------------------------------------------------
    // Guardar
    // ------------------------------------------------------------------

    public function save()
    {
        $this->validate([
            'paciente_id' => ['nullable', 'exists:pacientes,id', 'required_unless:sin_paciente,true'],
            'cliente_nombre' => ['nullable', 'string', 'max:255', 'required_if:sin_paciente,true'],
            'cliente_documento' => ['nullable', 'string', 'max:50'],
            'cliente_telefono' => ['nullable', 'string', 'min:7', 'max:20', 'required_if:sin_paciente,true'],
            'cita_id' => ['nullable', 'exists:citas,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.cantidad' => ['required', 'numeric', 'min:0.01'],
            'items.*.precio' => ['required', 'numeric', 'min:0'],
            'items.*.descuento' => ['nullable', 'numeric', 'min:0'],
            'impuestos' => ['nullable', 'numeric', 'min:0'],
            'metodo_pago' => ['required', 'string'],
        ], [
            'paciente_id.required_unless' => 'Seleccione un paciente o marque "Facturar sin paciente".',
            'cliente_nombre.required_if' => 'Ingrese el nombre del cliente.',
            'cliente_telefono.required_if' => 'Ingrese el número de WhatsApp del cliente.',
            'items.required' => 'Agregue al menos un servicio o producto a la factura.',
        ]);

        if ($this->cita_id && $this->registrar_pago) {
            $alreadyPaid = Pago::where('cita_id', $this->cita_id)->where('estado', '!=', 'anulado')->exists();
            if ($alreadyPaid) {
                session()->flash('error', 'Esta cita ya tiene un pago registrado.');
                return;
            }
        }

        // Validar stock de productos ANTES de persistir
        if ($this->registrar_pago) {
            foreach ($this->items as $item) {
                if ($item['tipo'] === 'insumo') {
                    $insumo = Insumo::find($item['id']);
                    if ($insumo && $insumo->stock_actual < $item['cantidad']) {
                        session()->flash('error', "Stock insuficiente para {$insumo->nombre} (disponible: {$insumo->stock_actual}).");
                        return;
                    }
                }
            }
        }

        $tenantId = auth()->user()->tenant_id ?? 1;

        // Cliente ocasional: registrar (o reutilizar) el paciente en la base
        // de datos con su teléfono, para que quede disponible en WhatsApp.
        if ($this->sin_paciente) {
            $paciente = Paciente::where('tenant_id', $tenantId)
                ->when($this->cliente_documento, fn ($q) => $q->where('documento', $this->cliente_documento))
                ->when(!$this->cliente_documento, fn ($q) => $q->where('telefono', $this->cliente_telefono))
                ->first();

            if (!$paciente) {
                [$nombre, $apellido] = array_pad(explode(' ', trim($this->cliente_nombre), 2), 2, '');
                $paciente = Paciente::create([
                    'tenant_id' => $tenantId,
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'documento' => $this->cliente_documento ?: null,
                    'telefono' => $this->cliente_telefono,
                ]);
            } else {
                // Actualizar datos básicos si estaban vacíos
                $paciente->fill(array_filter([
                    'telefono' => $paciente->telefono ?: $this->cliente_telefono,
                    'documento' => $paciente->documento ?: ($this->cliente_documento ?: null),
                ]));
                if ($paciente->isDirty()) {
                    $paciente->save();
                }
            }

            $this->paciente_id = $paciente->id;
        }

        $pacienteId = $this->paciente_id;
        $pagoId = null;
        $facturaId = null;

        DB::transaction(function () use ($tenantId, $pacienteId, &$pagoId, &$facturaId) {
            $factura = Factura::create([
                'tenant_id' => $tenantId,
                'paciente_id' => $pacienteId,
                'cita_id' => $this->cita_id,
                'cliente_nombre' => $this->sin_paciente ? $this->cliente_nombre : null,
                'cliente_documento' => $this->sin_paciente ? $this->cliente_documento : null,
                'numero_factura' => 'TMP-' . strtoupper(uniqid()),
                'subtotal' => 0,
                'descuento' => 0,
                'impuestos' => (float) $this->impuestos,
                'total' => 0,
                'fecha_emision' => now(),
                'estado' => 'borrador',
            ]);

            $factura->numero_factura = 'FAC-' . str_pad($factura->id, 6, '0', STR_PAD_LEFT);
            $factura->save();

            $facturaId = $factura->id;

            foreach ($this->items as $item) {
                $subtotal = max(0, ((float) $item['cantidad'] * (float) $item['precio']) - (float) $item['descuento']);
                $factura->items()->create([
                    'servicio_id' => $item['tipo'] === 'servicio' ? $item['id'] : null,
                    'insumo_id' => $item['tipo'] === 'insumo' ? $item['id'] : null,
                    'descripcion' => $item['descripcion'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'descuento' => $item['descuento'] ?? 0,
                    'subtotal' => $subtotal,
                ]);
            }

            $factura->refresh();
            $factura->recalcularTotales();

            if ($this->registrar_pago) {
                $pago = Pago::create([
                    'tenant_id' => $tenantId,
                    'factura_id' => $factura->id,
                    'paciente_id' => $pacienteId,
                    'cita_id' => $this->cita_id,
                    'servicio_id' => null,
                    'fecha_pago' => now()->format('Y-m-d'),
                    'valor' => $factura->subtotal,
                    'descuento' => $factura->descuento,
                    'monto_final' => $factura->total,
                    'metodo_pago' => $this->metodo_pago,
                    'observaciones' => $this->observaciones,
                    'comprobante_numero' => 'REC-' . strtoupper(uniqid()),
                    'estado' => 'pagado',
                ]);

                // Descontar inventario de los productos vendidos
                foreach ($factura->items as $item) {
                    if (!$item->insumo_id) {
                        continue;
                    }
                    $insumo = Insumo::lockForUpdate()->find($item->insumo_id);
                    $insumo->decrement('stock_actual', $item->cantidad);
                    Kardex::create([
                        'tenant_id' => $tenantId,
                        'insumo_id' => $insumo->id,
                        'tipo_movimiento' => 'salida',
                        'cantidad' => $item->cantidad,
                        'referencia' => 'Venta ' . $factura->numero_factura,
                        'fecha_movimiento' => now(),
                    ]);
                }

                if ($this->cita_id) {
                    Cita::where('id', $this->cita_id)->update(['estado' => 'pagada']);
                }

                $factura->update(['estado' => 'pagada']);

                $pagoId = $pago->id;
            }
        });

        if ($pagoId) {
            // Envío automático: misma factura térmica que el envío manual.
            $phone = $pacienteId ? (Paciente::find($pacienteId)?->telefono) : null;

            if ($phone && $facturaId) {
                \App\Jobs\SendFacturaPdf::dispatch($facturaId, $phone)->afterCommit();
            } else {
                \App\Jobs\SendPaymentReceiptPdf::dispatch($pagoId)->afterCommit();
            }
        }

        session()->flash('message', $this->registrar_pago ? 'Factura creada y pago registrado correctamente.' : 'Factura creada como borrador.');
        return redirect()->route('invoices.index');
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id ?? 1;

        $resultadosClientes = [];
        if (strlen($this->buscarCliente) >= 2 && !$this->paciente_id && !$this->sin_paciente) {
            $term = '%' . $this->buscarCliente . '%';
            $resultadosClientes = Paciente::where('tenant_id', $tenantId)
                ->where(function ($q) use ($term) {
                    $q->where('nombre', 'like', $term)
                        ->orWhere('apellido', 'like', $term)
                        ->orWhere('documento', 'like', $term);
                })
                ->limit(8)
                ->get();
        }

        return view('livewire.invoice-manager', [
            'resultadosClientes' => $resultadosClientes,
            'pacienteSeleccionado' => $this->paciente_id ? Paciente::find($this->paciente_id) : null,
            'citas' => Cita::where('tenant_id', $tenantId)
                ->whereNotIn('estado', ['cancelada', 'no_asistio'])
                ->with('paciente')
                ->orderBy('fecha_hora', 'desc')
                ->limit(50)
                ->get(),
            'servicios' => Servicio::where('tenant_id', $tenantId)->get(),
            'insumos' => Insumo::where('tenant_id', $tenantId)->orderBy('nombre')->get(),
            'metodosPago' => ['Efectivo', 'Transferencia bancaria', 'Nequi', 'Daviplata', 'Tarjeta débito', 'Tarjeta crédito'],
        ]);
    }
}
