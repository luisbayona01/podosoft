<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bloqueos_agenda', function (Blueprint $table) {
            $table->id();
        $table->foreignId('profesional_id')->constrained('profesionales')->cascadeOnDelete();
        $table->foreignId('sede_id')->nullable()->constrained('sedes')->cascadeOnDelete();
        $table->timestamp('fecha_inicio');
            $table->timestamp('fecha_fin');
            $table->string('motivo')->nullable();
            $table->enum('tipo', ['vacaciones', 'ausencia', 'bloqueo_manual'])->default('bloqueo_manual');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('bloqueos_agenda'); }
};
