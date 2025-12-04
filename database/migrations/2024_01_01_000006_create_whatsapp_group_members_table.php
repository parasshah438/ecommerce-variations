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
        Schema::create('whats_app_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('whats_app_groups')->onDelete('cascade');
            $table->foreignId('contact_id')->constrained('whats_app_contacts')->onDelete('cascade');
            $table->enum('role', ['admin', 'member'])->default('member');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            
            $table->unique(['group_id', 'contact_id']);
            $table->index(['group_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whats_app_group_members');
    }
};