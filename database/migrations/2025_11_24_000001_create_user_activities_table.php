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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->text('log_description');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('action')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('action');
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};