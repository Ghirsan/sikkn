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
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            
            // LRK Phase
            $table->string('role_in_program')->nullable();
            $table->text('responsibility')->nullable();
            $table->text('timeline')->nullable();
            $table->string('status')->default('draft');
            $table->text('revision_note')->nullable();
            
            // LPK Phase
            $table->text('achievement')->nullable();
            $table->text('obstacle')->nullable();
            $table->text('solution')->nullable();
            $table->text('actual_result')->nullable();
            $table->decimal('realized_budget', 12, 2)->default(0);
            $table->string('lpk_status')->default('draft');
            $table->text('lpk_revision_note')->nullable();

            $table->timestamps();
        });

        // Migrate existing data (safe copy)
        $programs = \Illuminate\Support\Facades\DB::table('programs')->get();
        foreach ($programs as $program) {
            if ($program->student_id) {
                \Illuminate\Support\Facades\DB::table('program_participants')->insert([
                    'program_id' => $program->id,
                    'student_id' => $program->student_id,
                    'role_in_program' => $program->role_in_program ?? null,
                    'responsibility' => $program->responsibility ?? null,
                    'timeline' => $program->timeline ?? null,
                    'status' => $program->status ?? 'draft',
                    'revision_note' => $program->revision_note ?? null,
                    'achievement' => $program->achievement ?? null,
                    'obstacle' => $program->obstacle ?? null,
                    'solution' => $program->solution ?? null,
                    'actual_result' => $program->actual_result ?? null,
                    'realized_budget' => $program->realized_budget ?? 0,
                    'lpk_status' => $program->lpk_status ?? 'draft',
                    'lpk_revision_note' => $program->lpk_revision_note ?? null,
                    'created_at' => $program->created_at,
                    'updated_at' => $program->updated_at,
                ]);
            }
        }

        Schema::table('programs', function (Blueprint $table) {
            // Drop foreign key first before modifying column
            $table->dropForeign(['student_id']);
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id')->nullable()->change();
            $table->foreign('student_id')->references('id')->on('users')->nullOnDelete();
            
            // Drop columns moved to pivot table
            $table->dropColumn([
                'role_in_program',
                'responsibility',
                'timeline',
                'status',
                'revision_note',
                'achievement',
                'obstacle',
                'solution',
                'actual_result',
                'realized_budget',
                'lpk_status',
                'lpk_revision_note',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id')->nullable(false)->change();
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            
            $table->string('role_in_program')->nullable();
            $table->text('responsibility')->nullable();
            $table->text('timeline')->nullable();
            $table->string('status')->default('draft');
            $table->text('revision_note')->nullable();
            
            $table->text('achievement')->nullable();
            $table->text('obstacle')->nullable();
            $table->text('solution')->nullable();
            $table->text('actual_result')->nullable();
            $table->decimal('realized_budget', 12, 2)->default(0);
            $table->string('lpk_status')->default('draft');
            $table->text('lpk_revision_note')->nullable();
        });

        Schema::dropIfExists('program_participants');
    }
};
