<div class="space-y-6">
    <!-- Module Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Inventario de Insumos</h1>
            <p class="text-slate-500 text-sm">Gestiona tus materiales, controla el stock y evita el desabastecimiento.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <!-- CSV Import Group -->
            <div class="flex items-center gap-2 bg-white p-1 rounded-xl border border-slate-200 shadow-sm">
                <input type="file" wire:model="csvFile" class="hidden" id="csv_import">
                <label for="csv_import" class="px-3 py-2 text-sm font-semibold text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Importar CSV
                </label>
                <button wire:click="importCsv" wire:loading.attr="disabled" class="px-3 py-2 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition-all disabled:opacity-50 active:scale-95 shadow-sm" {{ !$csvFile ? 'disabled' : '' }}>
                    Cargar
                </button>
            </div>
            <a href="{{ route('insumos.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-indigo-700 transition-all active:scale-95 gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nuevo Insumo
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @php
            $cards = [
                ['label' => 'Total Artículos', 'value' => $stats['total_items'], 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50'],
                ['label' => 'Stock Bajo / Crítico', 'value' => $stats['low_stock'], 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.876', 'color' => 'text-red-600', 'bg' => 'bg-red-50'],
                ['label' => 'Categorías', 'value' => $stats['categories'], 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'color' => 'text-purple-600', 'bg' => 'bg-purple-50'],
            ];
        @endphp
        @foreach($cards as $card)
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="{{ $card['bg'] }} {{ $card['color'] }} p-3 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"></path></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $card['value'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filters and Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="relative w-full md:w-96">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar insumo o SKU..." 
                class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm">
        </div>
        
        <div class="flex items-center gap-2 w-full md:w-auto">
            <span class="text-xs font-semibold text-slate-400 uppercase ml-2">Categoría:</span>
            <select wire:model.live="category_id" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm font-medium">
                <option value="">Todas</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->nombre }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Insumo / SKU</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Categoría</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Stock Actual</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Stock Mínimo</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Estado</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($insumos as $insumo)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $insumo->nombre }}</p>
                                    <p class="text-xs text-slate-500 font-mono">{{ $insumo->codigo_sku }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-slate-600 bg-slate-100 px-2 py-1 rounded-lg">{{ $insumo->categoria->nombre }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-bold text-slate-900">{{ $insumo->stock_actual }}</span>
                                <span class="text-xs text-slate-400 ml-1">{{ $insumo->unidad_medida }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm text-slate-500">{{ $insumo->stock_minimo }}</span>
                                <span class="text-xs text-slate-400 ml-1">{{ $insumo->unidad_medida }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($insumo->stock_actual <= $insumo->stock_minimo)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-600 mr-1.5 animate-pulse"></span>
                                        BAJO
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 border border-emerald-200">
                                        OK
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('insumos.edit', $insumo->id) }}" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                    </div>
                                    <p class="text-slate-900 font-semibold">No se encontraron insumos</p>
                                    <p class="text-slate-500 text-sm">Intenta ajustar tu búsqueda o agregar nuevos materiales.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
            {{ $insumos->links() }}
        </div>
    </div>
</div>
