<?php

namespace App\Livewire;

use App\Models\Pago;
use App\Models\Cita;
use App\Models\Servicio;
use Livewire\Component;
use Livewire\WithPagination;

class FinancialsReport extends Component
{
    use WithPagination;

    public $period = 'month'; // day, week, month
    public $filterMethod = '';
    public $filterService = '';

    public function render()
    {
        $tenant_id = auth()->user()->tenant_id ?? 1;
        $query = Pago::where('tenant_id', $tenant_id);

        // Period Filter using fecha_pago
        if ($this->period === 'day') {
            $query->whereDate('fecha_pago', today());
        } elseif ($this->period === 'week') {
            $query->whereBetween('fecha_pago', [now()->startOfWeek(), now()->endOfWeek()]);
        } else {
            $query->whereMonth('fecha_pago', now()->month)
                  ->whereYear('fecha_pago', now()->year);
        }

        if ($this->filterMethod) {
            $query->where('metodo_pago', $this->filterMethod);
        }

        if ($this->filterService) {
            $query->where('servicio_id', $this->filterService);
        }

        $payments = $query->with(['cita.paciente', 'servicio'])
            ->orderBy('fecha_pago', 'desc')
            ->paginate(15);

        $totalIncome = $query->sum('monto_final');

        // Income by method for the selected period
        $byMethod = Pago::where('tenant_id', $tenant_id)
            ->when($this->period === 'day', fn($q) => $q->whereDate('fecha_pago', today()))
            ->when($this->period === 'week', fn($q) => $q->whereBetween('fecha_pago', [now()->startOfWeek(), now()->endOfWeek()]))
            ->when($this->period === 'month', fn($q) => $q->whereMonth('fecha_pago', now()->month)->whereYear('fecha_pago', now()->year))
            ->selectRaw('metodo_pago, sum(monto_final) as total')
            ->groupBy('metodo_pago')
            ->get();

        return view('livewire.financials-report', [
            'payments' => $payments,
            'totalIncome' => $totalIncome,
            'byMethod' => $byMethod,
            'methods' => ['Efectivo', 'Transferencia bancaria', 'Nequi', 'Daviplata', 'Tarjeta débito', 'Tarjeta crédito'],
            'servicios' => Servicio::where('tenant_id', $tenant_id)->get()
        ]);
    }
}
