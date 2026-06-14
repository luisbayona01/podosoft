<div class="p-6 bg-slate-50 min-h-screen">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Reporte de Ingresos</h2>
            <p class="text-slate-500 text-sm">Análisis detallado de la caja y flujo de efectivo del consultorio.</p>
        </div>
        <div class="flex bg-white p-1 rounded-xl border border-slate-200 shadow-sm">
            <button wire:click="$set('period', 'day')" 
                class="px-4 py-2 text-xs font-semibold rounded-lg transition-all {{ $period === 'day' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100' }}">
                Hoy
            </button>
            <button wire:click="$set('period', 'week')" 
                class="px-4 py-2 text-xs font-semibold rounded-lg transition-all {{ $period === 'week' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100' }}">
                Semana
            </button>
            <button wire:click="$set('period', 'month')" 
                class="px-4 py-2 text-xs font-semibold rounded-lg transition-all {{ $period === 'month' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100' }}">
                Mes
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Main Total Card -->
        <div class="lg:col-span-1 p-6 bg-indigo-600 rounded-2xl shadow-lg text-white flex flex-col justify-center">
            <p class="text-xs font-medium opacity-80 uppercase tracking-wider mb-1">Ingresos Totales ({{ $period }})</p>
            <p class="text-4xl font-black">${{ number_format($totalIncome, 2) }}</p>
            <div class="mt-4 pt-4 border-t border-indigo-400/30 flex items-center gap-2 text-xs opacity-80">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                Cálculo basado en monto final pagado
            </div>
        </div>
        
        <!-- Distribution Card -->
        <div class="lg:col-span-2 p-6 bg-white rounded-2xl border border-slate-200 shadow-sm">
            <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-6">Distribución por Método de Pago</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                @foreach($byMethod as $item)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-center transition-hover hover:bg-slate-100">
                        <p class="text-[10px] font-semibold text-slate-500 uppercase mb-1">{{ $item->metodo_pago }}</p>
                        <p class="text-sm font-bold text-slate-900">${{ number_format($item->total, 2) }}</p>
                    </div>
                @endforeach
                @if($byMethod->isEmpty())
                    <p class="col-span-full text-center text-slate-400 italic text-sm py-4">No hay ingresos registrados en este periodo.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-8 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="relative">
            <label class="text-xs font-semibold text-slate-400 uppercase mb-1 block">Filtrar por Método</label>
            <select wire:model.live="filterMethod" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm transition-all">
                <option value="">Todos los métodos</option>
                @foreach($methods as $method)
                    <option value="{{ $method }}">{{ $method }}</option>
                @endforeach
            </select>
        </div>
        <div class="relative">
            <label class="text-xs font-semibold text-slate-400 uppercase mb-1 block">Filtrar por Servicio</label>
            <select wire:model.live="filterService" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm transition-all">
                <option value="">Todos los servicios</option>
                @foreach($servicios as $servicio)
                    <option value="{{ $servicio->id }}">{{ $servicio->nombre }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold tracking-wider">
                        <th class="px-6 py-4 border-b border-slate-200">Fecha</th>
                        <th class="px-6 py-4 border-b border-slate-200">Paciente / Cita</th>
                        <th class="px-6 py-4 border-b border-slate-200">Servicio</th>
                        <th class="px-6 py-4 border-b border-slate-200 text-center">Método</th>
                        <th class="px-6 py-4 border-b border-slate-200 text-right">Monto Final</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-700 divide-y divide-slate-100">
                    @forelse($payments as $pago)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900">{{ $pago->fecha_pago->format('d/m/Y') }}</div>
                                <div class="text-xs text-slate-500">{{ $pago->fecha_pago->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $pago->cita->paciente->nombre }} {{ $pago->cita->paciente->apellido }}</div>
                                <div class="text-xs text-slate-500">Recibo: {{ $pago->comprobante_numero }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-slate-600">{{ $pago->servicio->nombre ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 uppercase tracking-tighter">
                                    {{ $pago->metodo_pago }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900">
                                ${{ number_format($pago->monto_final, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p class="italic">No se encontraron pagos registrados para este periodo.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
            {{ $payments->links() }}
        </div>
    </div>
</div>
