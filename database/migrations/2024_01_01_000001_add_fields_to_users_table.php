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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->enum('role', ['admin', 'manager', 'user'])->default('user')->after('avatar');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('role');
            $table->date('date_of_birth')->nullable()->after('status');
            $table->text('address')->nullable()->after('date_of_birth');
            $table->string('city')->nullable()->after('address');
            $table->string('country')->nullable()->after('city');
            $table->text('bio')->nullable()->after('country');
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'avatar', 'role', 'status', 'date_of_birth',
                'address', 'city', 'country', 'bio'
            ]);
            $table->dropSoftDeletes();
        });
    }
};