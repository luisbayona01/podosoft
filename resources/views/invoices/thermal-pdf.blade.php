<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $factura->numero_factura }}</title>
    <style>
        @page { margin: 4mm 2mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #000; margin: 0; }
        .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 4px; margin-bottom: 4px; }
        .header h1 { font-size: 11px; margin: 0 0 2px; text-transform: uppercase; }
        .header p { margin: 1px 0; }
        .divider { border-top: 1px dashed #000; margin: 4px 0; }
        table.rows { width: 100%; border-collapse: collapse; }
        table.rows td { padding: 1px 0; vertical-align: top; }
        td.v { text-align: right; font-weight: bold; }
        .total-row td { font-size: 11px; font-weight: bold; padding-top: 3px; }
        .footer { text-align: center; margin-top: 8px; font-size: 8px; border-top: 1px dashed #000; padding-top: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $tenantName }}</h1>
        @if($factura->cita?->sede)
            <p>{{ $factura->cita->sede->nombre }}</p>
        @endif
        <p>FACTURA DE VENTA</p>
        <p>{{ $factura->numero_factura }}</p>
    </div>

    <table class="rows">
        <tr><td>Fecha:</td><td class="v">{{ $factura->fecha_emision?->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Cliente:</td><td class="v">{{ $factura->cliente_display }}</td></tr>
        @if($factura->cliente_documento || $factura->paciente?->documento)
        <tr><td>Doc:</td><td class="v">{{ $factura->cliente_documento ?: $factura->paciente?->documento }}</td></tr>
        @endif
        <tr><td>Estado:</td><td class="v">{{ strtoupper($factura->estado) }}</td></tr>
    </table>

    <div class="divider"></div>

    <table class="rows">
        <tr>
            <td style="font-weight: bold;">Descripción</td>
            <td style="font-weight: bold; text-align: center; width: 24px;">Cant</td>
            <td style="font-weight: bold;" class="v">Valor</td>
        </tr>
        @foreach($factura->items as $item)
        <tr>
            <td>{{ $item->descripcion }}</td>
            <td style="text-align: center;">{{ (float) $item->cantidad }}</td>
            <td class="v">${{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    <table class="rows">
        <tr><td>Subtotal:</td><td class="v">${{ number_format($factura->subtotal, 0, ',', '.') }}</td></tr>
        @if($factura->descuento > 0)
        <tr><td>Descuento:</td><td class="v">-${{ number_format($factura->descuento, 0, ',', '.') }}</td></tr>
        @endif
        @if($factura->impuestos > 0)
        <tr><td>Impuestos:</td><td class="v">${{ number_format($factura->impuestos, 0, ',', '.') }}</td></tr>
        @endif
        <tr class="total-row"><td>TOTAL:</td><td class="v">${{ number_format($factura->total, 0, ',', '.') }}</td></tr>
    </table>

    @if($factura->pagos->where('estado', '!=', 'anulado')->count())
        <div class="divider"></div>
        <table class="rows">
            @foreach($factura->pagos->where('estado', '!=', 'anulado') as $pago)
            <tr><td>{{ $pago->metodo_pago }}:</td><td class="v">${{ number_format($pago->monto_final ?? $pago->valor, 0, ',', '.') }}</td></tr>
            @endforeach
        </table>
    @endif

    <div class="footer">
        <p>Gracias por su compra</p>
        <p>{{ $tenantName }}</p>
    </div>
</body>
</html>
