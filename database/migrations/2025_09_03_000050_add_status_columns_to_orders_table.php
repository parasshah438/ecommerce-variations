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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status')->default('pending')->after('status');
            $table->text('notes')->nullable()->after('payment_method');
            $table->timestamp('cancelled_at')->nullable()->after('notes');
            $table->timestamp('returned_at')->nullable()->after('cancelled_at');
            $table->timestamp('refunded_at')->nullable()->after('returned_at');
            $table->string('cancellation_reason')->nullable()->after('refunded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status',
                'notes',
                'cancelled_at',
                'returned_at', 
                'refunded_at',
                'cancellation_reason'
            ]);
        });
    }
};
