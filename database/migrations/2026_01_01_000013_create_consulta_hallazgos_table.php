<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('consulta_hallazgos', function (Blueprint $table) {
            $table->id();
        $table->foreignId('historia_clinica_id')->constrained('historias_clinicas')->cascadeOnDelete();
        $table->foreignId('pie_zona_id')->constrained('pie_zonas')->cascadeOnDelete();
        $table->text('observaciones');
            $table->enum('severidad', ['leve', 'moderada', 'grave'])->default('moderada');
            $table->json('metadatos_graficos')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('consulta_hallazgos'); }
};
