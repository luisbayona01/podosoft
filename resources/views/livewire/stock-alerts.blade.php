<div class="p-4 bg-red-50 border border-red-100 rounded-xl shadow-sm">
    <div class="flex items-center gap-2 mb-3 text-red-700">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <span class="text-sm font-bold uppercase">Alertas de Stock Bajo</span>
    </div>
    
    @if($lowStockItems->isEmpty())
        <p class="text-xs text-red-500 italic">No hay insumos por debajo del stock mínimo.</p>
    @else
        <div class="space-y-2">
            @foreach($lowStockItems as $item)
                <div class="flex justify-between items-center p-2 bg-white rounded border border-red-200">
                    <span class="text-xs font-medium text-gray-700">{{ $item->nombre }}</span>
                    <span class="text-xs font-bold text-red-600">{{ $item->stock_actual }} / {{ $item->stock_minimo }} {{ $item->unidad_medida }}</span>
                </div>
            @endforeach
        </div>
        <a href="{{ route('insumos.index') }}" class="mt-3 block text-center text-xs text-red-600 font-bold hover:underline">
            Gestionar Inventario →
        </a>
    @endif
</div>
