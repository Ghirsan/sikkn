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
            $table->decimal('aspect_a', 5, 2)->default(0);
            $table->decimal('aspect_b', 5, 2)->default(0);
            $table->decimal('aspect_c', 5, 2)->default(0);
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
