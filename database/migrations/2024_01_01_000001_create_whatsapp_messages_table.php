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
        Schema::create('whats_app_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('phone');
            $table->enum('message_type', ['text', 'image', 'document', 'audio', 'video', 'contact', 'location', 'template']);
            $table->text('content')->nullable();
            $table->string('media_path')->nullable();
            $table->string('media_url')->nullable();
            $table->foreignId('template_id')->nullable()->constrained('whats_app_templates')->onDelete('set null');
            $table->foreignId('contact_id')->nullable()->constrained('whats_app_contacts')->onDelete('set null');
            $table->foreignId('bulk_message_id')->nullable()->constrained('whats_app_bulk_messages')->onDelete('set null');
            $table->string('batch_id')->nullable()->index();
            $table->string('ultramsg_id')->nullable();
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->json('response_data')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'phone']);
            $table->index(['user_id', 'message_type']);
            $table->index(['user_id', 'created_at']);
            $table->index('ultramsg_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whats_app_messages');
    }
};