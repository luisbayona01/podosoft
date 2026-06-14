<div class="p-6 bg-white rounded-lg shadow-md">
    <div class="flex items-center justify-between mb-6 border-b pb-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Características de {{ $categoria->nombre }}</h2>
            <p class="text-sm text-gray-500">Define los atributos que deben tener los insumos de esta categoría.</p>
        </div>
        <a href="{{ route('insumos.index') }}" class="text-sm text-indigo-600 hover:underline">Volver a Insumos</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Formulario de creación -->
        <div class="lg:col-span-1 bg-slate-50 p-6 rounded-2xl border border-slate-200 h-fit">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Agregar Atributo</h3>
            <form wire:submit.prevent="saveAttribute" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre del Atributo</label>
                    <input type="text" wire:model="nombre_atributo" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ej: Talla, Color, Concentración">
                    @error('nombre_atributo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo de Dato</label>
                    <select wire:model="tipo_atributo" class="w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="text">Texto</option>
                        <option value="number">Número</option>
                        <option value="boolean">Si / No (Booleano)</option>
                    </select>
                    @error('tipo_atributo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                    Guardar Característica
                </button>
            </form>
        </div>

        <!-- Lista de atributos -->
        <div class="lg:col-span-2">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Atributo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($attributes as $attr)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $attr->nombre }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $attr->tipo === 'text' ? 'bg-blue-100 text-blue-700' : ($attr->tipo === 'number' ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700') }}">
                                        {{ ucfirst($attr->tipo) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="deleteAttribute({{ $attr->id }})" class="text-red-600 hover:text-red-900 transition-colors">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-sm text-gray-500">
                                    No hay características definidas para esta categoría.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
