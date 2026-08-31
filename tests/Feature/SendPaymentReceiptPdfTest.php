<?php

namespace Tests\Feature;

use App\Jobs\SendPaymentReceiptPdf;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\Profesional;
use App\Models\Sede;
use App\Models\Servicio;
use App\Models\Tenant;
use App\Models\TenantWhatsAppAccount;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SendPaymentReceiptPdfTest extends TestCase
{
    private Tenant $tenant;
    private Paciente $paciente;
    private Pago $pago;

    protected function setUp(): void
    {
        // Use MySQL for tests (SQLite driver not available in this environment)
        putenv('DB_CONNECTION=mysql');
        $_ENV['DB_CONNECTION'] = 'mysql';
        parent::setUp();
        Config::set('database.default', 'mysql');

        Http::fake(['*' => Http::response(['status' => 'ok'])]);

        $this->tenant = Tenant::create([
            'nombre' => 'Clinica Receipt Test',
            'slug' => 'clinica-receipt-' . uniqid(),
            'email' => uniqid() . '@test.com',
            'telefono' => '111',
            'direccion' => 'Calle 1',
        ]);

        $this->paciente = Paciente::create([
            'tenant_id' => $this->tenant->id,
            'nombre' => 'Paciente',
            'apellido' => 'Test',
            'telefono' => '573001234567',
            'email' => uniqid() . '@test.com',
            'fecha_nacimiento' => '1990-01-01',
            'sexo' => 'Masculino',
            'direccion' => 'Calle 1',
            'consentimiento' => true,
        ]);

        $profesional = Profesional::create([
            'tenant_id' => $this->tenant->id,
            'nombre' => 'Doc',
            'apellido' => 'Test',
            'numero_licencia' => 'LIC-' . uniqid(),
            'email' => 'doc' . uniqid() . '@test.com',
        ]);

        $sede = Sede::create([
            'tenant_id' => $this->tenant->id,
            'nombre' => 'Sede Test',
            'direccion' => 'Cra 1',
        ]);

        $servicio = Servicio::create([
            'tenant_id' => $this->tenant->id,
            'nombre' => 'Consulta',
            'duracion' => 30,
            'precio' => 80000,
        ]);

        $cita = Cita::create([
            'tenant_id' => $this->tenant->id,
            'paciente_id' => $this->paciente->id,
            'profesional_id' => $profesional->id,
            'sede_id' => $sede->id,
            'fecha_hora' => now(),
            'estado' => 'confirmada',
        ]);

        $this->pago = Pago::create([
            'tenant_id' => $this->tenant->id,
            'paciente_id' => $this->paciente->id,
            'cita_id' => $cita->id,
            'servicio_id' => $servicio->id,
            'fecha_pago' => now()->toDateString(),
            'valor' => 80000,
            'descuento' => 0,
            'monto_final' => 80000,
            'metodo_pago' => 'Efectivo',
            'comprobante_numero' => 'REC-TEST-' . uniqid(),
            'estado' => 'pagado',
        ]);
    }

    public function test_sends_receipt_pdf_to_patient_whatsapp(): void
    {
        TenantWhatsAppAccount::create([
            'tenant_id' => $this->tenant->id,
            'provider' => 'evolution',
            'instance_name' => 'inst-receipt-' . uniqid(),
            'status' => 'connected',
        ]);

        (new SendPaymentReceiptPdf($this->pago->id))->handle();

        Http::assertSent(function ($request) {
            $url = (string) $request->url();

            return str_contains($url, '/message/sendMedia/')
                && $request['number'] === '573001234567'
                && $request['mediatype'] === 'document'
                && $request['mimetype'] === 'application/pdf'
                && !empty($request['media'])
                && str_starts_with($request['fileName'], 'comprobante-REC-TEST-');
        });
    }

    public function test_does_not_send_when_whatsapp_is_not_connected(): void
    {
        // Sin cuenta conectada para el tenant
        (new SendPaymentReceiptPdf($this->pago->id))->handle();

        Http::assertNothingSent();
    }

    public function test_does_not_send_when_patient_has_no_phone(): void
    {
        TenantWhatsAppAccount::create([
            'tenant_id' => $this->tenant->id,
            'provider' => 'evolution',
            'instance_name' => 'inst-receipt-' . uniqid(),
            'status' => 'connected',
        ]);

        $this->paciente->update(['telefono' => null]);

        (new SendPaymentReceiptPdf($this->pago->id))->handle();

        Http::assertNothingSent();
    }
}
