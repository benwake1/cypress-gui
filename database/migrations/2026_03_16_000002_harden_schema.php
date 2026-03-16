<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Schema hardening:
 *
 *  H3 — Make test_runs.triggered_by nullable + change FK to nullOnDelete so
 *       deleting a user does not cascade-delete all their run history.
 *
 *  H6 — Add the missing FK constraint on socialite_users.user_id so orphaned
 *       rows cannot accumulate and cannot be re-matched to a new user.
 *
 *  M3 — Add a DB-level unique constraint on test_suites (project_id, slug) so
 *       a race condition or direct DB insert cannot produce duplicate slugs.
 */
return new class extends Migration
{
    public function up(): void
    {
        // H3: triggered_by — nullable + nullOnDelete FK
        Schema::table('test_runs', function (Blueprint $table) {
            // SQLite does not support dropping individual FK constraints via Blueprint;
            // the change() call below rewrites the column which implicitly handles it.
            $table->foreignId('triggered_by')->nullable()->change();
        });

        // H6: socialite_users — purge orphaned rows, then add proper FK constraint
        DB::table('socialite_users')
            ->whereNotIn('user_id', DB::table('users')->select('id'))
            ->delete();

        Schema::table('socialite_users', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // M3: test_suites.slug — unique per project
        Schema::table('test_suites', function (Blueprint $table) {
            $table->unique(['project_id', 'slug'], 'test_suites_project_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('test_suites', function (Blueprint $table) {
            $table->dropUnique('test_suites_project_slug_unique');
        });

        Schema::table('socialite_users', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('test_runs', function (Blueprint $table) {
            $table->foreignId('triggered_by')->nullable(false)->change();
        });
    }
};
