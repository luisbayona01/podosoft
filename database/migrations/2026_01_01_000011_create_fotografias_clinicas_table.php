<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fotografias_clinicas', function (Blueprint $table) {
            $table->id();
        $table->foreignId('historia_clinica_id')->constrained('historias_clinicas')->cascadeOnDelete();
        $table->string('ruta');
            $table->string('nombre_archivo');
            $table->string('tipo_mime');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('fotografias_clinicas'); }
};
