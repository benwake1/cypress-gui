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
        Schema::table('test_runs', function (Blueprint $table) {
            $table->text('spec_override')->nullable()->after('branch');
            $table->unsignedBigInteger('parent_run_id')->nullable()->after('spec_override');
            $table->foreign('parent_run_id')->references('id')->on('test_runs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('test_runs', function (Blueprint $table) {
            $table->dropForeign(['parent_run_id']);
            $table->dropColumn(['spec_override', 'parent_run_id']);
        });
    }
};
