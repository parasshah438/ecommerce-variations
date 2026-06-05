<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('status', 20)->default('approved')->after('is_approved');
            $table->text('admin_notes')->nullable()->after('status');
            $table->timestamp('moderated_at')->nullable()->after('admin_notes');
            $table->foreignId('moderated_by')->nullable()->after('moderated_at')
                ->constrained('users')->nullOnDelete();

            $table->index('status');
        });

        DB::table('reviews')->where('is_approved', true)->update(['status' => 'approved']);
        DB::table('reviews')->where('is_approved', false)->update(['status' => 'pending']);
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['moderated_by']);
            $table->dropColumn(['status', 'admin_notes', 'moderated_at', 'moderated_by']);
        });
    }
};
