<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante {{ $pago->comprobante_numero }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; margin: 0; }
        .wrap { max-width: 480px; margin: 0 auto; padding: 24px; }
        .header { text-align: center; border-bottom: 1px dashed #94a3b8; padding-bottom: 16px; margin-bottom: 16px; }
        .header h1 { font-size: 18px; margin: 0 0 4px; text-transform: uppercase; letter-spacing: 2px; }
        .header p { margin: 2px 0; color: #64748b; }
        .row { width: 100%; padding: 4px 0; overflow: hidden; }
        .row .label { float: left; color: #64748b; }
        .row .value { float: right; font-weight: bold; }
        .section { border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; padding: 10px 0; margin: 12px 0; }
        .total { font-size: 15px; padding-top: 8px; }
        .discount .label, .discount .value { color: #dc2626; }
        .footer { text-align: center; margin-top: 24px; color: #94a3b8; font-size: 10px; border-top: 1px dashed #94a3b8; padding-top: 12px; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="header">
            <h1>Comprobante de Pago</h1>
            <p>{{ $tenantName }}</p>
            <p>Sede: {{ $pago->cita?->sede?->nombre ?? 'Sede Principal' }}</p>
        </div>

        <div class="row"><span class="label">Recibo No:</span><span class="value">{{ $pago->comprobante_numero }}</span></div>
        <div class="row"><span class="label">Fecha:</span><span class="value">{{ $pago->fecha_pago->format('d/m/Y') }}</span></div>
        <div class="row"><span class="label">Cliente:</span><span class="value">{{ $pago->factura?->cliente_display ?? trim(($pago->paciente?->nombre ?? '') . ' ' . ($pago->paciente?->apellido ?? '')) ?: 'Cliente ocasional' }}</span></div>
        <div class="row"><span class="label">Método de pago:</span><span class="value">{{ $pago->metodo_pago }}</span></div>

        <div class="section">
            @if($pago->factura)
                <div class="row"><span class="label">Factura:</span><span class="value">{{ $pago->factura->numero_factura }}</span></div>
                @foreach($pago->factura->items as $item)
                <div class="row"><span class="label">{{ $item->descripcion }} x{{ (float) $item->cantidad }}</span><span class="value">${{ number_format($item->subtotal, 0, ',', '.') }}</span></div>
                @endforeach
                @if($pago->factura->descuento > 0)
                <div class="row discount"><span class="label">Descuentos:</span><span class="value">-${{ number_format($pago->factura->descuento, 0, ',', '.') }}</span></div>
                @endif
            @else
                <div class="row"><span class="label">Servicio:</span><span class="value">{{ $pago->servicio->nombre ?? 'Consulta General' }}</span></div>
                <div class="row"><span class="label">Valor base:</span><span class="value">${{ number_format($pago->valor, 0, ',', '.') }}</span></div>
                @if($pago->descuento > 0)
                <div class="row discount"><span class="label">Descuento:</span><span class="value">-${{ number_format($pago->descuento, 0, ',', '.') }}</span></div>
                @endif
            @endif
        </div>

        <div class="row total"><span class="label">Total pagado:</span><span class="value">${{ number_format($pago->monto_final, 0, ',', '.') }}</span></div>

        @if($pago->observaciones)
        <div class="section">
            <div class="row"><span class="label">Observaciones:</span></div>
            <p>{{ $pago->observaciones }}</p>
        </div>
        @endif

        <div class="footer">
            <p>Gracias por su confianza.</p>
        </div>
    </div>
</body>
</html>
