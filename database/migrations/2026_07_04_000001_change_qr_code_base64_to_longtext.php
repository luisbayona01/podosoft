<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_whatsapp_accounts', function (Blueprint $table) {
            $table->longText('qr_code_base64')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tenant_whatsapp_accounts', function (Blueprint $table) {
            $table->string('qr_code_base64', 255)->nullable()->change();
        });
    }
};