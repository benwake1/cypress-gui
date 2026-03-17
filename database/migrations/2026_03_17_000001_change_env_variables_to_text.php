<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->text('env_variables')->nullable()->change();
        });

        Schema::table('test_suites', function (Blueprint $table) {
            $table->text('env_variables')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->json('env_variables')->nullable()->change();
        });

        Schema::table('test_suites', function (Blueprint $table) {
            $table->json('env_variables')->nullable()->change();
        });
    }
};
