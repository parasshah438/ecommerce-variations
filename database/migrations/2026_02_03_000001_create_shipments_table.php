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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            // Shiprocket identifiers
            $table->string('shiprocket_order_id')->nullable()->index();
            $table->string('shiprocket_shipment_id')->nullable()->index();
            
            // Shipment details
            $table->string('status')->default('created')->index();
            $table->string('carrier')->nullable();
            $table->string('tracking_number')->nullable()->index();
            $table->string('awb_code')->nullable()->index();
            
            // Dates
            $table->datetime('estimated_delivery')->nullable();
            $table->datetime('actual_delivery')->nullable();
            $table->datetime('pickup_scheduled_date')->nullable();
            $table->datetime('shipped_date')->nullable();
            $table->datetime('delivered_date')->nullable();
            
            // JSON data
            $table->json('shiprocket_response')->nullable();
            $table->json('courier_response')->nullable();
            $table->json('tracking_data')->nullable();
            $table->json('return_data')->nullable();
            
            // Additional info
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['order_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};