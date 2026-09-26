<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indicaciones/recomendaciones previas a la cita asociadas a cada servicio.
     * Se envían al paciente por WhatsApp al confirmar y en recordatorios.
     */
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->text('indicaciones')->nullable()->after('precio');
        });
    }

    public function down(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->dropColumn('indicaciones');
        });
    }
};
