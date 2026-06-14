<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('documento')->nullable()->after('name');
            $table->string('telefono')->nullable()->after('documento');
            $table->foreignId('professional_id')->nullable()->after('tenant_id')->constrained('profesionales')->nullOnDelete();
        });

        Schema::table('profesionales', function (Blueprint $table) {
            $table->string('documento')->nullable()->after('nombre');
            $table->string('apellido')->nullable()->change(); // Make apellido nullable if it was required
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['professional_id']);
            $table->dropColumn(['documento', 'telefono', 'professional_id']);
        });

        Schema::table('profesionales', function (Blueprint $table) {
            $table->dropColumn('documento');
        });
    }
};
