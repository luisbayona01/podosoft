<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('conversaciones_ia', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('requiere_humano');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversaciones_ia', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });
    }
};
