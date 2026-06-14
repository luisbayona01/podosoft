<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
public function up(): void {
Schema::create('mensajes_ia', function (Blueprint $table) {
$table->id();
$table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
$table->foreignId('conversacion_id')->constrained('conversaciones_ia')->cascadeOnDelete();
        $table->enum('origen',['paciente','ia','humano']);
        $table->longText('mensaje');
        $table->json('metadata')->nullable();
        $table->string('token_ia')->nullable();
        $table->timestamp('procesado_en')->nullable();
        $table->timestamps();
});
}
public function down(): void { Schema::dropIfExists('mensajes_ia'); }
};