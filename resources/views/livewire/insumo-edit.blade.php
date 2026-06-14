<div class="p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-2">Editar Insumo</h2>
    
    <form wire:submit.prevent="save" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Nombre del Insumo</label>
            <input type="text" wire:model="nombre" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Código SKU</label>
            <input type="text" wire:model="codigo_sku" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            @error('codigo_sku') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Categoría</label>
            <select wire:model="categoria_id" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Seleccione una categoría...</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->nombre }}</option>
                @endforeach
            </select>
            @error('categoria_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Unidad de Medida</label>
            <select wire:model="unidad_medida" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="unidad">Unidad (und)</option>
                <option value="ml">Mililitros (ml)</option>
                <option value="gr">Gramos (gr)</option>
                <option value="cm">Centímetros (cm)</option>
            </select>
            @error('unidad_medida') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Stock Actual</label>
            <input type="number" wire:model="stock_actual" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            @error('stock_actual') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Stock Mínimo (Alerta)</label>
            <input type="number" wire:model="stock_minimo" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            @error('stock_minimo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="md:col-span-2 flex justify-end space-x-4 mt-6">
            <a href="{{ route('insumos.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500">
                Actualizar Insumo
            </button>
        </div>
    </form>
</div>
