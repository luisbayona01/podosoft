<div class="space-y-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold text-gray-800">Línea de Tiempo Clínica</h3>
        <div class="flex gap-2">
            <input type="text" wire:model.live="filterProcedure" placeholder="Filtrar procedimiento..." 
                class="text-sm rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
    </div>

    <div class="relative border-l-2 border-indigo-200 ml-4 space-y-8">
        @foreach($histories as $history)
            <div class="relative pl-8">
                <!-- Timeline Dot -->
                <div class="absolute -left-[9px] top-1 w-4 h-4 bg-indigo-600 rounded-full border-2 border-white"></div>
                
                <div class="p-4 bg-white rounded-lg shadow-sm border border-gray-100 hover:border-indigo-300 transition-colors">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold text-indigo-600 uppercase">{{ $history->created_at->format('d M, Y H:i') }}</span>
                        @if($history->bloqueo_edicion)
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-[10px] rounded uppercase font-semibold">Bloqueado</span>
                        @endif
                    </div>
                    
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase">Diagnóstico</p>
                            <p class="text-sm text-gray-800 font-medium">{{ $history->diagnostico }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-400 uppercase">Procedimiento</p>
                            <p class="text-sm text-gray-700">{{ $history->procedimiento }}</p>
                        </div>
                        @if($history->observaciones)
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase">Observaciones</p>
                                <p class="text-xs text-gray-600 italic">{{ $history->observaciones }}</p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="mt-4 pt-3 border-t border-gray-50 flex justify-between items-center">
                        <span class="text-xs text-gray-400">Firma: {{ $history->firma_digital }}</span>
                        <div class="flex gap-2">
                             @if($history->fotografias->count() > 0)
                                <span class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded-full font-medium">
                                    {{ $history->fotografias->count() }} Fotos
                                </span>
                             @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $histories->links() }}
    </div>
</div>
