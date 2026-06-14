<div class="p-6 bg-slate-50 min-h-screen">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('appointments.index') }}" class="p-2 bg-white border border-slate-200 rounded-lg text-slate-500 hover:text-indigo-600 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-slate-900">{{ $pagoId ? 'Editar Pago' : 'Registrar Pago de Consulta' }}</h2>
                <p class="text-slate-500 text-sm">Registre el ingreso económico derivado de la atención al paciente.</p>
            </div>
        </div>

        <form wire:submit.prevent="save" class="space-y-6">
            <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Patient -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Paciente</label>
                        <div class="p-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-900 font-medium">
                            {{ \App\Models\Paciente::find($paciente_id)?->nombre }} {{ \App\Models\Paciente::find($paciente_id)?->apellido }}
                        </div>
                        @error('paciente_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Appointment -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Cita Relacionada</label>
                        <div class="p-2 bg-slate-50 border border-slate-200 rounded-lg text-slate-900 font-medium">
                            ID Cita: #{{ $cita_id }}
                        </div>
                        @error('cita_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Service -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Servicio Realizado</label>
                        <select wire:model="servicio_id" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <option value="">Seleccione servicio...</option>
                            @foreach($servicios as $servicio)
                                <option value="{{ $servicio->id }}">{{ $servicio->nombre }} - ${{ $servicio->precio }}</option>
                            @endforeach
                        </select>
                        @error('servicio_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Date -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Fecha del Pago</label>
                        <input type="date" wire:model="fecha_pago" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        @error('fecha_pago') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                    <!-- Value -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-indigo-700">Valor Servicio</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-indigo-500 font-bold">$</span>
                            <input type="number" step="0.01" wire:model="valor" class="w-full pl-7 py-2 border border-indigo-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        </div>
                        @error('valor') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Discount -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-indigo-700">Descuento</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-indigo-500 font-bold">$</span>
                            <input type="number" step="0.01" wire:model="descuento" class="w-full pl-7 py-2 border border-indigo-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        </div>
                        @error('descuento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Total -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-indigo-900">Total Pagado</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-indigo-900 font-bold">$</span>
                            <input type="number" step="0.01" wire:model="monto_final" readonly class="w-full pl-7 py-2 border border-indigo-300 bg-indigo-100 rounded-lg font-bold text-indigo-900 outline-none">
                        </div>
                        @error('monto_final') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Method -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Método de Pago</label>
                        <select wire:model="metodo_pago" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            @foreach($metodosPago as $metodo)
                                <option value="{{ $metodo }}">{{ $metodo }}</option>
                            @endforeach
                        </select>
                        @error('metodo_pago') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Observations -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Observaciones</label>
                        <input type="text" wire:model="observaciones" placeholder="Notas adicionales..." class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center">
                @if($pagoId)
                    <button type="button" wire:click="void" class="px-6 py-2.5 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-all">
                        Anular Pago
                    </button>
                @endif
                <div class="flex gap-3">
                    <a href="{{ route('appointments.index') }}" class="px-6 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-all">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-all shadow-sm">
                        {{ $pagoId ? 'Actualizar Pago' : 'Registrar Pago' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
