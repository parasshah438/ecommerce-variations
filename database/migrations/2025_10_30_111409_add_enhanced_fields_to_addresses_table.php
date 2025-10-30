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
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('alternate_phone')->nullable()->after('phone');
            $table->enum('type', ['home', 'work', 'other'])->default('home')->after('country');
            $table->boolean('is_default')->default(false)->after('type');
            $table->text('delivery_instructions')->nullable()->after('is_default');
            $table->string('landmark')->nullable()->after('delivery_instructions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn([
                'alternate_phone',
                'type', 
                'is_default',
                'delivery_instructions',
                'landmark'
            ]);
        });
    }
};
