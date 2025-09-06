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
        Schema::create('user_otps', function (Blueprint $table) {
            $table->id();
            $table->string('identifier'); // email or mobile
            $table->string('identifier_type'); // 'email' or 'mobile'
            $table->string('otp', 6);
            $table->integer('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('last_attempt_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('purpose')->default('login'); // login, registration, password_reset
            $table->timestamps();
            
            $table->index(['identifier', 'identifier_type']);
            $table->index(['otp', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_otps');
    }
};
