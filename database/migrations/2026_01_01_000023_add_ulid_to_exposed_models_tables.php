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
        $tables = [
            'groups',
            'users',
            'programs',
            'program_participants',
            'daily_logs',
            'mentoring_logs',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'ulid')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->ulid('ulid')->nullable()->unique()->after('id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'groups',
            'users',
            'programs',
            'program_participants',
            'daily_logs',
            'mentoring_logs',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'ulid')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropUnique(['ulid']);
                    $t->dropColumn('ulid');
                });
            }
        }
    }
};

