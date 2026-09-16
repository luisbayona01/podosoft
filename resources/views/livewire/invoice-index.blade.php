<div class="p-6 bg-slate-50 min-h-screen">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Facturas</h2>
                <p class="text-slate-500 text-sm">Facturación comercial independiente de citas e historias clínicas.</p>
            </div>
            <a href="{{ route('invoices.create') }}" class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-all shadow-sm">
                Nueva factura
            </a>
        </div>

        @if(session('message'))
            <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">{{ session('message') }}</div>
        @endif

        <div class="flex flex-col md:flex-row gap-3 mb-4">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por número o cliente..." class="flex-1 py-2 px-3 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
            <select wire:model.live="estado" class="py-2 px-3 bg-white border border-slate-200 rounded-lg text-sm">
                <option value="">Todos los estados</option>
                <option value="borrador">Borrador</option>
                <option value="emitida">Emitida</option>
                <option value="pagada">Pagada</option>
                <option value="anulada">Anulada</option>
            </select>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Número</th>
                        <th class="px-4 py-3 text-left">Fecha</th>
                        <th class="px-4 py-3 text-left">Cliente</th>
                        <th class="px-4 py-3 text-left">Origen</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($facturas as $factura)
                        <tr class="{{ $factura->estado === 'anulada' ? 'opacity-50' : '' }}">
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $factura->numero_factura }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $factura->fecha_emision?->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-slate-700">
                                {{ $factura->cliente_display }}
                                @if(!$factura->paciente_id)
                                    <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-500">sin registro</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500">
                                @if($factura->cita_id)
                                    Cita #{{ $factura->cita_id }}
                                @else
                                    <span class="text-slate-300">Venta directa</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-800">${{ number_format($factura->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded-full text-[11px] font-semibold
                                    {{ $factura->estado === 'pagada' ? 'bg-emerald-50 text-emerald-600' : '' }}
                                    {{ $factura->estado === 'borrador' ? 'bg-slate-100 text-slate-500' : '' }}
                                    {{ $factura->estado === 'emitida' ? 'bg-blue-50 text-blue-600' : '' }}
                                    {{ $factura->estado === 'anulada' ? 'bg-red-50 text-red-500' : '' }}">
                                    {{ ucfirst($factura->estado) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($factura->estado !== 'anulada')
                                    <button wire:click="anular({{ $factura->id }})"
                                        wire:confirm="¿Anular esta factura? Se revertirán el pago y el inventario."
                                        class="text-xs font-medium text-red-500 hover:underline">Anular</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-400">No hay facturas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $facturas->links() }}</div>
    </div>
</div>
