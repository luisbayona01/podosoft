<?php

namespace App\Livewire;

use App\Models\Pago;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Servicio;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;

class PatientBilling extends Component
{
    use WithPagination;

    public $patientId;
    public $showForm = false;
    public $pagoId = null;

    // Form fields
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

    public function mount($patientId)
    {
        $this->patientId = $patientId;
    }

    public function openCreateForm()
    {
        $this->resetForm();
        $this->showForm = true;
        
        // Try to find the last pending appointment to pre-fill
        $cita = Cita::where('paciente_id', $this->patientId)
            ->whereNotIn('estado', ['cancelada', 'no_asistio', 'pagada'])
            ->orderBy('fecha_hora', 'desc')
            ->first();

        if ($cita) {
            $this->cita_id = $cita->id;
            $servicio = $cita->servicios->first();
            if ($servicio) {
                $this->servicio_id = $servicio->id;
                $this->valor = $servicio->pivot->precio_aplicado ?? $servicio->precio;
                $this->monto_final = $this->valor;
            }
        }
        $this->fecha_pago = now()->format('Y-m-d');
    }

    public function openEditForm($id)
    {
        $this->resetForm();
        $pago = Pago::findOrFail($id);
        $this->pagoId = $id;
        $this->cita_id = $pago->cita_id;
        $this->servicio_id = $pago->servicio_id;
        $this->fecha_pago = $pago->fecha_pago->format('Y-m-d');
        $this->valor = $pago->valor;
        $this->descuento = $pago->descuento;
        $this->monto_final = $pago->monto_final;
        $this->metodo_pago = $pago->metodo_pago;
        $this->observaciones = $pago->observaciones;
        $this->showForm = true;
    }

    public function resetForm()
    {
        $this->pagoId = null;
        $this->cita_id = '';
        $this->servicio_id = '';
        $this->fecha_pago = now()->format('Y-m-d');
        $this->valor = 0;
        $this->descuento = 0;
        $this->monto_final = 0;
        $this->metodo_pago = 'Efectivo';
        $this->observaciones = '';
        $this->showForm = false;
    }

    public function updatedValor() { $this->calculateTotal(); }
    public function updatedDescuento() { $this->calculateTotal(); }

    private function calculateTotal()
    {
        $this->monto_final = max(0, $this->valor - $this->descuento);
    }

    public function save()
    {
        $this->validate();

        if ($this->cita_id) {
            $alreadyPaid = Pago::where('cita_id', $this->cita_id)
                ->where('estado', '!=', 'anulado')
                ->when($this->pagoId, fn ($q) => $q->where('id', '!=', $this->pagoId))
                ->exists();

            if ($alreadyPaid) {
                session()->flash('error', 'Esta cita ya tiene un pago registrado.');
                return;
            }
        }

        $data = [
            'tenant_id' => auth()->user()->tenant_id ?? 1,
            'paciente_id' => $this->patientId,
            'cita_id' => $this->cita_id,
            'servicio_id' => $this->servicio_id,
            'fecha_pago' => $this->fecha_pago,
            'valor' => $this->valor,
            'descuento' => $this->descuento,
            'monto_final' => $this->monto_final,
            'metodo_pago' => $this->metodo_pago,
            'observaciones' => $this->observaciones,
            'comprobante_numero' => $this->pagoId ? Pago::find($this->pagoId)->comprobante_numero : 'REC-' . strtoupper(uniqid()),
            'estado' => 'pagado',
        ];

        if ($this->pagoId) {
            Pago::findOrFail($this->pagoId)->update($data);
            session()->flash('message', 'Pago actualizado correctamente.');
        } else {
            Pago::create($data);
            if ($this->cita_id) {
                Cita::where('id', $this->cita_id)->update(['estado' => 'pagada']);
            }
            session()->flash('message', 'Pago registrado exitosamente.');
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function voidPayment($id)
    {
        $pago = Pago::findOrFail($id);
        $pago->update(['estado' => 'anulado']);

        if ($pago->cita_id) {
            $hasOtherPayment = Pago::where('cita_id', $pago->cita_id)
                ->where('estado', '!=', 'anulado')
                ->where('id', '!=', $pago->id)
                ->exists();

            if (! $hasOtherPayment) {
                Cita::where('id', $pago->cita_id)
                    ->where('estado', 'pagada')
                    ->update(['estado' => 'confirmada']);
            }
        }

        session()->flash('message', 'Pago anulado correctamente.');
    }

    public function render()
    {
        return view('livewire.patient-billing', [
            'payments' => Pago::where('paciente_id', $this->patientId)
                ->with(['cita', 'servicio'])
                ->orderBy('fecha_pago', 'desc')
                ->paginate(10),
            'servicios' => Servicio::where('tenant_id', auth()->user()->tenant_id ?? 1)->get(),
            'metodosPago' => ['Efectivo', 'Transferencia bancaria', 'Nequi', 'Daviplata', 'Tarjeta débito', 'Tarjeta crédito']
        ]);
    }
}
