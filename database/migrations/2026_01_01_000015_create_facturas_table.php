<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cita_id')->nullable()->constrained()->nullOnDelete();
            $table->string('numero_factura')->unique();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('impuestos', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->timestamp('fecha_emision');
            $table->enum('estado', ['borrador', 'emitida', 'pagada', 'anulada'])->default('borrador');
            $table->string('uuid_dian')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('facturas'); }
};
