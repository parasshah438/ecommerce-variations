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
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('email_type')->index(); // welcome, order_confirmation, etc.
            $table->string('recipient_email')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('subject');
            $table->enum('status', ['pending', 'sent', 'failed', 'retry'])->default('pending')->index();
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(3);
            $table->text('error_message')->nullable();
            $table->json('email_data')->nullable(); // Store email template data
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
