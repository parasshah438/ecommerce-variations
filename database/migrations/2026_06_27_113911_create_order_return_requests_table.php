<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_return_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('user_id');
            $table->string('status')->default('pending'); // pending, approved, rejected, pickup_scheduled, picked_up, refunded
            $table->text('customer_reason')->nullable();
            $table->json('return_items')->nullable(); // array of item_ids being returned
            $table->decimal('refund_amount', 12, 2)->nullable();
            $table->text('admin_note')->nullable(); // approve/reject reason
            $table->unsignedBigInteger('reviewed_by')->nullable(); // admin user id
            $table->timestamp('reviewed_at')->nullable();

            // Shiprocket return pickup fields
            $table->string('shiprocket_return_order_id')->nullable();
            $table->string('shiprocket_shipment_id')->nullable();
            $table->string('pickup_awb')->nullable();
            $table->string('pickup_courier')->nullable();
            $table->timestamp('pickup_scheduled_date')->nullable();
            $table->timestamp('picked_up_at')->nullable();

            // Refund
            $table->string('refund_id')->nullable(); // Razorpay refund ID
            $table->string('refund_method')->default('original'); // original, store_credit, bank_transfer
            $table->timestamp('refunded_at')->nullable();

            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_return_requests');
    }
};
