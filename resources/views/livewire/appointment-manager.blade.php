<div class="p-6 bg-slate-50 min-h-screen">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('appointments.index') }}" class="p-2 bg-white border border-slate-200 rounded-lg text-slate-500 hover:text-indigo-600 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-slate-900">{{ $isEdit ? 'Editar Cita' : 'Agendar Nueva Cita' }}</h2>
                <p class="text-slate-500 text-sm">Complete la información para coordinar la consulta.</p>
            </div>
        </div>

        <form wire:submit.prevent="save" class="space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
                
                <!-- Patient Selection -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-slate-700">Paciente</label>
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="patientSearch" wire:keydown="searchPatient" 
                            placeholder="Buscar por nombre o documento..." 
                            class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        <div class="absolute left-3 top-2.5 text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>
                    @if(!empty($searchResults))
                        <div class="absolute z-10 w-full max-w-md bg-white border border-slate-200 rounded-lg shadow-xl mt-1 overflow-hidden">
                            @foreach($searchResults as $patient)
                                <button type="button" wire:click="selectPatient({{ $patient->id }}, '{{ $patient->nombre }}', '{{ $patient->apellido }}')" 
                                    class="w-full text-left px-4 py-3 hover:bg-indigo-50 transition-colors border-b border-slate-100 last:border-none">
                                    <div class="font-medium text-slate-900">{{ $patient->nombre }} {{ $patient->apellido }}</div>
                                    <div class="text-xs text-slate-500">{{ $patient->documento }}</div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                    @error('paciente_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Professional -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Profesional</label>
                        <select wire:model="profesional_id" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <option value="">Seleccione profesional...</option>
                            @foreach($profesionales as $prof)
                                <option value="{{ $prof->id }}">{{ $prof->nombre }} ({{ $prof->especialidad }})</option>
                            @endforeach
                        </select>
                        @error('profesional_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Sede -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Sede</label>
                        <select wire:model="sede_id" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <option value="">Seleccione sede...</option>
                            @foreach($sedes as $sede)
                                <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                            @endforeach
                        </select>
                        @error('sede_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Date & Time -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Fecha y Hora</label>
                        <input type="datetime-local" wire:model="fecha_hora" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        @error('fecha_hora') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Service -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Servicio</label>
                        <select wire:model="servicio_id" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <option value="">Seleccione servicio...</option>
                            @foreach($servicios as $servicio)
                                <option value="{{ $servicio->id }}">{{ $servicio->nombre }} - ${{ $servicio->precio }}</option>
                            @endforeach
                        </select>
                        @error('servicio_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('appointments.index') }}" class="px-6 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-all">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-all shadow-sm">
                    {{ $isEdit ? 'Guardar Cambios' : 'Confirmar Cita' }}
                </button>
            </div>
        </form>
    </div>
</div>
