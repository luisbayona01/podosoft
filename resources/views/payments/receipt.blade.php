<div class="p-8 bg-white max-w-md mx-auto border border-slate-200 shadow-lg rounded-xl font-mono text-sm">
    <div class="text-center border-b border-dashed border-slate-300 pb-6 mb-6">
        <h2 class="text-xl font-bold uppercase tracking-widest text-slate-900">Comprobante de Pago</h2>
        <p class="text-slate-500">{{ config('app.name', 'Clínica de Podología') }}</p>
        <p class="text-xs text-slate-400 mt-1">Sede: {{ $pago->cita?->sede?->nombre ?? 'Sede Principal' }}</p>
    </div>

    <div class="space-y-4">
        <div class="flex justify-between">
            <span class="text-slate-500">Recibo No:</span>
            <span class="font-bold text-slate-900">{{ $pago->comprobante_numero }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Fecha:</span>
            <span class="font-bold text-slate-900">{{ $pago->fecha_pago->format('d/m/Y H:i') }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-500">Cliente:</span>
            <span class="font-bold text-slate-900 text-right">{{ $pago->factura?->cliente_display ?? trim(($pago->paciente?->nombre ?? '') . ' ' . ($pago->paciente?->apellido ?? '')) ?: 'Cliente ocasional' }}</span>
        </div>

        @if($pago->factura)
        <div class="border-t border-b border-slate-100 py-4 my-4 space-y-2">
            <div class="flex justify-between text-xs text-slate-400">
                <span>Factura:</span>
                <span class="font-semibold">{{ $pago->factura->numero_factura }}</span>
            </div>
            @foreach($pago->factura->items as $item)
            <div class="flex justify-between">
                <span class="text-slate-600">{{ $item->descripcion }} x{{ (float) $item->cantidad }}</span>
                <span>${{ number_format($item->subtotal, 0, ',', '.') }}</span>
            </div>
            @endforeach
            @if($pago->factura->descuento > 0)
            <div class="flex justify-between text-red-500">
                <span>Descuentos:</span>
                <span>-${{ number_format($pago->factura->descuento, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>
        @else
        <div class="border-t border-b border-slate-100 py-4 my-4 space-y-2">
            <div class="flex justify-between">
                <span class="text-slate-600">Servicio:</span>
                <span class="font-medium">{{ $pago->servicio->nombre ?? 'Consulta General' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-600">Valor Base:</span>
                <span>${{ number_format($pago->valor, 2) }}</span>
            </div>
            <div class="flex justify-between text-red-500">
                <span>Descuento:</span>
                <span>-${{ number_format($pago->descuento, 2) }}</span>
            </div>
        </div>
        @endif

        <div class="flex justify-between text-lg font-bold text-slate-900">
            <span>Total Pagado:</span>
            <span>${{ number_format($pago->monto_final, 2) }}</span>
        </div>
        
        <div class="flex justify-between text-xs italic text-slate-500">
            <span>Método de Pago:</span>
            <span>{{ $pago->metodo_pago }}</span>
        </div>
    </div>

    <div class="mt-10 text-center border-t border-slate-100 pt-6">
        <p class="text-xs text-slate-400">¡Gracias por su confianza!</p>
        <p class="text-[10px] text-slate-300 mt-1">Este es un comprobante interno de pago.</p>
    </div>
</div>
