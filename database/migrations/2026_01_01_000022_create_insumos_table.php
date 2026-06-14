<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias_insumos')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('codigo_sku')->nullable();
            $table->decimal('stock_actual', 12, 2)->default(0);
            $table->decimal('stock_minimo', 12, 2)->default(0);
            $table->string('unidad_medida')->default('unidad');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('insumos'); }
};
