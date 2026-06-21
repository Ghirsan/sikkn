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
        Schema::table('program_participants', function (Blueprint $table) {
            $table->date('execution_date')->nullable()->after('lpk_revision_note');
            $table->text('problem_potential')->nullable()->after('execution_date');
            $table->text('location')->nullable()->after('problem_potential');
            $table->text('method')->nullable()->after('location');
            $table->text('target_audience')->nullable()->after('method');
            $table->text('output_target')->nullable()->after('target_audience');
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn([
                'execution_date',
                'problem_potential',
                'location',
                'method',
                'target_audience',
                'output_target'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->date('execution_date')->nullable();
            $table->text('problem_potential')->nullable();
            $table->text('location')->nullable();
            $table->text('method')->nullable();
            $table->text('target_audience')->nullable();
            $table->text('output_target')->nullable();
        });

        Schema::table('program_participants', function (Blueprint $table) {
            $table->dropColumn([
                'execution_date',
                'problem_potential',
                'location',
                'method',
                'target_audience',
                'output_target'
            ]);
        });
    }
};
