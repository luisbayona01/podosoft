<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notas_credito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('factura_id')->constrained()->cascadeOnDelete();
            $table->string('numero_nota')->unique();
            $table->decimal('valor', 12, 2);
            $table->text('motivo');
            $table->string('uuid_dian')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('notas_credito'); }
};
