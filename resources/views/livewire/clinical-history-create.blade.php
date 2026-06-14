<div class="p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-2">Nueva Nota de Evolución</h2>

    <form wire:submit.prevent="save" class="space-y-6">
        <!-- Plantillas rápidas -->
        <div class="p-4 bg-indigo-50 rounded-lg border border-indigo-100">
            <label class="block text-sm font-medium text-indigo-900 mb-2">Usar Plantilla de Diagnóstico</label>
            <div class="flex gap-4 items-center">
                <select wire:model="selectedTemplate" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Seleccione una plantilla...</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->nombre }}</option>
                    @endforeach
                </select>
                <button type="button" wire:click="applyTemplate" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors text-sm">
                    Aplicar
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <!-- Diagnóstico -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Diagnóstico</label>
                <textarea wire:model="diagnostico" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Describa el diagnóstico..."></textarea>
                @error('diagnostico') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Procedimiento -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Procedimiento Realizado</label>
                <textarea wire:model="procedimiento" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Detalle el procedimiento..."></textarea>
                @error('procedimiento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Observaciones -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Observaciones Adicionales</label>
                <textarea wire:model="observaciones" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
            </div>

            <!-- Firma Digital (Simulada con Texto/Input) -->
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-2">Firma Digital del Profesional</label>
                <input type="text" wire:model="firma_digital" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nombre completo del profesional">
                <p class="text-xs text-gray-500 mt-2 italic">Al firmar, se registrará la fecha y hora exacta del cierre de la nota.</p>
                @error('firma_digital') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="{{ route('patients.show', $patientId) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500">
                Guardar Nota de Evolución
            </button>
        </div>
    </form>
</div>
