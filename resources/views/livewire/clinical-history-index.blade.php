<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-800">Historia Clínica Podológica</h3>
        <a href="{{ route('clinical-history.create', ['patientId' => $patient->id]) }}"
            class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition-colors">
            + Nueva Nota de Evolución
        </a>
    </div>

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

    @if($selectedHistory)
        <div class="flex p-1 bg-slate-100 rounded-xl w-fit">
            <button wire:click="setTab('timeline')"
                class="flex items-center gap-2 px-4 py-2 text-sm font-bold rounded-lg transition-all {{ $activeTab === 'timeline' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Notas de Evolución
            </button>
            <button wire:click="setTab('photos')"
                class="flex items-center gap-2 px-4 py-2 text-sm font-bold rounded-lg transition-all {{ $activeTab === 'photos' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Fotografías Clínicas
                @if($selectedHistory->fotografias->count() > 0)
                    <span class="px-1.5 py-0.5 bg-indigo-100 text-indigo-600 text-xs font-bold rounded-full">
                        {{ $selectedHistory->fotografias->count() }}
                    </span>
                @endif
            </button>
            <button wire:click="setTab('documents')"
                class="flex items-center gap-2 px-4 py-2 text-sm font-bold rounded-lg transition-all {{ $activeTab === 'documents' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Consentimiento y Documentos
                @if($selectedHistory->documentos->count() > 0)
                    <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full">
                        {{ $selectedHistory->documentos->count() }}
                    </span>
                @endif
            </button>
        </div>

        <div class="animate-in fade-in slide-in-from-bottom-2 duration-300">
            @if($activeTab === 'timeline')
                <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-sm">
                    <div class="space-y-3 mb-4 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h4 class="font-bold text-slate-700">Detalle de la Nota</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase">Fecha</span>
                                <p class="text-slate-700 font-medium">{{ $selectedHistory->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase">Diagnóstico</span>
                                <p class="text-slate-700 font-medium">{{ $selectedHistory->diagnostico }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase">Procedimiento</span>
                                <p class="text-slate-700">{{ $selectedHistory->procedimiento }}</p>
                            </div>
                        </div>
                        @if($selectedHistory->observaciones)
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase">Observaciones</span>
                                <p class="text-slate-600 text-sm">{{ $selectedHistory->observaciones }}</p>
                            </div>
                        @endif
                    </div>

                    <div wire:click="setTab('photos')" class="flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-700 cursor-pointer mb-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="font-medium">Ver {{ $selectedHistory->fotografias->count() }} fotografía(s) clínica(s)</span>
                    </div>

                    <livewire:clinical-history-timeline :patientId="$patient->id" />
                </div>
            @elseif($activeTab === 'photos')
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="space-y-3 mb-4 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h4 class="font-bold text-slate-700">Fotografías de esta Nota</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase">Fecha</span>
                                <p class="text-slate-700 font-medium">{{ $selectedHistory->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase">Diagnóstico</span>
                                <p class="text-slate-700 font-medium">{{ $selectedHistory->diagnostico }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase">Procedimiento</span>
                                <p class="text-slate-700">{{ $selectedHistory->procedimiento }}</p>
                            </div>
                        </div>
                    </div>
                    <livewire:clinical-history-photos :historyId="$selectedHistory->id" />
                </div>
            @elseif($activeTab === 'documents')
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="space-y-3 mb-4 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h4 class="font-bold text-slate-700">Documentos de esta Nota</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase">Fecha</span>
                                <p class="text-slate-700 font-medium">{{ $selectedHistory->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase">Diagnóstico</span>
                                <p class="text-slate-700 font-medium">{{ $selectedHistory->diagnostico }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase">Procedimiento</span>
                                <p class="text-slate-700">{{ $selectedHistory->procedimiento }}</p>
                            </div>
                        </div>
                    </div>
                    <livewire:clinical-history-documents :historyId="$selectedHistory->id" />
                </div>
            @endif
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-8 text-center text-slate-500">
                <p>No hay notas de evolución registradas para este paciente.</p>
            </div>
        </div>
    @endif
</div>