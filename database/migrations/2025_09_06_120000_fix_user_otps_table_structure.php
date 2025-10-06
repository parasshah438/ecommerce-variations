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
        // Only run this migration if the table exists and actually needs updates
        if (Schema::hasTable('user_otps')) {
            $needsUpdate = false;
            
            // Check if we need to update the table structure
            if (Schema::hasColumn('user_otps', 'is_verified') || 
                Schema::hasColumn('user_otps', 'last_attempt_at') || 
                Schema::hasColumn('user_otps', 'purpose') ||
                !Schema::hasColumn('user_otps', 'verified_at')) {
                $needsUpdate = true;
            }
            
            if ($needsUpdate) {
                // Add missing columns only if they don't exist
                Schema::table('user_otps', function (Blueprint $table) {
                    if (!Schema::hasColumn('user_otps', 'verified_at')) {
                        $table->timestamp('verified_at')->nullable()->after('expires_at');
                    }
                    
                    if (!Schema::hasColumn('user_otps', 'is_used')) {
                        $table->boolean('is_used')->default(false)->after('verified_at');
                    }
                    
                    if (!Schema::hasColumn('user_otps', 'ip_address')) {
                        $table->string('ip_address')->nullable()->after('is_used');
                    }
                    
                    if (!Schema::hasColumn('user_otps', 'user_agent')) {
                        $table->text('user_agent')->nullable()->after('ip_address');
                    }
                });
                
                // Drop old columns in a separate schema call to avoid conflicts
                Schema::table('user_otps', function (Blueprint $table) {
                    // Drop the old columns if they exist
                    if (Schema::hasColumn('user_otps', 'is_verified')) {
                        $table->dropColumn('is_verified');
                    }
                    
                    if (Schema::hasColumn('user_otps', 'last_attempt_at')) {
                        $table->dropColumn('last_attempt_at');
                    }
                    
                    if (Schema::hasColumn('user_otps', 'purpose')) {
                        $table->dropColumn('purpose');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_otps')) {
            Schema::table('user_otps', function (Blueprint $table) {
                // Add back the old columns if they don't exist
                if (!Schema::hasColumn('user_otps', 'is_verified')) {
                    $table->boolean('is_verified')->default(false);
                }
                
                if (!Schema::hasColumn('user_otps', 'last_attempt_at')) {
                    $table->timestamp('last_attempt_at')->nullable();
                }
                
                if (!Schema::hasColumn('user_otps', 'purpose')) {
                    $table->string('purpose')->default('login');
                }
            });
            
            // Drop the new columns in a separate schema call
            Schema::table('user_otps', function (Blueprint $table) {
                $table->dropColumn(['verified_at', 'is_used', 'ip_address', 'user_agent']);
            });
        }
    }
};
