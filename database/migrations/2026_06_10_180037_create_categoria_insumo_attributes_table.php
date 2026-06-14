<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categoria_insumo_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias_insumos')->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('nombre');
            $table->string('tipo')->default('text'); // text, number, boolean, select
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categoria_insumo_attributes');
    }
};
