<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
public function up(): void {
Schema::create('pagos', function (Blueprint $table) {
$table->id();
$table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
$table->foreignId('cita_id')->constrained()->cascadeOnDelete();
$table->decimal('valor',12,2);
$table->enum('metodo_pago',['efectivo','transferencia','nequi','daviplata','tarjeta']);
$table->enum('estado',['pendiente','pagado','anulado'])->default('pagado');
$table->timestamps();
});
}
public function down(): void { Schema::dropIfExists('pagos'); }
};