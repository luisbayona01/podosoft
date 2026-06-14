<div class="p-6 bg-white rounded-xl shadow-lg border border-gray-100">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 border-b pb-6">
        <div>
            <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Registrar Nuevo Insumo</h2>
            <p class="text-sm text-gray-500 mt-1">Complete la información para dar de alta un nuevo material en el inventario.</p>
        </div>
        <div class="flex bg-gray-100 p-1 rounded-xl border border-gray-200">
            <button wire:click="$set('activeTab', 'insumo')"
                class="px-4 py-2 text-sm font-semibold rounded-lg transition-all duration-200 {{ $activeTab === 'insumo' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200' }}">
                Insumo
            </button>
            <button wire:click="$set('activeTab', 'category')"
                class="px-4 py-2 text-sm font-semibold rounded-lg transition-all duration-200 {{ $activeTab === 'category' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200' }}">
                Nueva Categoría
            </button>
        </div>
    </div>

    @if($activeTab === 'insumo')
        <form wire:submit.prevent="save" class="space-y-8">
            <!-- Sección 1: Información General -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50/50 p-6 rounded-2xl border border-gray-100">
                <div class="md:col-span-3 flex items-center gap-2 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Información General</h3>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">Nombre del Insumo</label>
                    <div class="relative">
                        <input type="text" wire:model="nombre" placeholder="Ej. Gasas Estériles"
                            class="w-full pl-3 pr-3 py-2 rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    </div>
                    @error('nombre') <span class="text-red-500 text-xs flex items-center gap-1 mt-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" /></svg>
                        {{ $message }}
                    </span> @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">Código SKU</label>
                    <input type="text" wire:model="codigo_sku" placeholder="SKU-0000"
                        class="w-full pl-3 pr-3 py-2 rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    @error('codigo_sku') <span class="text-red-500 text-xs flex items-center gap-1 mt-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" /></svg>
                        {{ $message }}
                    </span> @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">Categoría</label>
                    <select wire:model="categoria_id"
                        class="w-full pl-3 pr-3 py-2 rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        <option value="">Seleccione una categoría...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->nombre }}</option>
                        @endforeach
                    </select>
                    @error('categoria_id') <span class="text-red-500 text-xs flex items-center gap-1 mt-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" /></svg>
                        {{ $message }}
                    </span> @enderror
                </div>
            </div>

            <!-- Sección 2: Inventario y Control -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50/50 p-6 rounded-2xl border border-gray-100">
                <div class="md:col-span-3 flex items-center gap-2 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Inventario y Control</h3>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">Unidad de Medida</label>
                    <select wire:model="unidad_medida"
                        class="w-full pl-3 pr-3 py-2 rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        <option value="unidad">Unidad (und)</option>
                        <option value="ml">Mililitros (ml)</option>
                        <option value="gr">Gramos (gr)</option>
                        <option value="cm">Centímetros (cm)</option>
                    </select>
                    @error('unidad_medida') <span class="text-red-500 text-xs flex items-center gap-1 mt-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" /></svg>
                        {{ $message }}
                    </span> @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">Stock Actual</label>
                    <input type="number" wire:model="stock_actual" placeholder="0"
                        class="w-full pl-3 pr-3 py-2 rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    @error('stock_actual') <span class="text-red-500 text-xs flex items-center gap-1 mt-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" /></svg>
                        {{ $message }}
                    </span> @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700">Stock Mínimo (Alerta)</label>
                    <input type="number" wire:model="stock_minimo" placeholder="0"
                        class="w-full pl-3 pr-3 py-2 rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    @error('stock_minimo') <span class="text-red-500 text-xs flex items-center gap-1 mt-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" /></svg>
                        {{ $message }}
                    </span> @enderror
                </div>
            </div>

            <!-- Sección 3: Atributos Dinámicos -->
            @if(!empty($categoryAttributes))
                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Características Específicas</h3>
                        </div>
                        <a href="{{ route('category-attributes.manage', ['categoria_id' => $categoria_id]) }}"
                            class="text-xs font-bold text-indigo-600 hover:text-indigo-800 hover:underline transition-colors flex items-center gap-1">
                            Gestionar Atributos $\to$
                        </a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($categoryAttributes as $attr)
                            <div class="space-y-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase">{{ $attr->nombre }}</label>
                                @if($attr->tipo === 'boolean')
                                    <select wire:model="attrValues.{{ $attr->id }}"
                                        class="w-full pl-3 pr-3 py-2 rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all">
                                        <option value="">Seleccione...</option>
                                        <option value="Si">Si</option>
                                        <option value="No">No</option>
                                    </select>
                                @elseif($attr->tipo === 'number')
                                    <input type="number" wire:model="attrValues.{{ $attr->id }}"
                                        class="w-full pl-3 pr-3 py-2 rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                                        placeholder="0.00" step="0.01">
                                @else
                                    <input type="text" wire:model="attrValues.{{ $attr->id }}"
                                        class="w-full pl-3 pr-3 py-2 rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                                        placeholder="Valor de {{ $attr->nombre }}">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex justify-end items-center space-x-4 pt-4">
                <a href="{{ route('insumos.index') }}"
                    class="px-5 py-2.5 text-sm font-semibold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-800 transition-all shadow-sm">
                    Cancelar
                </a>
                <button type="submit" wire:loading.attr="disabled"
                    class="px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 flex items-center gap-2 transition-all shadow-md shadow-indigo-200">
                    <span wire:loading.remove wire:target="save">Guardar Insumo</span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.291z"></path>
                        </svg>
                        Guardando...
                    </span>
                </button>
            </div>
        </form>
    @else
        <!-- Category Tab -->
        <div class="max-w-md mx-auto py-12">
            <div class="bg-indigo-50 p-8 rounded-2xl border border-indigo-100 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 bg-indigo-600 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-indigo-900">Crear Nueva Categoría</h3>
                </div>
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-indigo-700 mb-2">Nombre de la Categoría</label>
                        <input type="text" wire:model="newCategoryNombre" placeholder="Ej. Materiales Quirúrgicos"
                            class="w-full pl-3 pr-3 py-2 rounded-lg border-indigo-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        @error('newCategoryNombre') <span class="text-red-500 text-xs flex items-center gap-1 mt-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><line x1="12" y1="8" x2="12" y2="12" /><line x1="12" y1="16" x2="12.01" y2="16" /></svg>
                            {{ $message }}
                        </span> @enderror
                    </div>
                    <button wire:click="saveCategory" wire:loading.attr="disabled"
                        class="w-full px-4 py-3 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-all shadow-md shadow-indigo-200 flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="saveCategory">Guardar Categoría</span>
                        <span wire:loading wire:target="saveCategory" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.291z"></path>
                            </svg>
                            Guardando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>