<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
public function up(): void {
Schema::create('pacientes', function (Blueprint $table) {
$table->id();
$table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
$table->string('tipo_documento',10);
$table->string('documento');
$table->string('nombre');
$table->string('apellido');
$table->string('telefono')->nullable();
$table->string('email')->nullable();
$table->date('fecha_nacimiento')->nullable();
$table->text('direccion')->nullable();
$table->boolean('consentimiento')->default(false);
$table->timestamp('fecha_consentimiento')->nullable();
$table->timestamps();
$table->softDeletes();
$table->unique(['tenant_id','documento']);
});
}
public function down(): void { Schema::dropIfExists('pacientes'); }
};