<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('recetas_insumos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insumo_id')->constrained()->cascadeOnDelete();
            $table->decimal('cantidad_consumo', 12, 2);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('recetas_insumos'); }
};
