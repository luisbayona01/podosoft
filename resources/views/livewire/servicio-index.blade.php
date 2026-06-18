<div class="space-y-6">
    <!-- Module Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Servicios</h1>
            <p class="text-slate-500 text-sm">Gestiona los servicios que ofreces en tu consultorio.</p>
        </div>
        <a href="{{ route('servicios.create') }}"
            class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-indigo-700 transition-all active:scale-95 gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nuevo Servicio
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @php
            $cards = [
                ['label' => 'Total Servicios', 'value' => $stats['total'], 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50'],
                ['label' => 'Activos', 'value' => $stats['active'], 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
                ['label' => 'Inactivos', 'value' => $stats['inactive'], 'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'text-red-600', 'bg' => 'bg-red-50'],
            ];
        @endphp
        @foreach($cards as $card)
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="{{ $card['bg'] }} {{ $card['color'] }} p-3 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $card['value'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filters and Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o descripción..."
                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Servicio</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Precio</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Duración</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Estado</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($servicios as $servicio)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $servicio->nombre }}</p>
                                    @if($servicio->descripcion)
                                        <p class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ $servicio->descripcion }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-slate-900">${{ number_format($servicio->precio, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm text-slate-600">{{ $servicio->duracion }} min</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button wire:click="toggleActive({{ $servicio->id }})" class="focus:outline-none">
                                    @if($servicio->activo)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 border border-emerald-200 hover:bg-emerald-200 transition-colors">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 border border-red-200 hover:bg-red-200 transition-colors">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>
                                            Inactivo
                                        </span>
                                    @endif
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('servicios.edit', $servicio->id) }}"
                                        class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all"
                                        title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <button wire:click="delete({{ $servicio->id }})"
                                        wire:confirm="¿Estás seguro de eliminar el servicio '{{ $servicio->nombre }}'?"
                                        class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all"
                                        title="Eliminar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </div>
                                    <p class="text-slate-900 font-semibold">No se encontraron servicios</p>
                                    <p class="text-slate-500 text-sm">Intenta ajustar tu búsqueda o crea un nuevo servicio.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($servicios->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
                {{ $servicios->links() }}
            </div>
        @endif
    </div>
</div>