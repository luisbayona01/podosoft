<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-800">Historia Clínica Podológica</h3>
        <a href="{{ route('clinical-history.create', ['patientId' => $patient->id]) }}" 
            class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors">
            + Nueva Nota de Evolución
        </a>
    </div>

    <!-- Quick Stats / Last Visit -->
    @if($lastHistory)
        <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg">
            <p class="text-xs font-bold text-blue-600 uppercase">Última Evolución</p>
            <p class="text-sm text-blue-800 font-medium">{{ $lastHistory->created_at->format('d/m/Y') }} - {{ $lastHistory->diagnostico }}</p>
        </div>
    @else
        <div class="p-4 bg-gray-50 border-l-4 border-gray-300 rounded-r-lg text-gray-500 text-sm italic">
            No hay registros previos de historia clínica para este paciente.
        </div>
    @endif

    <!-- Timeline integrated -->
    <livewire:clinical-history-timeline :patientId="$patient->id" />
</div>
