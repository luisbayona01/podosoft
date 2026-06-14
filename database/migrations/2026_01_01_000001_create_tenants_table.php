<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
public function up(): void {
Schema::create('tenants', function (Blueprint $table) {
$table->id();
$table->string('nombre');
$table->string('slug')->unique();
$table->string('nit')->nullable();
$table->string('telefono')->nullable();
$table->string('email')->nullable();
$table->string('direccion')->nullable();
        $table->string('logo')->nullable();
        $table->string('plan_id')->nullable();
        $table->enum('estado_suscripcion', ['activo', 'vencido', 'suspendido'])->default('activo');
        $table->date('fecha_vencimiento')->nullable();
        $table->boolean('activo')->default(true);
        $table->timestamps();
});
}
public function down(): void { Schema::dropIfExists('tenants'); }
};