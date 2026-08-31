<?php

namespace App\Jobs;

use App\Models\BotBlockedContact;
use App\Models\Pago;
use App\Models\Tenant;
use App\Models\TenantWhatsAppAccount;
use App\Services\EvolutionApiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPaymentReceiptPdf implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 120;

    public function __construct(
        public int $pagoId,
    ) {}

    /**
     * Generates the payment receipt PDF and sends it to the patient's
     * WhatsApp via Evolution API.
     */
    public function handle(): void
    {
        $pago = Pago::with(['paciente', 'cita.sede', 'servicio'])->find($this->pagoId);

        if (!$pago) {
            Log::warning('[SendPaymentReceiptPdf] Pago not found', ['pago_id' => $this->pagoId]);
            return;
        }

        if ($pago->estado === 'anulado') {
            Log::info('[SendPaymentReceiptPdf] Payment voided, skipping', ['pago_id' => $pago->id]);
            return;
        }

        $phone = BotBlockedContact::normalizePhone($pago->paciente->telefono ?? null);

        if (!$phone) {
            Log::warning('[SendPaymentReceiptPdf] Patient has no phone', [
                'pago_id' => $pago->id,
                'paciente_id' => $pago->paciente_id,
            ]);
            return;
        }

        $account = TenantWhatsAppAccount::where('tenant_id', $pago->tenant_id)
            ->where('status', 'connected')
            ->first();

        if (!$account) {
            Log::warning('[SendPaymentReceiptPdf] No connected WhatsApp account', [
                'tenant_id' => $pago->tenant_id,
                'pago_id' => $pago->id,
            ]);
            return;
        }

        $tenantName = Tenant::find($pago->tenant_id)?->nombre ?? config('app.name');

        try {
            $pdf = Pdf::loadView('payments.receipt-pdf', [
                'pago' => $pago,
                'tenantName' => $tenantName,
            ]);

            $fileName = "comprobante-{$pago->comprobante_numero}.pdf";

            $caption = "🧾 Comprobante de pago {$pago->comprobante_numero}\n"
                . "Servicio: " . ($pago->servicio->nombre ?? 'Consulta General') . "\n"
                . "Total: $" . number_format($pago->monto_final, 0, ',', '.') . "\n\n"
                . "Gracias por tu pago.";

            $sent = app(EvolutionApiService::class)
                ->fromAccount($account)
                ->sendDocument($phone, $pdf->output(), $fileName, $caption);

            Log::info('[SendPaymentReceiptPdf] Receipt sent', [
                'pago_id' => $pago->id,
                'phone' => $phone,
                'sent' => $sent,
            ]);
        } catch (\Exception $e) {
            Log::error('[SendPaymentReceiptPdf] Failed to send receipt', [
                'pago_id' => $pago->id,
                'error' => $e->getMessage(),
            ]);
            throw $e; // permite reintentos de la cola
        }
    }
}
