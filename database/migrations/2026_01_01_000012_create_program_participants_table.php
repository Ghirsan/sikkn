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
        Schema::create('program_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('participant_code')->nullable()->unique();
            $table->string('participant_title')->nullable();
            $table->foreignId('sdg_category_id')->nullable()->constrained('sdg_categories')->nullOnDelete();
            $table->string('role_in_program')->nullable();
            $table->text('responsibility')->nullable();
            $table->string('status')->default('draft')->index();
            $table->text('revision_note')->nullable();
            $table->text('achievement')->nullable();
            $table->text('obstacle')->nullable();
            $table->text('solution')->nullable();
            $table->string('lpk_status')->default('draft')->index();
            $table->date('execution_date')->nullable();
            $table->text('execution_description')->nullable();
            $table->text('problem_potential')->nullable();
            $table->text('location')->nullable();
            $table->text('method')->nullable();
            $table->text('target_audience')->nullable();
            $table->text('output_target')->nullable();
            $table->string('documentation_image_path')->nullable();
            $table->string('documentation_caption')->nullable();
            $table->timestamps();

            $table->unique(['program_id', 'student_id'], 'program_student_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_participants');
    }
};
