<div class="space-y-6">
    <!-- Add Antecedent Form -->
    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
        <h3 class="text-sm font-bold text-gray-700 mb-4 uppercase">Agregar Antecedente</h3>
        <form wire:submit.prevent="addAntecedent" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="space-y-2">
                <label class="block text-xs font-medium text-gray-500">Tipo de Antecedente</label>
                <select wire:model="selectedType" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Seleccione...</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}">{{ $type->name }} {{ $type->is_risk ? '🔴' : '' }}</option>
                    @endforeach
                </select>
                @error('selectedType') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="space-y-2 md:col-span-1">
                <label class="block text-xs font-medium text-gray-500">Notas / Observaciones</label>
                <input type="text" wire:model="notes" placeholder="Detalles adicionales..." class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors">
                    Agregar
                </button>
            </div>
        </form>
    </div>

    <!-- Antecedents List -->
    <div class="space-y-3">
        <h3 class="text-sm font-bold text-gray-700 uppercase">Antecedentes Registrados</h3>
        @if($patientAntecedents->isEmpty())
            <p class="text-sm text-gray-500 italic">No hay antecedentes registrados para este paciente.</p>
        @else
            <div class="grid grid-cols-1 gap-3">
                @foreach($patientAntecedents as $antecedent)
                    <div class="flex justify-between items-center p-3 bg-white border rounded-lg shadow-sm {{ $antecedent->is_risk ? 'border-l-4 border-l-red-500' : 'border-l-4 border-l-gray-300' }}">
                        <div class="flex items-center space-x-3">
                            <span class="text-sm font-bold {{ $antecedent->is_risk ? 'text-red-600' : 'text-gray-700' }}">
                                {{ $antecedent->name }}
                            </span>
                            @if($antecedent->pivot->notes)
                                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">{{ $antecedent->pivot->notes }}</span>
                            @endif
                        </div>
                        <button wire:click="removeAntecedent({{ $antecedent->id }})" class="text-red-400 hover:text-red-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
