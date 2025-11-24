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
            // Add foreign key columns for locations
            $table->foreignId('country_id')->nullable()->after('country')->constrained('countries')->onDelete('set null');
            $table->foreignId('state_id')->nullable()->after('country_id')->constrained('states')->onDelete('set null');
            $table->foreignId('city_id')->nullable()->after('state_id')->constrained('cities')->onDelete('set null');
            
            // Add beneficiary location columns
            $table->foreignId('beneficiary_country_id')->nullable()->after('city_id')->constrained('countries')->onDelete('set null');
            $table->foreignId('beneficiary_state_id')->nullable()->after('beneficiary_country_id')->constrained('states')->onDelete('set null');
            $table->foreignId('beneficiary_city_id')->nullable()->after('beneficiary_state_id')->constrained('cities')->onDelete('set null');
            
            // Add institute column as foreign key
            $table->foreignId('institute')->nullable()->after('beneficiary_city_id')->constrained('universities')->onDelete('set null');
            
            // Add indexes for better performance
            $table->index(['status', 'role']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['state_id']);
            $table->dropForeign(['city_id']);
            $table->dropForeign(['beneficiary_country_id']);
            $table->dropForeign(['beneficiary_state_id']);
            $table->dropForeign(['beneficiary_city_id']);
            $table->dropForeign(['institute']);
            
            $table->dropIndex(['status', 'role']);
            $table->dropIndex(['created_at']);
            
            $table->dropColumn([
                'country_id',
                'state_id', 
                'city_id',
                'beneficiary_country_id',
                'beneficiary_state_id',
                'beneficiary_city_id',
                'institute'
            ]);
        });
    }
};