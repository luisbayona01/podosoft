<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
    <div class="container mx-auto px-4 py-8 max-w-lg">
        @if($tenantConfig)
        <div class="text-center mb-8">
            @if(!empty($tenantConfig['logo']))
            <img src="{{ Storage::url($tenantConfig['logo']) }}" alt="{{ $tenantConfig['nombre'] }}" class="h-16 mx-auto mb-4 object-contain">
            @endif
            <h1 class="text-2xl font-bold text-slate-800">{{ $tenantConfig['nombre'] ?? 'Clínica' }}</h1>
            <p class="text-slate-500 text-sm mt-1">Selecciona un servicio</p>
        </div>
        @endif

        @if(empty($servicios))
        <div class="bg-white rounded-2xl shadow-xl p-6 text-center">
            <p class="text-slate-500">No hay servicios disponibles en este momento.</p>
        </div>
        @else
        <div class="space-y-4">
            @foreach($servicios as $servicio)
            <div class="bg-white rounded-2xl shadow-lg p-5 border border-slate-100">
                <div class="flex justify-between items-start mb-3">
                    <h3 class="text-lg font-semibold text-slate-800">{{ $servicio['nombre'] }}</h3>
                    @if(!empty($servicio['precio']))
                    <span class="text-lg font-bold text-indigo-600">${{ number_format($servicio['precio'], 0, ',', '.') }}</span>
                    @endif
                </div>

                @if(!empty($servicio['descripcion']))
                <p class="text-slate-600 text-sm mb-3">{{ $servicio['descripcion'] }}</p>
                @endif

                @if(!empty($servicio['duracion']))
                <div class="flex items-center gap-2 text-sm text-slate-500 mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Duración: {{ $servicio['duracion'] }} minutos</span>
                </div>
                @endif

                <button
                    wire:click="selectServicio({{ $servicio['id'] }})"
                    class="w-full py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors flex items-center justify-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Seleccionar
                </button>
            </div>
            @endforeach
        </div>
        @endif

        <p class="text-center text-slate-400 text-xs mt-8">
            ¿Necesitas ayuda? Contáctanos por WhatsApp
        </p>
    </div>
</div>