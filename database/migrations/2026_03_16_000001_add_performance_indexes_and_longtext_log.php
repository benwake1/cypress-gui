<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Production-hardening migration:
 *
 *  1. Indexes on test_results (status, spec_file, full_title) — used by FlakyTests,
 *     TestHistory, and CompareRuns queries that filter/group on these columns.
 *  2. Index on test_runs.status — used by dashboard listing and run-status filtering.
 *  3. Change test_runs.log_output from text → longText — Cypress runs can produce
 *     output well in excess of 64 KB (the MySQL TEXT limit).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_results', function (Blueprint $table) {
            $table->index('status',     'test_results_status_index');
            $table->index('spec_file',  'test_results_spec_file_index');
            $table->index('full_title', 'test_results_full_title_index');
        });

        Schema::table('test_runs', function (Blueprint $table) {
            $table->index('status', 'test_runs_status_index');

            // Expand log storage — longText supports up to 4 GB vs TEXT's 64 KB.
            $table->longText('log_output')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('test_results', function (Blueprint $table) {
            $table->dropIndex('test_results_status_index');
            $table->dropIndex('test_results_spec_file_index');
            $table->dropIndex('test_results_full_title_index');
        });

        Schema::table('test_runs', function (Blueprint $table) {
            $table->dropIndex('test_runs_status_index');
            $table->text('log_output')->nullable()->change();
        });
    }
};
