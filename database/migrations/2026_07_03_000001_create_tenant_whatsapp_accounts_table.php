<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_whatsapp_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('evolution');
            $table->string('instance_name')->unique();
            $table->string('instance_id')->nullable();
            $table->string('phone')->nullable();
            $table->enum('status', ['pending', 'connecting', 'connected', 'disconnected', 'error'])->default('pending');
            $table->string('api_key')->nullable();
            $table->string('server_url')->nullable();
            $table->text('session')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->string('webhook_url')->nullable();
            $table->text('qr_code')->nullable();
            $table->string('qr_code_base64')->nullable();
            $table->boolean('webhook_configured')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['instance_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_whatsapp_accounts');
    }
};