<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
    <div class="container mx-auto px-4 py-8 max-w-lg">
        @if($tenantConfig)
        <div class="text-center mb-6">
            @if(!empty($tenantConfig['logo']))
            <img src="{{ Storage::url($tenantConfig['logo']) }}" alt="{{ $tenantConfig['nombre'] }}" class="h-14 mx-auto mb-3 object-contain">
            @endif
            <h1 class="text-xl font-bold text-slate-800">{{ $tenantConfig['nombre'] ?? 'Clínica' }}</h1>
            <p class="text-slate-500 text-sm">Agenda tu cita</p>
        </div>
        @endif

        @if($patient)
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-3 mb-4 text-center">
            <p class="text-sm text-indigo-700">Paciente: <strong>{{ $patient->nombre }} {{ $patient->apellido }}</strong></p>
        </div>
        @endif

        <div class="bg-white rounded-2xl shadow-xl p-6">
            <div class="mb-5">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-medium text-slate-500">Paso {{ $step }} de {{ $totalSteps }}</span>
                    <span class="text-xs text-slate-400">{{ round(($step / $totalSteps) * 100) }}%</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-1.5">
                    <div class="bg-indigo-600 h-1.5 rounded-full transition-all duration-300" style="width: {{ ($step / $totalSteps) * 100 }}%"></div>
                </div>
            </div>

            @if($errorMessage)
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl">
                <p class="text-red-700 text-sm">{{ $errorMessage }}</p>
            </div>
            @endif

            @if($citaCreada)
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-slate-800 mb-2">¡Cita Agendada!</h2>
                <div class="bg-slate-50 rounded-xl p-4 text-left text-sm space-y-1.5 mb-4">
                    <p><span class="text-slate-500">Fecha:</span> <span class="font-medium">{{ date('d/m/Y', strtotime($fecha)) }}</span></p>
                    <p><span class="text-slate-500">Hora:</span> <span class="font-medium">{{ $horaSeleccionada }}</span></p>
                    <p><span class="text-slate-500">Profesional:</span> <span class="font-medium">{{ $selectedProfesional->nombre ?? '' }} {{ $selectedProfesional->apellido ?? '' }}</span></p>
                    <p><span class="text-slate-500">Servicio:</span> <span class="font-medium">{{ $selectedServicio->nombre ?? '' }}</span></p>
                </div>
                <p class="text-slate-600 text-sm mb-4">Recibirás un mensaje de confirmación por WhatsApp.</p>
                <button wire:click="restart" class="text-indigo-600 hover:underline text-sm font-medium">
                    Agendar otra cita
                </button>
            </div>
            @else
            <form wire:submit.prevent="confirmAppointment">
                @csrf

                @if($step === 1)
                <div>
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Selecciona un Servicio</h2>
                    <div class="space-y-2 max-h-80 overflow-y-auto">
                        @foreach($servicios as $servicio)
                        <button type="button" wire:click="selectServicio({{ $servicio['id'] }})"
                            class="w-full p-3 text-left rounded-xl border-2 transition-all {{ $servicioId === $servicio['id'] ? 'border-indigo-600 bg-indigo-50' : 'border-slate-200 hover:border-slate-300' }}">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-medium text-slate-800">{{ $servicio['nombre'] }}</p>
                                    <p class="text-sm text-slate-500">{{ $servicio['duracion'] }} min</p>
                                </div>
                                <p class="font-bold text-indigo-600">${{ number_format($servicio['precio'], 0, ',', '.') }}</p>
                            </div>
                        </button>
                        @endforeach
                    </div>
                </div>

                @elseif($step === 2)
                <div>
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Selecciona un Profesional</h2>
                    <div class="space-y-2 max-h-80 overflow-y-auto">
                        @foreach($profesionales as $profesional)
                        <button type="button" wire:click="selectProfesional({{ $profesional['id'] }})"
                            class="w-full p-3 text-left rounded-xl border-2 transition-all {{ $profesionalId === $profesional['id'] ? 'border-indigo-600 bg-indigo-50' : 'border-slate-200 hover:border-slate-300' }}">
                            <p class="font-medium text-slate-800">{{ $profesional['nombre'] }} {{ $profesional['apellido'] }}</p>
                            <p class="text-sm text-slate-500">{{ $profesional['especialidad'] }}</p>
                        </button>
                        @endforeach
                    </div>
                </div>

                @elseif($step === 3)
                <div>
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Selecciona una Sede</h2>
                    <div class="space-y-2 max-h-80 overflow-y-auto">
                        @foreach($sedes as $sede)
                        <button type="button" wire:click="selectSede({{ $sede['id'] }})"
                            class="w-full p-3 text-left rounded-xl border-2 transition-all {{ $sedeId === $sede['id'] ? 'border-indigo-600 bg-indigo-50' : 'border-slate-200 hover:border-slate-300' }}">
                            <p class="font-medium text-slate-800">{{ $sede['nombre'] }}</p>
                            <p class="text-sm text-slate-500">{{ $sede['direccion'] }}</p>
                        </button>
                        @endforeach
                    </div>
                </div>

                @elseif($step === 4)
                <div>
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Fecha y Horario</h2>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Fecha</label>
                        <input type="date" wire:model.live="fecha" min="{{ date('Y-m-d') }}"
                            class="w-full rounded-lg border-slate-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    @if($fecha && count($horariosDisponibles) > 0)
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Horarios Disponibles</label>
                        <div class="grid grid-cols-3 gap-2 max-h-48 overflow-y-auto">
                            @foreach($horariosDisponibles as $slot)
                            <button type="button" wire:click="selectHora('{{ $slot['hora'] }}')"
                                class="p-2 text-center rounded-lg border-2 text-sm font-medium transition-all {{ $horaSeleccionada === $slot['hora'] ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-200 hover:border-indigo-300 text-slate-700' }}">
                                {{ $slot['hora'] }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @elseif($fecha && count($horariosDisponibles) === 0)
                    <p class="text-slate-500 text-sm text-center py-4">No hay horarios disponibles para esta fecha.</p>
                    @endif
                </div>

                @elseif($step === 5)
                <div>
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Confirmar Cita</h2>

                    <div class="bg-slate-50 rounded-xl p-4 space-y-2 text-sm mb-4">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Servicio:</span>
                            <span class="font-medium">{{ $selectedServicio->nombre ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Profesional:</span>
                            <span class="font-medium">{{ $selectedProfesional->nombre ?? '' }} {{ $selectedProfesional->apellido ?? '' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Sede:</span>
                            <span class="font-medium">{{ $selectedSede->nombre ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Fecha:</span>
                            <span class="font-medium">{{ date('d/m/Y', strtotime($fecha)) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Hora:</span>
                            <span class="font-medium">{{ $horaSeleccionada }}</span>
                        </div>
                        <div class="border-t pt-2 flex justify-between">
                            <span class="text-slate-500">Valor:</span>
                            <span class="font-bold text-indigo-600">${{ number_format($selectedServicio->precio ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <button type="submit" wire:loading.attr="disabled" wire:target="confirmAppointment"
                        class="w-full py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="confirmAppointment">Confirmar Cita</span>
                        <span wire:loading wire:target="confirmAppointment">Procesando...</span>
                    </button>
                </div>
                @endif

                <div class="flex gap-3 mt-5">
                    @if($step > 1)
                    <button type="button" wire:click="previousStep" class="flex-1 py-2.5 bg-slate-100 text-slate-700 font-medium rounded-xl hover:bg-slate-200 transition-colors text-sm">
                        Atrás
                    </button>
                    @endif

                    @if($step < $totalSteps)
                    <button type="button" wire:click="nextStep" wire:loading.attr="disabled"
                        class="flex-1 py-2.5 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition-colors text-sm disabled:opacity-50">
                        <span wire:loading.remove>Continuar</span>
                        <span wire:loading>...</span>
                    </button>
                    @endif
                </div>
            </form>
            @endif
        </div>

        <div class="text-center mt-5">
            <a href="{{ route('login') }}" class="text-sm text-slate-500 hover:text-slate-700">
                Accesos de administrador
            </a>
        </div>
    </div>
</div>