<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('kardex', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insumo_id')->constrained()->cascadeOnDelete();
            $table->enum('tipo_movimiento', ['entrada', 'salida', 'ajuste']);
            $table->decimal('cantidad', 12, 2);
            $table->string('referencia')->nullable();
            $table->timestamp('fecha_movimiento');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('kardex'); }
};
