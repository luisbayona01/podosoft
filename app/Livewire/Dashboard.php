<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\Cita;

class Dashboard extends Component
{
    public function render()
    {
        $tenant_id = auth()->user()->tenant_id ?? 1;
        $today = now()->toDateString();
        $startOfWeek = now()->startOfWeek()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();

        return view('livewire.dashboard', [
            'totalPatients' => Paciente::where('tenant_id', $tenant_id)->count(),
            'riskPatients' => Paciente::where('tenant_id', $tenant_id)->whereHas('antecedents', function($q) {
                $q->where('is_risk', true);
            })->count(),
            'metrics' => [
                'income_today' => Pago::where('tenant_id', $tenant_id)->whereDate('fecha_pago', $today)->sum('monto_final'),
                'income_week' => Pago::where('tenant_id', $tenant_id)->whereDate('fecha_pago', '>=', $startOfWeek)->sum('monto_final'),
                'income_month' => Pago::where('tenant_id', $tenant_id)->whereDate('fecha_pago', '>=', $startOfMonth)->sum('monto_final'),
                'total_payments' => Pago::where('tenant_id', $tenant_id)->count(),
                'appointments_today' => Cita::where('tenant_id', $tenant_id)->whereDate('fecha_hora', $today)->count(),
                'patients_today' => Cita::where('tenant_id', $tenant_id)->whereDate('fecha_hora', $today)->where('estado', 'Completada')->count(),
                'appointments_pending' => Cita::where('tenant_id', $tenant_id)->where('estado', 'Pendiente')->count(),
                'appointments_cancelled' => Cita::where('tenant_id', $tenant_id)->where('estado', 'Cancelada')->count(),
            ],
            'chart_income_day' => Pago::where('tenant_id', $tenant_id)
                ->where('fecha_pago', '>=', now()->subDays(30))
                ->selectRaw('DATE(fecha_pago) as date, SUM(monto_final) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'chart_income_service' => Pago::where('pagos.tenant_id', $tenant_id)
                ->join('servicios', 'pagos.servicio_id', '=', 'servicios.id')
                ->selectRaw('servicios.nombre, SUM(pagos.monto_final) as total')
                ->groupBy('servicios.nombre')
                ->get(),
            'chart_income_method' => Pago::where('tenant_id', $tenant_id)
                ->selectRaw('metodo_pago, SUM(monto_final) as total')
                ->groupBy('metodo_pago')
                ->get(),
        ]);
    }
}
