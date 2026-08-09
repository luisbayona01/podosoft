<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div class="flex items-center gap-3">
            <h3 class="text-lg font-bold text-gray-800">Consentimiento y Documentos</h3>
            <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full">
                {{ $documents->count() }} documento{{ $documents->count() !== 1 ? 's' : '' }}
            </span>
        </div>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h4 class="font-bold text-slate-700">Adjuntar Documento de Aceptación del Tratamiento</h4>
            </div>

            <form wire:submit="saveDocument" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Tipo de documento
                        </label>
                        <select wire:model="tipo" class="block w-full text-sm rounded-xl border-slate-200 shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="consentimiento">Consentimiento / Aceptación de tratamiento</option>
                            <option value="otros">Otro documento clínico</option>
                        </select>
                        @error('tipo')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Archivo (consentimiento firmado, autorización, etc.)
                        </label>
                        <input type="file" wire:model="newDocument" accept="application/pdf,image/jpeg,image/png,image/jpg,image/webp,.doc,.docx"
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-600 hover:file:bg-emerald-100 transition-colors">
                        @error('newDocument')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1.5 text-xs text-slate-400">
                            Formatos: pdf, jpg, jpeg, png, webp, doc, docx. Máximo: 10 MB.
                        </p>
                        <div wire:loading wire:target="newDocument" class="mt-2 flex items-center gap-2 text-xs text-emerald-600">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Subiendo documento...
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Observaciones (opcional)
                        </label>
                        <textarea wire:model="observations" rows="2" placeholder="Ej: Consentimiento firmado por el paciente el 09/08/2026..."
                            class="block w-full text-sm rounded-xl border-slate-200 shadow-sm focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                        @error('observations')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-bold rounded-xl hover:bg-emerald-700 transition-all active:scale-95 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg wire:loading.remove wire:target="saveDocument" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <svg wire:loading wire:target="saveDocument" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="saveDocument">Adjuntar Documento</span>
                        <span wire:loading wire:target="saveDocument">Adjuntando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($documents->count() > 0)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h4 class="font-bold text-slate-700">Documentos adjuntos</h4>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($documents as $document)
                    <div class="px-5 py-4 flex items-center justify-between gap-4 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 shrink-0 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-800 truncate" title="{{ $document->nombre_archivo }}">
                                    {{ $document->nombre_archivo }}
                                </p>
                                <p class="text-xs text-slate-400">
                                    {{ $document->tipo === 'consentimiento' ? 'Consentimiento / Aceptación de tratamiento' : 'Documento clínico' }}
                                    &middot; {{ $document->created_at->format('d/m/Y H:i') }}
                                </p>
                                @if($document->observaciones)
                                    <p class="text-xs text-slate-500 mt-1 truncate" title="{{ $document->observaciones }}">
                                        {{ $document->observaciones }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('clinical-history.document', ['document' => $document->id]) }}" target="_blank"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">
                                Ver
                            </a>
                            <a href="{{ route('clinical-history.document.download', ['document' => $document->id]) }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-emerald-600 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Descargar
                            </a>
                            <button wire:click="confirmDelete({{ $document->id }})"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Eliminar
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-8 text-center">
            <div class="w-16 h-16 mx-auto bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <p class="text-slate-600 font-semibold">No hay documentos de consentimiento registrados</p>
            <p class="text-slate-400 text-sm mt-1">Adjunta el documento de aceptación del tratamiento usando el formulario superior.</p>
        </div>
    @endif

    <div x-data="{ showConfirm: false }" @show-confirm-delete-document.window="showConfirm = true"
        x-show="showConfirm" x-on:keydown.escape.window="showConfirm = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6"
            x-on:click.stop>
            <div class="text-center">
                <div class="w-14 h-14 mx-auto bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-2">¿Eliminar documento?</h3>
                <p class="text-slate-500 text-sm mb-6">
                    Esta acción no se puede deshacer. El documento "{{ $documentToDelete?->nombre_archivo }}" será eliminado permanentemente.
                </p>
                <div class="flex gap-3">
                    <button wire:click="cancelDelete" @click="showConfirm = false"
                        class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="deleteDocument" @click="showConfirm = false"
                        class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-red-500 rounded-xl hover:bg-red-600 transition-colors">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>