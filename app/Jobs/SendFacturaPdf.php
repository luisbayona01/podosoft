<?php

namespace App\Jobs;

use App\Http\Controllers\InvoicePdfController;
use App\Models\BotBlockedContact;
use App\Models\Factura;
use App\Models\TenantWhatsAppAccount;
use App\Services\EvolutionApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFacturaPdf implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 120;

    public function __construct(
        public int $facturaId,
        public string $phone,
    ) {
        $this->onQueue('facturas');
    }

    /**
     * Generates the invoice thermal PDF and sends it to the given
     * WhatsApp number via Evolution API.
     */
    public function handle(): void
    {
        $factura = Factura::with(['items', 'paciente', 'pagos', 'cita.sede'])->find($this->facturaId);

        if (!$factura) {
            Log::warning('[SendFacturaPdf] Factura not found', ['factura_id' => $this->facturaId]);
            return;
        }

        $phone = BotBlockedContact::normalizePhone($this->phone);

        if (!$phone) {
            Log::warning('[SendFacturaPdf] Invalid phone', [
                'factura_id' => $factura->id,
                'phone' => $this->phone,
            ]);
            return;
        }

        $account = TenantWhatsAppAccount::where('tenant_id', $factura->tenant_id)
            ->where('status', 'connected')
            ->first();

        if (!$account) {
            Log::warning('[SendFacturaPdf] No connected WhatsApp account', [
                'tenant_id' => $factura->tenant_id,
                'factura_id' => $factura->id,
            ]);
            return;
        }

        try {
            $pdf = InvoicePdfController::buildPdf($factura);

            $pdfContents = $pdf->output();

            Log::info('[SendFacturaPdf] PDF generated', [
                'factura_id' => $factura->id,
                'pdf_bytes' => strlen($pdfContents),
                'items' => $factura->items->count(),
            ]);

            $fileName = "factura-{$factura->numero_factura}.pdf";

            $caption = "🧾 Factura {$factura->numero_factura}\n"
                . "Total: $" . number_format($factura->total, 0, ',', '.') . "\n\n"
                . "Gracias por su compra.";

            $sent = app(EvolutionApiService::class)
                ->fromAccount($account)
                ->sendDocument($phone, $pdfContents, $fileName, $caption);

            Log::info('[SendFacturaPdf] Invoice sent', [
                'factura_id' => $factura->id,
                'phone' => $phone,
                'sent' => $sent,
            ]);
        } catch (\Exception $e) {
            Log::error('[SendFacturaPdf] Failed to send invoice', [
                'factura_id' => $factura->id,
                'error' => $e->getMessage(),
            ]);
            throw $e; // permite reintentos de la cola
        }
    }
}
