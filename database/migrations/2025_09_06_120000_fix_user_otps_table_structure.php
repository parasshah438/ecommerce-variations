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
        Schema::table('user_otps', function (Blueprint $table) {
            // Add missing columns
            $table->timestamp('verified_at')->nullable()->after('expires_at');
            $table->boolean('is_used')->default(false)->after('verified_at');
            $table->string('ip_address')->nullable()->after('is_used');
            $table->text('user_agent')->nullable()->after('ip_address');
            
            // Drop the old column if it exists
            if (Schema::hasColumn('user_otps', 'is_verified')) {
                $table->dropColumn('is_verified');
            }
            
            // Drop unused columns if they exist
            if (Schema::hasColumn('user_otps', 'last_attempt_at')) {
                $table->dropColumn('last_attempt_at');
            }
            
            if (Schema::hasColumn('user_otps', 'purpose')) {
                $table->dropColumn('purpose');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_otps', function (Blueprint $table) {
            // Reverse the changes
            $table->dropColumn(['verified_at', 'is_used', 'ip_address', 'user_agent']);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('last_attempt_at')->nullable();
            $table->string('purpose')->default('login');
        });
    }
};
