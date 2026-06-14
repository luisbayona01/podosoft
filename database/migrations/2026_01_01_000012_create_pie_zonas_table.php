<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pie_zonas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('nombre');
            $table->string('codigo_zona')->unique();
            $table->json('coordenadas_svg')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pie_zonas'); }
};
