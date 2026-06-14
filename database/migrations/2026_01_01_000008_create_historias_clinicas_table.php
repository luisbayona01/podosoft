<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
public function up(): void {
Schema::create('historias_clinicas', function (Blueprint $table) {
$table->id();
$table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
$table->foreignId('paciente_id')->constrained()->cascadeOnDelete();
$table->foreignId('cita_id')->nullable()->constrained()->nullOnDelete();
        $table->text('diagnostico');
        $table->text('procedimiento');
        $table->longText('observaciones')->nullable();
        $table->string('firma_digital')->nullable();
        $table->timestamp('fecha_firma')->nullable();
        $table->boolean('bloqueo_edicion')->default(false);
        $table->timestamps();
});
}
public function down(): void { Schema::dropIfExists('historias_clinicas'); }
};