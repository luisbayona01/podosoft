<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div class="flex items-center gap-3">
            <h3 class="text-lg font-bold text-gray-800">Fotografías Clínicas</h3>
            <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-700 text-xs font-bold rounded-full">
                {{ $photos->count() }} foto{{ $photos->count() !== 1 ? 's' : '' }}
            </span>
        </div>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div class="space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h4 class="font-bold text-slate-700">Subir Nueva Fotografía</h4>
            </div>

            <form wire:submit="savePhoto" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Archivo de imagen
                        </label>
                        <input type="file" wire:model="newPhoto" accept="image/jpeg,image/png,image/jpg,image/webp"
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 transition-colors">
                        @error('newPhoto')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1.5 text-xs text-slate-400">
                            Formatos: jpg, jpeg, png, webp. Máximo: 5 MB.
                        </p>
                        <div wire:loading wire:target="newPhoto" class="mt-2 flex items-center gap-2 text-xs text-indigo-600">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Procesando imagen...
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Observaciones (opcional)
                        </label>
                        <textarea wire:model="observations" rows="3" placeholder="Ej: Vista plantar del pie derecho..."
                            class="block w-full text-sm rounded-xl border-slate-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        @error('observations')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition-all active:scale-95 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg wire:loading.remove wire:target="savePhoto" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <svg wire:loading wire:target="savePhoto" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="savePhoto">Subir Fotografía</span>
                        <span wire:loading wire:target="savePhoto">Subiendo...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($photos->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($photos as $photo)
                <div class="group relative bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                    <div class="aspect-square bg-slate-100">
                        @php
                            $photoUrl = route('clinical-history.photo', ['photo' => $photo->id]);
                        @endphp
                        <img src="{{ $photoUrl }}" alt="{{ $photo->nombre_archivo }}"
                            class="w-full h-full object-cover cursor-pointer hover:opacity-90 transition-opacity"
                            wire:click="viewPhoto({{ $photo->id }})">
                    </div>

                    <div class="p-2.5 border-t border-slate-100">
                        <p class="text-xs font-medium text-slate-700 truncate" title="{{ $photo->nombre_archivo }}">
                            {{ $photo->nombre_archivo }}
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $photo->created_at->format('d/m/Y H:i') }}
                        </p>
                        @if($photo->observaciones)
                            <p class="text-xs text-slate-500 mt-1 truncate" title="{{ $photo->observaciones }}">
                                {{ $photo->observaciones }}
                            </p>
                        @endif
                    </div>

                    <button wire:click="confirmDelete({{ $photo->id }})"
                        class="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-lg opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-8 text-center">
            <div class="w-16 h-16 mx-auto bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <p class="text-slate-600 font-semibold">No hay fotografías registradas</p>
            <p class="text-slate-400 text-sm mt-1">Sube la primera fotografía usando el formulario superior.</p>
        </div>
    @endif

    @if($showModal && $selectedPhoto)
        <div x-data="{ open: @entangle('showModal') }" x-show="open" x-on:keydown.escape.window="open = false"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden"
                x-on:click.stop>
                <div class="flex items-center justify-between p-4 border-b border-slate-200">
                    <div>
                        <h3 class="font-bold text-slate-800">{{ $selectedPhoto->nombre_archivo }}</h3>
                        @if($selectedPhoto->observaciones)
                            <p class="text-sm text-slate-500 mt-0.5">{{ $selectedPhoto->observaciones }}</p>
                        @endif
                    </div>
                    <button wire:click="closeModal" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="p-4 bg-slate-900 flex items-center justify-center" style="max-height: 70vh;">
                    <img src="{{ route('clinical-history.photo', ['photo' => $selectedPhoto->id]) }}"
                        alt="{{ $selectedPhoto->nombre_archivo }}"
                        class="max-w-full max-h-full object-contain rounded-lg">
                </div>

                <div class="p-4 border-t border-slate-200 flex justify-between items-center text-xs text-slate-500">
                    <span>Subida el {{ $selectedPhoto->created_at->format('d/m/Y H:i:s') }}</span>
                    <div class="flex gap-2">
                        <a href="{{ route('clinical-history.photo.download', ['photo' => $selectedPhoto->id]) }}"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Descargar
                        </a>
                        <button wire:click="closeModal"
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div x-data="{ showConfirm: false }" @show-confirm-delete.window="showConfirm = true"
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
                <h3 class="text-lg font-bold text-slate-800 mb-2">¿Eliminar fotografía?</h3>
                <p class="text-slate-500 text-sm mb-6">
                    Esta acción no se puede deshacer. La fotografía "{{ $photoToDelete?->nombre_archivo }}" será eliminada permanentemente.
                </p>
                <div class="flex gap-3">
                    <button wire:click="cancelDelete" @click="showConfirm = false"
                        class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">
                        Cancelar
                    </button>
                    <button wire:click="deletePhoto" @click="showConfirm = false"
                        class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-red-500 rounded-xl hover:bg-red-600 transition-colors">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>