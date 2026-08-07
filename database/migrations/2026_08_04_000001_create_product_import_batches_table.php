<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_uuid')->nullable()->index();
            $table->string('original_filename')->nullable();
            $table->string('file_path')->nullable();
            $table->string('status')->default('uploaded'); // uploaded, validating, validated, importing, completed, failed
            $table->integer('total_rows')->default(0);
            $table->integer('valid_rows')->default(0);
            $table->integer('invalid_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->json('validation_errors')->nullable();
            $table->json('import_errors')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_import_batches');
    }
};
