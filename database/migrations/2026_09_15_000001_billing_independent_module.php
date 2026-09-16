<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Factura como módulo comercial independiente:
        // paciente y cita pasan a ser relaciones OPCIONALES.
        Schema::table('facturas', function (Blueprint $table) {
            $table->foreignId('paciente_id')->nullable()->after('cita_id')->constrained('pacientes')->nullOnDelete();
            $table->string('cliente_nombre')->nullable()->after('paciente_id');
            $table->string('cliente_documento')->nullable()->after('cliente_nombre');
            $table->decimal('descuento', 12, 2)->default(0)->after('subtotal');
        });

        // Líneas de detalle de la factura: servicios y/o productos (insumos)
        Schema::create('factura_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->nullOnDelete();
            $table->foreignId('insumo_id')->nullable()->constrained('insumos')->nullOnDelete();
            $table->string('descripcion');
            $table->decimal('cantidad', 12, 2)->default(1);
            $table->decimal('precio_unitario', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
        });

        // Precio de venta para poder vender insumos como productos
        Schema::table('insumos', function (Blueprint $table) {
            $table->decimal('precio_venta', 12, 2)->default(0)->after('unidad_medida');
        });

        // Pago pasa a poder referenciar una factura y el paciente/cita se vuelven opcionales.
        // Se cambia el cascadeOnDelete de cita a nullOnDelete para no perder registros financieros.
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropForeign(['paciente_id']);
            $table->dropForeign(['cita_id']);
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->unsignedBigInteger('paciente_id')->nullable()->change();
            $table->unsignedBigInteger('cita_id')->nullable()->change();
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->foreign('paciente_id')->references('id')->on('pacientes')->nullOnDelete();
            $table->foreign('cita_id')->references('id')->on('citas')->nullOnDelete();
            $table->foreignId('factura_id')->nullable()->after('comprobante_numero')->constrained('facturas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropForeign(['factura_id']);
            $table->dropColumn('factura_id');
        });

        Schema::table('insumos', function (Blueprint $table) {
            $table->dropColumn('precio_venta');
        });

        Schema::dropIfExists('factura_items');

        Schema::table('facturas', function (Blueprint $table) {
            $table->dropForeign(['paciente_id']);
            $table->dropColumn(['paciente_id', 'cliente_nombre', 'cliente_documento', 'descuento']);
        });
    }
};
