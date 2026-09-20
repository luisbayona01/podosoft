<?php

namespace Tests\Feature;

use App\Livewire\InvoiceManager;
use App\Models\CategoriaInsumo;
use App\Models\Cita;
use App\Models\Factura;
use App\Models\Insumo;
use App\Models\Kardex;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\Profesional;
use App\Models\Sede;
use App\Models\Servicio;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceFlowTest extends TestCase
{
    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        putenv('DB_CONNECTION=mysql');
        $_ENV['DB_CONNECTION'] = 'mysql';
        parent::setUp();
        Config::set('database.default', 'mysql');

        Queue::fake();

        $this->tenant = Tenant::create([
            'nombre' => 'Clinica Billing Test',
            'slug' => 'billing-' . uniqid(),
            'email' => uniqid() . '@test.com',
            'telefono' => '111',
            'direccion' => 'Calle 1',
        ]);

        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->actingAs($this->user);
    }

    private function makeServicio(int $precio = 80000): Servicio
    {
        return Servicio::create([
            'tenant_id' => $this->tenant->id,
            'nombre' => 'Consulta ' . uniqid(),
            'duracion' => 30,
            'precio' => $precio,
        ]);
    }

    private function makeInsumo(int $stock = 10, int $precio = 45000): Insumo
    {
        $categoria = CategoriaInsumo::firstOrCreate([
            'tenant_id' => $this->tenant->id,
            'nombre' => 'Productos',
        ]);

        return Insumo::create([
            'tenant_id' => $this->tenant->id,
            'categoria_id' => $categoria->id,
            'nombre' => 'Crema ' . uniqid(),
            'stock_actual' => $stock,
            'unidad_medida' => 'unidad',
            'precio_venta' => $precio,
        ]);
    }

    public function test_factura_totalmente_independiente_sin_paciente_ni_cita(): void
    {
        $servicio = $this->makeServicio(30000);

        Livewire::test(InvoiceManager::class)
            ->set('sin_paciente', true)
            ->set('cliente_nombre', 'Cliente Ocasional')
            ->set('cliente_telefono', '573001112233')
            ->set('registrar_pago', false)
            ->set('nuevoServicioId', $servicio->id)
            ->call('save')
            ->assertRedirect(route('invoices.index'));

        $factura = Factura::latest('id')->first();

        // El cliente ocasional queda registrado como paciente con su teléfono
        $this->assertNotNull($factura->paciente_id);
        $paciente = \App\Models\Paciente::find($factura->paciente_id);
        $this->assertEquals('573001112233', $paciente->telefono);
        $this->assertEquals('Cliente', $paciente->nombre);
        $this->assertEquals('Ocasional', $paciente->apellido);
        $this->assertNull($factura->cita_id);
        $this->assertEquals('Cliente Ocasional', $factura->cliente_nombre);
        $this->assertEquals(30000, $factura->total);
        $this->assertEquals('borrador', $factura->estado);
        $this->assertCount(1, $factura->items);
    }

    public function test_requiere_nombre_cuando_es_cliente_ocasional(): void
    {
        $servicio = $this->makeServicio();

        Livewire::test(InvoiceManager::class)
            ->set('sin_paciente', true)
            ->set('registrar_pago', false)
            ->set('nuevoServicioId', $servicio->id)
            ->call('save')
            ->assertHasErrors(['cliente_nombre']);
    }

    public function test_requiere_al_menos_un_item(): void
    {
        Livewire::test(InvoiceManager::class)
            ->set('sin_paciente', true)
            ->set('cliente_nombre', 'X')
            ->set('cliente_telefono', '573001112233')
            ->set('registrar_pago', false)
            ->call('save')
            ->assertHasErrors(['items']);
    }

    public function test_venta_de_producto_descuenta_stock_y_registra_kardex(): void
    {
        $insumo = $this->makeInsumo(5, 45000);

        Livewire::test(InvoiceManager::class)
            ->set('sin_paciente', true)
            ->set('cliente_nombre', 'Comprador')
            ->set('cliente_telefono', '573001112233')
            ->set('nuevoInsumoId', $insumo->id)
            ->set('items.0.cantidad', 2)
            ->call('save');

        $this->assertEquals(3, $insumo->fresh()->stock_actual);

        $this->assertDatabaseHas('kardex', [
            'insumo_id' => $insumo->id,
            'tipo_movimiento' => 'salida',
            'cantidad' => 2,
        ]);

        $pago = Pago::latest('id')->first();
        $this->assertEquals(90000, $pago->monto_final);
        $this->assertNotNull($pago->factura_id);
        $this->assertEquals('pagada', $pago->factura->estado);
    }

    public function test_bloquea_venta_si_no_hay_stock(): void
    {
        $insumo = $this->makeInsumo(1, 45000);
        $facturasAntes = Factura::count();

        Livewire::test(InvoiceManager::class)
            ->set('sin_paciente', true)
            ->set('cliente_nombre', 'Comprador')
            ->set('cliente_telefono', '573001112233')
            ->set('nuevoInsumoId', $insumo->id)
            ->set('items.0.cantidad', 5)
            ->call('save');

        $this->assertEquals(1, $insumo->fresh()->stock_actual);
        $this->assertEquals($facturasAntes, Factura::count());
    }

    public function test_servicio_mas_producto_en_una_sola_factura(): void
    {
        $servicio = $this->makeServicio(80000);
        $insumo = $this->makeInsumo(10, 45000);

        Livewire::test(InvoiceManager::class)
            ->set('sin_paciente', true)
            ->set('cliente_nombre', 'Cliente Mixto')
            ->set('cliente_telefono', '573001112233')
            ->set('nuevoServicioId', $servicio->id)
            ->set('nuevoInsumoId', $insumo->id)
            ->call('save');

        $factura = Factura::latest('id')->first();
        $this->assertCount(2, $factura->items);
        $this->assertEquals(125000, $factura->total);
        $this->assertEquals(9, $insumo->fresh()->stock_actual);
    }

    public function test_factura_desde_cita_precarga_paciente_y_marca_cita_pagada(): void
    {
        $paciente = Paciente::create([
            'tenant_id' => $this->tenant->id,
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'telefono' => '3000000000',
            'fecha_nacimiento' => '1990-01-01',
        ]);

        $profesional = Profesional::create([
            'tenant_id' => $this->tenant->id,
            'nombre' => 'Doc',
            'apellido' => 'Test',
            'numero_licencia' => 'LIC-' . uniqid(),
            'email' => uniqid() . '@test.com',
        ]);

        $sede = Sede::create([
            'tenant_id' => $this->tenant->id,
            'nombre' => 'Sede T',
            'direccion' => 'Cra 1',
        ]);

        $cita = Cita::create([
            'tenant_id' => $this->tenant->id,
            'paciente_id' => $paciente->id,
            'profesional_id' => $profesional->id,
            'sede_id' => $sede->id,
            'fecha_hora' => now(),
            'estado' => 'confirmada',
        ]);

        $servicio = $this->makeServicio(80000);
        $cita->servicios()->attach($servicio->id, ['precio_aplicado' => 80000]);

        Livewire::test(InvoiceManager::class, ['citaId' => $cita->id])
            ->call('save');

        $factura = Factura::latest('id')->first();
        $this->assertEquals($paciente->id, $factura->paciente_id);
        $this->assertEquals($cita->id, $factura->cita_id);
        $this->assertEquals(80000, $factura->total);
        $this->assertEquals('pagada', $cita->fresh()->estado);
    }

    public function test_anular_factura_revierte_stock(): void
    {
        $insumo = $this->makeInsumo(5, 45000);

        Livewire::test(InvoiceManager::class)
            ->set('sin_paciente', true)
            ->set('cliente_nombre', 'Comprador')
            ->set('cliente_telefono', '573001112233')
            ->set('nuevoInsumoId', $insumo->id)
            ->call('save');

        $factura = Factura::latest('id')->first();
        $this->assertEquals(4, $insumo->fresh()->stock_actual);

        Livewire::test(\App\Livewire\InvoiceIndex::class)
            ->call('anular', $factura->id);

        $this->assertEquals('anulada', $factura->fresh()->estado);
        $this->assertEquals(5, $insumo->fresh()->stock_actual);
        $this->assertEquals('anulado', $factura->pagos()->first()->fresh()->estado);
        $this->assertEquals(1, Kardex::where('insumo_id', $insumo->id)->where('tipo_movimiento', 'entrada')->count());
    }
}
