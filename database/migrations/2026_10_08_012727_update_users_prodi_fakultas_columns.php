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
            $table->foreignId('faculty_id')->nullable()->after('nip')->constrained('faculties')->nullOnDelete();
            $table->foreignId('study_program_id')->nullable()->after('faculty_id')->constrained('study_programs')->nullOnDelete();
            $table->dropColumn(['prodi', 'fakultas']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('prodi')->nullable()->after('nip');
            $table->string('fakultas')->nullable()->after('prodi');
            $table->dropConstrainedForeignId('study_program_id');
            $table->dropConstrainedForeignId('faculty_id');
        });
    }
};
