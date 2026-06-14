<div class="p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-2">Mapa Gráfico del Pie</h2>

    <div class="flex flex-col md:flex-row gap-8 items-center justify-center">
        <!-- SVG Foot Map (Simplified Placeholder) -->
        <div class="relative w-64 h-96 bg-gray-100 rounded-full border-4 border-gray-200 flex items-center justify-center overflow-hidden">
            <svg viewBox="0 0 100 200" class="w-full h-full">
                <!-- Simplified foot shape -->
                <path d="M30,20 Q50,10 70,20 L75,150 Q50,180 25,150 Z" fill="#f3f4f6" stroke="#d1d5db" stroke-width="2" />
                
                <!-- Dynamic Zones -->
                @foreach($zonas as $zona)
                    <circle cx="50" cy="100" r="5" 
                        wire:click="selectZona({{ $zona->id }})" 
                        class="cursor-pointer transition-colors {{ $selectedZonaId == $zona->id ? 'fill-red-500' : 'fill-gray-400 hover:fill-indigo-500' }}" />
                @endforeach
            </svg>
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-20 text-xs font-bold text-gray-400 rotate-45">
                MAPA INTERACTIVO
            </div>
        </div>

        <!-- Zone Controls -->
        <div class="w-full max-w-sm space-y-4">
            <div class="p-4 bg-indigo-50 rounded-lg border border-indigo-100">
                <p class="text-sm font-bold text-indigo-900 mb-2">Seleccionar Zona</p>
                <select wire:model="selectedZonaId" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- Seleccione una zona --</option>
                    @foreach($zonas as $zona)
                        <option value="{{ $zona->id }}">{{ $zona->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Observación de la Zona</label>
                <textarea wire:model="note" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Describa la lesión..."></textarea>
            </div>

            <button wire:click="markZona" class="w-full py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors font-medium">
                Marcar Zona en Historia
            </button>
        </div>
    </div>
</div>
