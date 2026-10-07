<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dpl_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('pembekalan', 5, 2)->default(0);
            $table->decimal('gelar_karya', 5, 2)->default(0);
            $table->decimal('kehadiran', 5, 2)->default(0);
            $table->decimal('lrk', 5, 2)->default(0);
            $table->decimal('integritas', 5, 2)->default(0);
            $table->decimal('sosial_kemasyarakatan', 5, 2)->default(0);
            $table->decimal('lpk', 5, 2)->default(0);
            $table->decimal('ujian_akhir', 5, 2)->default(0);
            $table->decimal('final_grade', 5, 2)->default(0);
            $table->char('grade_letter', 2)->nullable();
            $table->timestamps();

            $table->unique('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
