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
            $table->string('active_session_id')->nullable()->after('remember_token');
            $table->timestamp('last_login_at')->nullable()->after('active_session_id');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->text('last_device_info')->nullable()->after('last_login_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'active_session_id',
                'last_login_at', 
                'last_login_ip',
                'last_device_info'
            ]);
        });
    }
};
