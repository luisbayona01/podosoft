<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfController extends Controller
{
    /**
     * Builds the thermal-printer (80mm) PDF for a given invoice.
     */
    public static function buildPdf(Factura $factura)
    {
        $factura->loadMissing(['items', 'paciente', 'pagos', 'cita.sede']);

        $tenantName = Tenant::find($factura->tenant_id)?->nombre ?? config('app.name');

        return Pdf::loadView('invoices.thermal-pdf', [
            'factura' => $factura,
            'tenantName' => $tenantName,
        ])->setPaper([0, 0, 226.77, 841.89], 'portrait'); // 80mm x 297mm
    }

    /**
     * Downloads the invoice as a thermal-printer PDF.
     */
    public function download($id)
    {
        $factura = Factura::findOrFail($id);

        return $this->buildPdf($factura)
            ->download("factura-{$factura->numero_factura}.pdf");
    }
}
