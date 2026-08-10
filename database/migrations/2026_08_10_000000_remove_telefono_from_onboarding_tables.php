<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('telefono');
        });

        Schema::table('profesionales', function (Blueprint $table) {
            $table->dropColumn('telefono');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('telefono');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('telefono')->nullable();
        });

        Schema::table('profesionales', function (Blueprint $table) {
            $table->string('telefono')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('telefono')->nullable()->after('documento');
        });
    }
};