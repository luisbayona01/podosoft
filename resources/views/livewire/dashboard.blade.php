<div class="p-6 space-y-6 bg-slate-50 min-h-screen">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Panel de Control</h1>
            <p class="text-slate-500 text-sm">Bienvenido de vuelta. Aquí tienes el resumen de tu consultorio hoy.</p>
        </div>
        <livewire:stock-alerts />
    </div>
    
    <!-- Financial Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-6 bg-white rounded-2xl shadow-sm border border-slate-200 flex items-center space-x-4">
            <div class="p-3 bg-emerald-100 text-emerald-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Ingresos Hoy</p>
                <p class="text-2xl font-bold text-slate-900">${{ number_format($metrics['income_today'], 2) }}</p>
            </div>
        </div>
        <div class="p-6 bg-white rounded-2xl shadow-sm border border-slate-200 flex items-center space-x-4">
            <div class="p-3 bg-blue-100 text-blue-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Ingresos Mes</p>
                <p class="text-2xl font-bold text-slate-900">${{ number_format($metrics['income_month'], 2) }}</p>
            </div>
        </div>
        <div class="p-6 bg-white rounded-2xl shadow-sm border border-slate-200 flex items-center space-x-4">
            <div class="p-3 bg-indigo-100 text-indigo-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Citas Hoy</p>
                <p class="text-2xl font-bold text-slate-900">{{ $metrics['appointments_today'] }}</p>
            </div>
        </div>
        <div class="p-6 bg-white rounded-2xl shadow-sm border border-slate-200 flex items-center space-x-4">
            <div class="p-3 bg-amber-100 text-amber-600 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Pendientes</p>
                <p class="text-2xl font-bold text-slate-900">{{ $metrics['appointments_pending'] }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- General Metrics -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
                <h3 class="text-lg font-bold text-slate-900">Estado General</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg">
                        <span class="text-sm text-slate-600">Total Pacientes</span>
                        <span class="font-bold text-slate-900">{{ $totalPatients }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                        <span class="text-sm text-red-600">Pacientes en Riesgo</span>
                        <span class="font-bold text-red-700">{{ $riskPatients }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg">
                        <span class="text-sm text-slate-600">Pacientes Atendidos Hoy</span>
                        <span class="font-bold text-slate-900">{{ $metrics['patients_today'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg">
                        <span class="text-sm text-slate-600">Pagos Registrados</span>
                        <span class="font-bold text-slate-900">{{ $metrics['total_payments'] }}</span>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-indigo-600 rounded-2xl shadow-sm text-white flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80 font-medium">Acceso Rápido</p>
                    <p class="text-lg font-bold">Registrar Paciente</p>
                </div>
                <a href="{{ route('patients.create') }}" class="p-2 bg-white text-indigo-600 rounded-lg hover:bg-indigo-50 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </a>
            </div>
        </div>

        <!-- Charts / Visuals -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <h3 class="text-lg font-bold text-slate-900 mb-6">Distribución de Ingresos por Método</h3>
                <div class="flex flex-wrap gap-4">
                    @foreach($chart_income_method as $item)
                        <div class="flex items-center gap-2 bg-slate-50 px-3 py-2 rounded-lg border border-slate-100">
                            <span class="text-xs font-medium text-slate-500">{{ $item->metodo_pago }}</span>
                            <span class="text-sm font-bold text-slate-900">${{ number_format($item->total, 2) }}</span>
                        </div>
                    @endforeach
                    @if($chart_income_method->isEmpty())
                        <p class="text-slate-400 italic text-sm">No hay datos de pagos registrados.</p>
                    @endif
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <h3 class="text-lg font-bold text-slate-900 mb-6">Ingresos por Servicio</h3>
                <div class="space-y-4">
                    @foreach($chart_income_service as $item)
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs font-medium text-slate-600">
                                <span>{{ $item->nombre }}</span>
                                <span>${{ number_format($item->total, 2) }}</span>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-indigo-500 h-full" style="width: {{ ($item->total / ($chart_income_service->sum('total') ?: 1)) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                    @if($chart_income_service->isEmpty())
                        <p class="text-slate-400 italic text-sm">No hay datos de ingresos por servicio.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

