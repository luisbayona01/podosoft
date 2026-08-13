<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->string('tipo_documento', 10)->nullable()->change();
            $table->string('documento')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->string('tipo_documento', 10)->nullable(false)->change();
            $table->string('documento')->nullable(false)->change();
        });
    }
};