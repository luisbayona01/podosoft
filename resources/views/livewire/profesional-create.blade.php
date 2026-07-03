<div>
    <div class="mb-6">
        <a href="{{ route('profesionales.index') }}"
            class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6" />
            </svg>
            Volver a profesionales
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <div class="border-b border-slate-100">
            <nav class="flex -mb-px">
                <button type="button" wire:click="setActiveTab(0)"
                    class="px-6 py-4 text-sm font-medium transition-all border-b-2 {{ $activeTab === 0 ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                    Datos Personales
                </button>
                <button type="button" wire:click="setActiveTab(1)"
                    class="px-6 py-4 text-sm font-medium transition-all border-b-2 {{ $activeTab === 1 ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                    Horarios
                </button>
            </nav>
        </div>

        <form wire:submit="save" class="p-6">
            @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg flex items-center gap-3 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <path d="m15 9-6 6M9 9l6 6" />
                </svg>
                {{ session('error') }}
            </div>
            @endif

            @if($activeTab === 0)
            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombres *</label>
                        <input type="text" wire:model="nombre"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nombre') border-red-500 @enderror"
                            placeholder="Ej: Juan Carlos">
                        @error('nombre')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Apellidos *</label>
                        <input type="text" wire:model="apellido"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('apellido') border-red-500 @enderror"
                            placeholder="Ej: Pérez García">
                        @error('apellido')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Documento *</label>
                        <input type="text" wire:model="documento"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('documento') border-red-500 @enderror"
                            placeholder="Ej: 1012345678">
                        @error('documento')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Especialidad</label>
                        <input type="text" wire:model="especialidad"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ej: Podología General">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Número de Licencia</label>
                        <input type="text" wire:model="numero_licencia"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ej: RP-12345">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono</label>
                        <input type="text" wire:model="telefono"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ej: 3001234567">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo Electrónico</label>
                        <input type="email" wire:model="email"
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ej: juan.perez@email.com">
                        @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="activo"
                                class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm font-medium text-slate-700">Profesional activo</span>
                        </label>
                    </div>
                </div>
            </div>
            @endif

            @if($activeTab === 1)
            <div class="space-y-4">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm text-slate-600">Selecciona los días y horarios de atención del profesional.</p>
                    <button type="button" wire:click="initHorarios"
                        class="text-xs text-slate-500 hover:text-slate-700 underline">
                        Restablecer horarios
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Día</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Activo</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Hora Inicio</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Hora Fin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($horarios as $dia => $horario)
                            <tr class="hover:bg-slate-50 transition-colors {{ !$horario['activo'] ? 'opacity-50' : '' }}">
                                <td class="px-4 py-3">
                                    <span class="text-sm font-medium text-slate-700 capitalize">
                                        {{ $diasSemana[$dia] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" wire:click="toggleDia('{{ $dia }}')"
                                        class="inline-flex items-center justify-center w-10 h-6 rounded-full transition-all {{ $horario['activo'] ? 'bg-blue-600' : 'bg-slate-200' }}">
                                        <span class="w-4 h-4 rounded-full bg-white shadow-sm transform transition-transform {{ $horario['activo'] ? 'translate-x-2' : '-translate-x-2' }}"></span>
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="time" wire:model="horarios.{{ $dia }}.hora_inicio"
                                        class="w-28 px-3 py-2 border border-slate-200 rounded-lg text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        {{ !$horario['activo'] ? 'disabled' : '' }}>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="time" wire:model="horarios.{{ $dia }}.hora_fin"
                                        class="w-28 px-3 py-2 border border-slate-200 rounded-lg text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        {{ !$horario['activo'] ? 'disabled' : '' }}>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="text-xs text-slate-500 mt-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="inline mr-1">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 16v-4" />
                        <path d="M12 8h.01" />
                    </svg>
                    Solo se guardarán los horarios activos con hora de inicio menor a hora fin.
                </p>
            </div>
            @endif

            <div class="flex items-center gap-3 pt-6 mt-6 border-t border-slate-100">
                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                    class="px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-xl hover:bg-blue-700 transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="save">Guardar Profesional</span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
                <a href="{{ route('profesionales.index') }}"
                    class="px-6 py-2.5 bg-slate-100 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-200 transition-colors">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>