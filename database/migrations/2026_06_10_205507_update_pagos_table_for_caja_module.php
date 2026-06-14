<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->foreignId('paciente_id')->after('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('servicio_id')->after('cita_id')->nullable()->constrained()->cascadeOnDelete();
            $table->date('fecha_pago')->after('servicio_id')->default(now());
            $table->decimal('descuento', 12, 2)->after('valor')->default(0);
            $table->decimal('monto_final', 12, 2)->after('descuento');
            $table->string('metodo_pago')->change(); // Changing from enum to string for flexibility
            $table->text('observaciones')->nullable()->after('metodo_pago');
            $table->string('comprobante_numero')->after('observaciones')->nullable();
            $table->string('estado')->change(); // Changing from enum to string
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropForeign(['paciente_id']);
            $table->dropColumn(['paciente_id', 'servicio_id', 'fecha_pago', 'descuento', 'monto_final', 'observaciones', 'comprobante_numero']);
            // Note: Reverting enum change is complex, usually not done in down() unless strictly necessary
        });
    }
};
