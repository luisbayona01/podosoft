<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
public function up(): void {
Schema::create('servicios', function (Blueprint $table) {
$table->id();
$table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
        $table->string('nombre');
        $table->text('descripcion')->nullable();
        $table->integer('duracion');
        $table->decimal('precio',12,2);
$table->boolean('activo')->default(true);
$table->timestamps();
$table->softDeletes();
});
}
public function down(): void { Schema::dropIfExists('servicios'); }
};