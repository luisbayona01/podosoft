<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bot_blocked_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->unique()->comment('Número normalizado: solo dígitos, con código de país');
            $table->string('reason')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_blocked_contacts');
    }
};
