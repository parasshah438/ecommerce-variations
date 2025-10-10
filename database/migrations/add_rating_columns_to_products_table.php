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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'average_rating')) {
                $table->decimal('average_rating', 3, 2)->nullable()->after('description');
            }
            if (!Schema::hasColumn('products', 'reviews_count')) {
                $table->unsignedInteger('reviews_count')->default(0)->after('average_rating');
            }
        });
        
        // Add some sample ratings to existing products
        if (Schema::hasColumn('products', 'average_rating')) {
            \DB::table('products')->whereNull('average_rating')->update([
                'average_rating' => \DB::raw('ROUND(RAND() * 1.3 + 3.5, 1)'),
                'reviews_count' => \DB::raw('FLOOR(RAND() * 146) + 5')
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'average_rating')) {
                $table->dropColumn('average_rating');
            }
            if (Schema::hasColumn('products', 'reviews_count')) {
                $table->dropColumn('reviews_count');
            }
        });
    }
};