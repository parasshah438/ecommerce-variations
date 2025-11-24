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
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->string('location')->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('set null');
            $table->foreignId('state_id')->nullable()->constrained('states')->onDelete('set null');
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->enum('type', ['public', 'private', 'government'])->default('public');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->year('established_year')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['status', 'type']);
            $table->index(['country_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('universities');
    }
};