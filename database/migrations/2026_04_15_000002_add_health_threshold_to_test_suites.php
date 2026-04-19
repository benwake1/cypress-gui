<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_suites', function (Blueprint $table) {
            $table->float('pass_rate_threshold')->nullable()->after('active');
            $table->timestamp('last_breach_at')->nullable()->after('pass_rate_threshold');
        });
    }

    public function down(): void
    {
        Schema::table('test_suites', function (Blueprint $table) {
            $table->dropColumn(['pass_rate_threshold', 'last_breach_at']);
        });
    }
};
