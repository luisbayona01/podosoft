<div class="space-y-6">
    @if($showForm)
        <!-- Registration Form -->
        <div class="bg-white p-6 rounded-2xl border border-indigo-100 shadow-sm animate-in fade-in slide-in-from-top-4 duration-300">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-slate-900">{{ $pagoId ? 'Editar Pago' : 'Registrar Nuevo Pago' }}</h3>
                <button wire:click="resetForm" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form wire:submit.prevent="save" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-500 uppercase">Cita Relacionada</label>
                        <select wire:model="cita_id" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                            <option value="">Seleccione cita...</option>
                            @foreach(\App\Models\Cita::where('paciente_id', $patientId)->where('estado', '!=', 'Cancelada')->get() as $cita)
                                <option value="{{ $cita->id }}">{{ $cita->fecha_hora->format('d/m/Y H:i') }} - {{ $cita->estado }}</option>
                            @endforeach
                        </select>
                        @error('cita_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-500 uppercase">Servicio</label>
                        <select wire:model="servicio_id" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                            <option value="">Seleccione servicio...</option>
                            @foreach($servicios as $servicio)
                                <option value="{{ $servicio->id }}">{{ $servicio->nombre }} - ${{ $servicio->precio }}</option>
                            @endforeach
                        </select>
                        @error('servicio_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-slate-500 uppercase">Fecha Pago</label>
                            <input type="date" wire:model="fecha_pago" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500 uppercase">Método</label>
                            <select wire:model="metodo_pago" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                                @foreach($metodosPago as $metodo)
                                    <option value="{{ $metodo }}">{{ $metodo }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-slate-500 uppercase">Valor</label>
                            <input type="number" step="0.01" wire:model="valor" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm font-bold">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500 uppercase">Descuento</label>
                            <input type="number" step="0.01" wire:model="descuento" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-900 uppercase">Total a Pagar</label>
                        <div class="text-2xl font-bold text-indigo-600">${{ number_format($monto_final, 2) }}</div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 uppercase">Observaciones</label>
                        <textarea wire:model="observaciones" rows="2" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 md:col-span-2 pt-4 border-t border-slate-100">
                    <button type="button" wire:click="resetForm" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-all">
                        Cancelar
                    </button>
                    <button type="submit" class="px-6 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-all shadow-sm">
                        {{ $pagoId ? 'Actualizar Pago' : 'Registrar Pago' }}
                    </button>
                </div>
            </form>
        </div>
    @else
        <!-- Billing History -->
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-900">Historial de Pagos</h3>
                <button wire:click="openCreateForm" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-all flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nuevo Pago
                </button>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold tracking-wider">
                                <th class="px-6 py-4 border-b">Fecha</th>
                                <th class="px-6 py-4 border-b">Servicio</th>
                                <th class="px-6 py-4 border-b text-center">Método</th>
                                <th class="px-6 py-4 border-b text-right">Monto</th>
                                <th class="px-6 py-4 border-b text-center">Estado</th>
                                <th class="px-6 py-4 border-b text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-slate-700 divide-y divide-slate-100">
                            @forelse($payments as $pago)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-900">{{ $pago->fecha_pago->format('d/m/Y') }}</div>
                                        <div class="text-xs text-slate-500">{{ $pago->comprobante_numero }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-900">{{ $pago->servicio->nombre ?? 'No especificado' }}</div>
                                        <div class="text-xs text-slate-500">Cita #{{ $pago->cita_id }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 uppercase">{{ $pago->metodo_pago }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-slate-900">
                                        ${{ number_format($pago->monto_final, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase {{ $pago->estado === 'pagado' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $pago->estado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <a href="{{ route('payments.receipt', $pago->id) }}" target="_blank" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Comprobante">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </a>
                                        <button wire:click="openEditForm({{ $pago->id }})" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        @if($pago->estado === 'pagado')
                                            <button wire:click="voidPayment({{ $pago->id }})" 
                                                wire:confirm="¿Estás seguro de que deseas anular este pago?" 
                                                class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Anular">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">
                                        No se han registrado pagos para este paciente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-4">
                {{ $payments->links() }}
            </div>
        </div>
    @endif
</div>
