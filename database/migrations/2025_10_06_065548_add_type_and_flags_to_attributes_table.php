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
        Schema::table('attributes', function (Blueprint $table) {
            $table->enum('type', ['text', 'color', 'size', 'number'])->default('text')->after('name');
            $table->boolean('is_required')->default(false)->after('type');
            $table->boolean('is_filterable')->default(true)->after('is_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn(['type', 'is_required', 'is_filterable']);
        });
    }
};
