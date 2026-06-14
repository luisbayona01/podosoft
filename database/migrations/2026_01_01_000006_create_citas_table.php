<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
public function up(): void {
Schema::create('citas', function (Blueprint $table) {
$table->id();
        $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->foreignId('paciente_id')->constrained()->cascadeOnDelete();
        $table->foreignId('profesional_id')->constrained('profesionales')->cascadeOnDelete();
        $table->foreignId('sede_id')->constrained('sedes')->cascadeOnDelete();
        $table->timestamp('fecha_hora');
$table->enum('estado',['pendiente','confirmada','cancelada','atendida','no_asistio'])->default('pendiente');
$table->enum('origen',['manual','web','whatsapp','ia'])->default('manual');
$table->timestamps();
});
}
public function down(): void { Schema::dropIfExists('citas'); }
};