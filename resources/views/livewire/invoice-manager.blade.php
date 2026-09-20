<div class="p-6 bg-slate-50 min-h-screen">
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('invoices.index') }}" class="p-2 bg-white border border-slate-200 rounded-lg text-slate-500 hover:text-indigo-600 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Nueva Factura</h2>
                <p class="text-slate-500 text-sm">Factura comercial independiente. Paciente y cita son opcionales.</p>
            </div>
        </div>

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ session('error') }}</div>
        @endif

        <form wire:submit.prevent="save" class="space-y-6">

            {{-- ============ CLIENTE / PACIENTE (OPCIONAL) ============ --}}
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Cliente / Paciente <span class="text-slate-400 font-normal">(opcional)</span></h3>

                @if($pacienteSeleccionado)
                    <div class="flex items-center justify-between p-3 bg-indigo-50 border border-indigo-100 rounded-xl">
                        <div>
                            <div class="font-semibold text-indigo-900">{{ $pacienteSeleccionado->nombre }} {{ $pacienteSeleccionado->apellido }}</div>
                            <div class="text-xs text-indigo-600">{{ $pacienteSeleccionado->documento ?? 'Sin documento' }}</div>
                        </div>
                        <button type="button" wire:click="clearPaciente" class="text-xs font-medium text-red-600 hover:underline">Quitar</button>
                    </div>
                @elseif(!$sin_paciente)
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="buscarCliente" placeholder="Buscar paciente por nombre o documento..." class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                        @if(count($resultadosClientes))
                            <div class="absolute z-10 inset-x-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg divide-y divide-slate-100">
                                @foreach($resultadosClientes as $cliente)
                                    <button type="button" wire:click="selectPaciente({{ $cliente->id }})" class="w-full text-left px-4 py-2 hover:bg-indigo-50">
                                        <span class="font-medium text-slate-800">{{ $cliente->nombre }} {{ $cliente->apellido }}</span>
                                        <span class="text-xs text-slate-500 ml-2">{{ $cliente->documento }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @error('paciente_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                @endif

                @if(!$pacienteSeleccionado)
                    <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                        <input type="checkbox" wire:model.live="sin_paciente" class="rounded border-slate-300 text-indigo-600">
                        Facturar sin paciente (cliente ocasional)
                    </label>
                @endif

                @if($sin_paciente)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Nombre del cliente</label>
                            <input type="text" wire:model="cliente_nombre" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Nombre o razón social">
                            @error('cliente_nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Documento / NIT <span class="text-slate-400 font-normal">(opcional)</span></label>
                            <input type="text" wire:model="cliente_documento" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">WhatsApp / Teléfono</label>
                            <input type="text" wire:model="cliente_telefono" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Ej: 573001234567">
                            @error('cliente_telefono') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <p class="text-xs text-slate-400">El cliente quedará registrado en tu base de datos y podrás enviarle facturas por WhatsApp.</p>
                @endif
            </div>

            {{-- ============ ORIGEN: CITA (OPCIONAL) ============ --}}
            @if(!$sin_paciente)
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                    <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Origen <span class="text-slate-400 font-normal">(opcional)</span></h3>
                    <select wire:model.live="cita_id" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">Sin cita asociada</option>
                        @foreach($citas as $cita)
                            <option value="{{ $cita->id }}">
                                Cita #{{ $cita->id }} — {{ $cita->paciente?->nombre }} {{ $cita->paciente?->apellido }} — {{ $cita->fecha_hora?->format('d/m/Y H:i') }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-400">Al seleccionar una cita se precarga el paciente y sus servicios, pero puede modificarlos libremente.</p>
                    @error('cita_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            @endif

            {{-- ============ ÍTEMS ============ --}}
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Detalle de la factura</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-500">Agregar servicio</label>
                        <select wire:model.live="nuevoServicioId" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">Seleccione servicio...</option>
                            @foreach($servicios as $servicio)
                                <option value="{{ $servicio->id }}">{{ $servicio->nombre }} — ${{ number_format($servicio->precio, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-500">Agregar producto</label>
                        <select wire:model.live="nuevoInsumoId" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">Seleccione producto...</option>
                            @foreach($insumos as $insumo)
                                <option value="{{ $insumo->id }}">{{ $insumo->nombre }} — stock: {{ (float) $insumo->stock_actual }} — ${{ number_format($insumo->precio_venta, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @error('items') <div class="text-red-500 text-xs">{{ $message }}</div> @enderror

                @if(count($items))
                    <div class="overflow-x-auto border border-slate-100 rounded-xl">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                                <tr>
                                    <th class="px-3 py-2 text-left">Tipo</th>
                                    <th class="px-3 py-2 text-left">Descripción</th>
                                    <th class="px-3 py-2 text-center w-24">Cant.</th>
                                    <th class="px-3 py-2 text-center w-32">Precio</th>
                                    <th class="px-3 py-2 text-center w-28">Descuento</th>
                                    <th class="px-3 py-2 text-right">Subtotal</th>
                                    <th class="px-3 py-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($items as $index => $item)
                                    <tr wire:key="item-{{ $index }}">
                                        <td class="px-3 py-2">
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $item['tipo'] === 'servicio' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600' }}">
                                                {{ $item['tipo'] === 'servicio' ? 'Servicio' : 'Producto' }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text" wire:model="items.{{ $index }}.descripcion" class="w-full border border-slate-200 rounded-lg py-1 px-2 text-sm">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" min="0.01" step="0.01" wire:model.live="items.{{ $index }}.cantidad" class="w-20 border border-slate-200 rounded-lg py-1 px-2 text-sm text-center">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" min="0" step="0.01" wire:model.live="items.{{ $index }}.precio" class="w-28 border border-slate-200 rounded-lg py-1 px-2 text-sm text-right">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" min="0" step="0.01" wire:model.live="items.{{ $index }}.descuento" class="w-24 border border-slate-200 rounded-lg py-1 px-2 text-sm text-right">
                                        </td>
                                        <td class="px-3 py-2 text-right font-semibold text-slate-800">
                                            ${{ number_format(max(0, ((float) ($item['cantidad'] ?: 0) * (float) ($item['precio'] ?: 0)) - (float) ($item['descuento'] ?: 0)), 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <button type="button" wire:click="removeItem({{ $index }})" class="text-red-400 hover:text-red-600">&times;</button>
                                        </td>
                                    </tr>
                                    @error("items.$index.cantidad") <tr><td colspan="7" class="text-red-500 text-xs px-3">{{ $message }}</td></tr> @enderror
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-col items-end gap-2 pt-2">
                        <div class="w-full md:w-72 space-y-2">
                            <div class="flex justify-between text-sm text-slate-600">
                                <span>Subtotal</span>
                                <span>${{ number_format($this->subtotal(), 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-slate-600">
                                <span>Descuentos</span>
                                <span>-${{ number_format($this->descuentoTotal(), 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm text-slate-600">
                                <span>Impuestos</span>
                                <input type="number" min="0" step="0.01" wire:model.live="impuestos" class="w-28 border border-slate-200 rounded-lg py-1 px-2 text-right">
                            </div>
                            <div class="flex justify-between text-lg font-bold text-slate-900 border-t border-slate-200 pt-2">
                                <span>Total</span>
                                <span>${{ number_format($this->total(), 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-slate-400 text-center py-4">Agregue servicios o productos para armar la factura.</p>
                @endif
            </div>

            {{-- ============ PAGO ============ --}}
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 cursor-pointer">
                    <input type="checkbox" wire:model.live="registrar_pago" class="rounded border-slate-300 text-indigo-600">
                    Registrar pago inmediatamente
                </label>

                @if($registrar_pago)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Método de pago</label>
                            <select wire:model="metodo_pago" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                                @foreach($metodosPago as $metodo)
                                    <option value="{{ $metodo }}">{{ $metodo }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Observaciones</label>
                            <input type="text" wire:model="observaciones" class="w-full py-2 px-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Notas adicionales...">
                        </div>
                    </div>
                @else
                    <p class="text-xs text-slate-400">La factura quedará en estado "borrador" y podrá pagarse después. No se descontará inventario.</p>
                @endif
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('invoices.index') }}" class="px-6 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-all">Cancelar</a>
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-all shadow-sm">
                    {{ $registrar_pago ? 'Crear factura y registrar pago' : 'Guardar borrador' }}
                </button>
            </div>
        </form>
    </div>
</div>
