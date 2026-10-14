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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('periods')->cascadeOnDelete();
            $table->string('name');
            $table->foreignId('group_type_id')->nullable()->constrained('group_types')->nullOnDelete();
            $table->string('village')->nullable();
            $table->string('district')->nullable();
            $table->string('regency')->nullable();
            $table->string('province')->nullable();
            $table->string('partner_name')->nullable();
            $table->string('village_head')->nullable();
            $table->text('background')->nullable();
            $table->boolean('is_lrk_locked')->default(false);
            $table->boolean('is_lpk_locked')->default(false);
            $table->string('location_map_path')->nullable();
            $table->string('survey_document_path')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedBigInteger('lead_dpl_id')->nullable();
            $table->unsignedBigInteger('student_leader_id')->nullable();
            $table->text('program_multidisiplin_text')->nullable();
            $table->text('program_sosmas_text')->nullable();
            $table->text('program_lainnya_text')->nullable();
            $table->text('storyboard_text')->nullable();
            $table->text('video_script_text')->nullable();
            $table->text('survey_documentation_text')->nullable();
            $table->text('location_map_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
