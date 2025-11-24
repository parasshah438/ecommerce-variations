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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('country_code', 5)->nullable()->after('email');
            $table->string('mobile_number', 15)->nullable()->after('country_code');
            $table->string('avatar')->nullable()->after('mobile_number');
            $table->enum('role', ['admin', 'manager', 'user'])->default('user')->after('avatar');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('role');
            $table->date('date_of_birth')->nullable()->after('status');
            $table->text('address')->nullable()->after('date_of_birth');
            $table->string('city')->nullable()->after('address');
            $table->string('country')->nullable()->after('city');
            $table->text('bio')->nullable()->after('country');
            $table->rememberToken();
            $table->softDeletes()->after('updated_at');
            $table->timestamps();
        });


        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
