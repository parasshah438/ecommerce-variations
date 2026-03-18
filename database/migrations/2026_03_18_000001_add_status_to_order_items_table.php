<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('status')->default('active')->after('price'); // active | cancelled
            $table->timestamp('cancelled_at')->nullable()->after('status');
            $table->string('cancellation_reason')->nullable()->after('cancelled_at');
            $table->decimal('refund_amount', 10, 2)->nullable()->after('cancellation_reason');
            $table->string('refund_id')->nullable()->after('refund_amount'); // Razorpay refund ID
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['status', 'cancelled_at', 'cancellation_reason', 'refund_amount', 'refund_id']);
        });
    }
};
