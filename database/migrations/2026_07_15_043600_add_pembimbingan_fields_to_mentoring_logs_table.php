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
        Schema::table('mentoring_logs', function (Blueprint $table) {
            $table->foreignId('program_id')->nullable()->after('student_id')->constrained('programs')->nullOnDelete();
            $table->string('target_group')->nullable()->after('dpl_feedback');
            $table->unsignedInteger('student_count')->nullable()->after('target_group');
            $table->string('output')->nullable()->after('student_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mentoring_logs', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn(['program_id', 'target_group', 'student_count', 'output']);
        });
    }
};
