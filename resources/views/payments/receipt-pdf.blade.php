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
        table.rows { width: 100%; border-collapse: collapse; }
        table.rows td { padding: 4px 0; vertical-align: top; }
        td.label { color: #64748b; }
        td.value { text-align: right; font-weight: bold; }
        .section { border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; padding: 10px 0; margin: 12px 0; }
        .total td { font-size: 15px; padding-top: 8px; }
        tr.discount td { color: #dc2626; }
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

        <table class="rows">
            <tr><td class="label">Recibo No:</td><td class="value">{{ $pago->comprobante_numero }}</td></tr>
            <tr><td class="label">Fecha:</td><td class="value">{{ $pago->fecha_pago->format('d/m/Y') }}</td></tr>
            <tr><td class="label">Cliente:</td><td class="value">{{ $pago->factura?->cliente_display ?? trim(($pago->paciente?->nombre ?? '') . ' ' . ($pago->paciente?->apellido ?? '')) ?: 'Cliente ocasional' }}</td></tr>
            <tr><td class="label">Método de pago:</td><td class="value">{{ $pago->metodo_pago }}</td></tr>
        </table>

        <div class="section">
            <table class="rows">
                @if($pago->factura)
                    <tr><td class="label">Factura:</td><td class="value">{{ $pago->factura->numero_factura }}</td></tr>
                    @foreach($pago->factura->items as $item)
                    <tr><td class="label">{{ $item->descripcion }} x{{ (float) $item->cantidad }}</td><td class="value">${{ number_format($item->subtotal, 0, ',', '.') }}</td></tr>
                    @endforeach
                    @if($pago->factura->descuento > 0)
                    <tr class="discount"><td class="label">Descuentos:</td><td class="value">-${{ number_format($pago->factura->descuento, 0, ',', '.') }}</td></tr>
                    @endif
                @else
                    <tr><td class="label">Servicio:</td><td class="value">{{ $pago->servicio->nombre ?? 'Consulta General' }}</td></tr>
                    <tr><td class="label">Valor base:</td><td class="value">${{ number_format($pago->valor, 0, ',', '.') }}</td></tr>
                    @if($pago->descuento > 0)
                    <tr class="discount"><td class="label">Descuento:</td><td class="value">-${{ number_format($pago->descuento, 0, ',', '.') }}</td></tr>
                    @endif
                @endif
            </table>
        </div>

        <table class="rows total">
            <tr><td class="label">Total pagado:</td><td class="value">${{ number_format($pago->monto_final, 0, ',', '.') }}</td></tr>
        </table>

        @if($pago->observaciones)
        <div class="section">
            <table class="rows">
                <tr><td class="label">Observaciones:</td></tr>
            </table>
            <p>{{ $pago->observaciones }}</p>
        </div>
        @endif

        <div class="footer">
            <p>Gracias por su confianza.</p>
        </div>
    </div>
</body>
</html>
