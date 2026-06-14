<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
public function up(): void {
Schema::create('conversaciones_ia', function (Blueprint $table) {
$table->id();
$table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
$table->foreignId('paciente_id')->nullable()->constrained()->nullOnDelete();
$table->string('telefono');
        $table->enum('estado',['activa','cerrada','escalada'])->default('activa');
        $table->string('intencion_detectada')->nullable();
        $table->decimal('confianza_modelo', 5, 2)->nullable();
        $table->boolean('requiere_humano')->default(false);
        $table->timestamp('ultima_interaccion')->nullable();
        $table->timestamps();
});
}
public function down(): void { Schema::dropIfExists('conversaciones_ia'); }
};