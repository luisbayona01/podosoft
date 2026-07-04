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
        Schema::create('short_links', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16)->unique();
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('original_url');
            $table->timestamp('expires_at');
            $table->unsignedInteger('click_count')->default(0);
            $table->timestamp('last_access_at')->nullable();
            $table->timestamps();

            $table->index('code');
            $table->index(['tenant_id', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('short_links');
    }
};
