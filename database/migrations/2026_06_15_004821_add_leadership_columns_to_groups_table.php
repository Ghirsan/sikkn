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
        Schema::table('groups', function (Blueprint $table) {
            $table->foreignId('lead_dpl_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('student_leader_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['lead_dpl_id']);
            $table->dropForeign(['student_leader_id']);
            $table->dropColumn(['lead_dpl_id', 'student_leader_id']);
        });
    }
};
