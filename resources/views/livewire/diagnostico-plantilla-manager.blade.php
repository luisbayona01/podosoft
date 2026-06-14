<div class="p-6 bg-slate-50 min-h-screen">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Gestión de Plantillas de Diagnóstico</h1>
                <p class="text-slate-500">Crea y administra textos predefinidos para agilizar la historia clínica.</p>
            </div>
            <button wire:click="resetForm" x-show="isEditing" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">
                Cancelar Edición
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Formulario -->
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 sticky top-8">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">
                        {{ $isEditing ? 'Editar Plantilla' : 'Nueva Plantilla' }}
                    </h3>
                    <form wire:submit.prevent="save" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre de la Plantilla</label>
                            <input type="text" wire:model="nombre" 
                                class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition-all" 
                                placeholder="Ej: Pododermatosis común">
                            @error('nombre') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Diagnóstico Base</label>
                            <textarea wire:model="diagnostico_base" rows="4" 
                                class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition-all" 
                                placeholder="Texto predefinido del diagnóstico..."></textarea>
                            @error('diagnostico_base') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Procedimiento Base</label>
                            <textarea wire:model="procedimiento_base" rows="4" 
                                class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 outline-none transition-all" 
                                placeholder="Texto predefinido del procedimiento..."></textarea>
                            @error('procedimiento_base') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" 
                            class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-all shadow-md shadow-indigo-100">
                            {{ $isEditing ? 'Actualizar Plantilla' : 'Guardar Plantilla' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Listado -->
            <div class="lg:col-span-2">
                @if(session()->has('success'))
                    <div class="mb-4 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r-lg flex items-center gap-3 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse($templates as $template)
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-indigo-300 transition-all group">
                            <div class="flex justify-between items-start mb-3">
                                <h4 class="font-bold text-slate-800 group-hover:text-indigo-600 transition-colors">{{ $template->nombre }}</h4>
                                <div class="flex gap-2">
                                    <button wire:click="edit({{ $template->id }})" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="delete({{ $template->id }})" 
                                        onclick="confirm('¿Estás seguro de eliminar esta plantilla?') || event.stopImmediatePropagation()"
                                        class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Diagnóstico</p>
                                    <p class="text-sm text-slate-600 line-clamp-2 italic">"{{ $template->diagnostico_base }}"</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Procedimiento</p>
                                    <p class="text-sm text-slate-600 line-clamp-2 italic">"{{ $template->procedimiento_base }}"</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center bg-white rounded-2xl border border-dashed border-slate-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="mx-auto text-slate-300 mb-4">
                                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                                <polyline points="14 2 14 8 20 8"/>
                            </svg>
                            <p class="text-slate-500">No hay plantillas creadas aún.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
