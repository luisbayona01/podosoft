<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_calendar_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('google_email')->nullable();
            $table->string('google_account_id')->nullable();
            $table->string('calendar_id')->nullable();
            $table->text('access_token')->nullable();   // encrypted via model cast
            $table->text('refresh_token')->nullable();  // encrypted via model cast
            $table->timestamp('token_expires_at')->nullable();
            $table->text('scopes')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();

            // One active connection per tenant
            $table->unique('tenant_id');
            $table->index('google_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_calendar_connections');
    }
};
