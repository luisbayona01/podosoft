<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('servicios.index') }}" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Nuevo Servicio</h1>
        </div>
        <p class="text-slate-500 text-sm ml-11">Completa la información del servicio que deseas ofrecer.</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 md:p-8">
        <form wire:submit="save" class="space-y-6">
            <!-- Nombre -->
            <div>
                <label for="nombre" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Nombre del Servicio <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model="nombre" id="nombre"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all text-sm"
                    placeholder="Ej: Podología básica">
                @error('nombre')
                    <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Descripción -->
            <div>
                <label for="descripcion" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Descripción
                </label>
                <textarea wire:model="descripcion" id="descripcion" rows="3"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all text-sm resize-none"
                    placeholder="Describe el servicio en detalle..."></textarea>
                @error('descripcion')
                    <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Precio y Duración -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="precio" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Precio <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm">$</span>
                        <input type="number" wire:model="precio" id="precio" step="0.01" min="0"
                            class="w-full pl-8 pr-4 py-3 rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all text-sm"
                            placeholder="0.00">
                    </div>
                    @error('precio')
                        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="duracion" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Duración (minutos) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" wire:model="duracion" id="duracion" min="1" max="480"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all text-sm"
                        placeholder="30">
                    @error('duracion')
                        <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <!-- Estado -->
            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model="activo" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
                <span class="text-sm font-medium text-slate-700">Servicio activo</span>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('servicios.index') }}"
                    class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">
                    Cancelar
                </a>
                <button type="submit"
                    class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-sm shadow-indigo-200 transition-all active:scale-95">
                    Guardar Servicio
                </button>
            </div>
        </form>
    </div>
</div>