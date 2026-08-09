<div class="p-6 bg-slate-50 min-h-screen">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Agenda de Consultas</h2>
            <p class="text-slate-500 text-sm">Gestiona tus citas, horarios y disponibilidad de profesionales.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('appointments.create') }}" class="px-4 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-all shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Agendar Cita
            </a>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-// 9 5h4M4 17h16a2 2 0 002-2V5a2 2 0 00-2-2H4a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Citas de Hoy</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $metrics['today'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-yellow-50 text-yellow-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Pendientes</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $metrics['pending'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Confirmadas</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $metrics['confirmed'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h2m4 0h2m-8 4h8a2 2 0 002-2V8a2 2 0 00-2-2H7a2 2 0 00-2 2v9a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Pagadas</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $metrics['pagada'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-red-50 text-red-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Canceladas</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $metrics['cancelled'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Appointments Panel -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-8 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Citas para Hoy</h3>
                    <p class="text-sm text-slate-500">{{ now()->format('l, d M Y') }}</p>
                </div>
            </div>
            <span class="px-3 py-1.5 bg-blue-50 text-blue-700 text-xs font-bold uppercase tracking-wide rounded-full">
                {{ count($todayAppointments) }} cita(s)
            </span>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($todayAppointments as $cita)
                <div class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-16 shrink-0">
                            <span class="text-lg font-bold text-slate-900">{{ $cita->fecha_hora->format('H:i') }}</span>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold shrink-0">
                            {{ strtoupper(substr($cita->paciente?->nombre ?? '?', 0, 1)) }}{{ strtoupper(substr($cita->paciente?->apellido ?? '', 0, 1)) }}
                        </div>
                        <div>
                            <div class="font-semibold text-slate-900">{{ $cita->paciente?->nombre }} {{ $cita->paciente?->apellido }}</div>
                            <div class="text-xs text-slate-500">
                                {{ $cita->servicios->pluck('nombre')->join(', ') ?: 'Sin servicios' }}
                                &middot; {{ $cita->profesional?->nombre ?? 'Sin asignar' }}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        @php $estadoToday = strtolower($cita->estado); @endphp
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wide
                            {{ $estadoToday === 'pagada' ? 'bg-emerald-600 text-white' :
                               ($estadoToday === 'completada' ? 'bg-emerald-100 text-emerald-700' :
                               ($estadoToday === 'confirmada' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700')) }}">
                            {{ $cita->estado }}
                        </span>
                        @unless($estadoToday === 'pagada')
                            <a href="{{ route('payments.create', ['citaId' => $cita->id]) }}" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all" title="Registrar Pago">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </a>
                        @else
                            <span class="p-2 text-emerald-700 bg-emerald-50 rounded-lg" title="Pago registrado">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                        @endunless
                    </div>
                </div>
            @empty
                <div class="px-6 py-10 text-center text-slate-400">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="w-10 h-10 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="italic">No tienes citas programadas para hoy.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Advanced Filters -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-8 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="relative">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar paciente por nombre o documento..." 
                class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none text-sm">
            <div class="absolute left-3 top-2.5 text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>
        <div>
            <input type="date" wire:model.live="date" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none text-sm">
        </div>
        <div>
            <select wire:model.live="status" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none text-sm">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="confirmada">Confirmada</option>
                <option value="pagada">Pagada</option>
                <option value="completada">Completada</option>
                <option value="cancelada">Cancelada</option>
                <option value="no_asistio">No asistió</option>
            </select>
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold tracking-wider">
                        <th class="px-6 py-4 border-b border-slate-200">Fecha y Hora</th>
                        <th class="px-6 py-4 border-b border-slate-200">Paciente</th>
                        <th class="px-6 py-4 border-b border-slate-200">Profesional / Sede</th>
                        <th class="px-6 py-4 border-b border-slate-200 text-center">Estado</th>
                        <th class="px-6 py-4 border-b border-slate-200 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-700 divide-y divide-slate-100">
                    @forelse($appointments as $cita)
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $cita->fecha_hora->format('d M, Y') }}</div>
                                <div class="text-xs text-slate-500 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $cita->fecha_hora->format('H:i') }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $cita->paciente->nombre }} {{ $cita->paciente->apellido }}</div>
                                <div class="text-xs text-slate-500">{{ $cita->paciente->documento }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-slate-900 font-medium">{{ $cita->profesional->nombre ?? 'Sin asignar' }}</div>
                                <div class="text-xs text-slate-500">{{ $cita->sede->nombre ?? 'Sede no definida' }}</div>
                            </td>
                            @php $estadoLabel = strtolower($cita->estado); @endphp
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wide
                                    {{ $estadoLabel === 'pagada' ? 'bg-emerald-600 text-white' :
                                       ($estadoLabel === 'completada' ? 'bg-emerald-100 text-emerald-700' :
                                       ($estadoLabel === 'cancelada' ? 'bg-red-100 text-red-700' :
                                       ($estadoLabel === 'confirmada' ? 'bg-blue-100 text-blue-700' :
                                       ($estadoLabel === 'no_asistio' ? 'bg-orange-100 text-orange-700' : 'bg-yellow-100 text-yellow-700')))) }}">
                                    {{ $cita->estado }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    @unless($estadoLabel === 'pagada')
                                    <a href="{{ route('payments.create', ['citaId' => $cita->id]) }}" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all" title="Registrar Pago">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </a>
@else
                                    <span class="p-2 text-emerald-700 bg-emerald-50 rounded-lg" title="Pago registrado">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </span>
@endunless
                                    <button wire:click="updateStatus({{ $cita->id }}, 'Confirmada')" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Confirmar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </button>
                                    <button wire:click="updateStatus({{ $cita->id }}, 'Completada')" class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all" title="Marcar como Completada">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </button>
                                    <button wire:click="cancelAppointment({{ $cita->id }})" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Cancelar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                    <a href="{{ route('appointments.edit', $cita->id) }}" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                    <a href="{{ route('patients.show', $cita->paciente_id) }}?tab=historia" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Ver Historia">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <p class="italic">No hay citas que coincidan con la búsqueda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $appointments->links() }}
    </div>
</div>
