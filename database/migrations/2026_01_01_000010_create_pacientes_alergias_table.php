<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pacientes_alergias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained()->cascadeOnDelete();
            $table->string('descripcion');
            $table->enum('severidad', ['leve', 'moderada', 'grave'])->default('moderada');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pacientes_alergias'); }
};
