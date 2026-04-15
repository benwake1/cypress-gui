<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_suites', function (Blueprint $table) {
            $table->string('schedule_cron', 100)->nullable()->after('timeout_minutes');
            $table->boolean('schedule_enabled')->default(false)->after('schedule_cron');
            $table->string('schedule_timezone', 64)->nullable()->after('schedule_enabled');
            $table->timestamp('last_scheduled_at')->nullable()->after('schedule_timezone');
        });
    }

    public function down(): void
    {
        Schema::table('test_suites', function (Blueprint $table) {
            $table->dropColumn(['schedule_cron', 'schedule_enabled', 'schedule_timezone', 'last_scheduled_at']);
        });
    }
};
