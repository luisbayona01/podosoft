<div class="space-y-4">
    @if($appointments->isEmpty())
        <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-// 4 4h4m-12 4h12m-12 4h12m-12 4h12"></path></svg>
            <p class="mt-2 text-sm text-gray-500">No hay citas registradas para este paciente.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4">
            @foreach($appointments as $cita)
                <div class="p-4 bg-white border rounded-lg shadow-sm flex justify-between items-center hover:border-indigo-300 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-indigo-100 text-indigo-600 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-// 4 4h4m-12 4h12m-12 4h12m-12 4h12"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900">{{ $cita->fecha_hora->format('d/m/Y H:i') }}</p>
                            <div class="flex gap-2 mt-1">
                                <span class="text-[10px] bg-gray-100 text-gray-600 px-2 py-0.5 rounded uppercase font-semibold">
                                    {{ $cita->profesional->nombre ?? 'Sin asignar' }}
                                </span>
                                <span class="text-[10px] bg-blue-100 text-blue-600 px-2 py-0.5 rounded uppercase font-semibold">
                                    {{ $cita->sede->nombre ?? 'Sede Central' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $cita->estado === 'Completada' ? 'bg-emerald-100 text-emerald-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $cita->estado }}
                        </span>
                        <div class="mt-1 text-[10px] text-gray-400">
                            Origen: {{ $cita->origen ?? 'Manual' }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">
            {{ $appointments->links() }}
        </div>
    @endif
</div>
