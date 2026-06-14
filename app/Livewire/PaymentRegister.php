<?php

namespace App\Livewire;

use App\Models\Pago;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Servicio;
use Livewire\Component;
use Livewire\Attributes\Rule;

class PaymentRegister extends Component
{
    public $citaId;
    public $pagoId = null;

    #[Rule('required|exists:pacientes,id')]
    public $paciente_id;

    #[Rule('required|exists:citas,id')]
    public $cita_id;

    #[Rule('required|exists:servicios,id')]
    public $servicio_id;

    #[Rule('required|date')]
    public $fecha_pago;

    #[Rule('required|numeric|min:0')]
    public $valor = 0;

    #[Rule('numeric|min:0')]
    public $descuento = 0;

    #[Rule('required|numeric|min:0')]
    public $monto_final = 0;

    #[Rule('required|string')]
    public $metodo_pago = 'Efectivo';

    public $observaciones = '';

    public function mount($id = null, $citaId = null)
    {
        $this->pagoId = $id;
        $this->citaId = $citaId;

        if ($id) {
            $pago = Pago::findOrFail($id);
            $this->fillPago($pago);
        } elseif ($citaId) {
            $this->prepareFromCita($citaId);
        }
    }

    private function fillPago(Pago $pago)
    {
        $this->paciente_id = $pago->paciente_id;
        $this->cita_id = $pago->cita_id;
        $this->servicio_id = $pago->servicio_id;
        $this->fecha_pago = $pago->fecha_pago->format('Y-m-d');
        $this->valor = $pago->valor;
        $this->descuento = $pago->descuento;
        $this->monto_final = $pago->monto_final;
        $this->metodo_pago = $pago->metodo_pago;
        $this->observaciones = $pago->observaciones;
    }

    private function prepareFromCita($citaId)
    {
        $cita = Cita::with(['paciente', 'servicios'])->findOrFail($citaId);
        $this->paciente_id = $cita->paciente_id;
        $this->cita_id = $cita->id;
        $this->fecha_pago = now()->format('Y-m-d');
        
        // Use the first service of the cita as default
        $servicio = $cita->servicios->first();
        if ($servicio) {
            $this->servicio_id = $servicio->id;
            $this->valor = $servicio->pivot->precio_aplicado ?? $servicio->precio;
            $this->monto_final = $this->valor;
        }
    }

    public function updatedValor()
    {
        $this->calculateTotal();
    }

    public function updatedDescuento()
    {
        $this->calculateTotal();
    }

    private function calculateTotal()
    {
        $this->monto_final = max(0, $this->valor - $this->descuento);
    }

    public function save()
    {
        $this->validate();

        $data = [
            'tenant_id' => auth()->user()->tenant_id ?? 1,
            'paciente_id' => $this->paciente_id,
            'cita_id' => $this->cita_id,
            'servicio_id' => $this->servicio_id,
            'fecha_pago' => $this->fecha_pago,
            'valor' => $this->valor,
            'descuento' => $this->descuento,
            'monto_final' => $this->monto_final,
            'metodo_pago' => $this->metodo_pago,
            'observaciones' => $this->observaciones,
            'comprobante_numero' => 'REC-' . strtoupper(uniqid()),
            'estado' => 'pagado',
        ];

        if ($this->pagoId) {
            Pago::findOrFail($this->pagoId)->update($data);
            session()->flash('message', 'Pago actualizado correctamente.');
        } else {
            Pago::create($data);
            session()->flash('message', 'Pago registrado exitosamente.');
        }

        return redirect()->route('appointments.index');
    }

    public function void()
    {
        if (!$this->pagoId) return;
        
        $pago = Pago::findOrFail($this->pagoId);
        $pago->update(['estado' => 'anulado']);
        session()->flash('message', 'Pago anulado correctamente.');
        return redirect()->route('appointments.index');
    }

    public function render()
    {
        return view('livewire.payment-register', [
            'servicios' => Servicio::where('tenant_id', auth()->user()->tenant_id ?? 1)->get(),
            'metodosPago' => [
                'Efectivo', 'Transferencia bancaria', 'Nequi', 'Daviplata', 'Tarjeta débito', 'Tarjeta crédito'
            ]
        ]);
    }
}
