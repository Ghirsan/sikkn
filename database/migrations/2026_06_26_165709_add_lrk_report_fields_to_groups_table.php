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
            $table->text('program_multidisiplin_text')->nullable();
            $table->text('program_sosmas_text')->nullable();
            $table->text('program_lainnya_text')->nullable();
            $table->text('storyboard_text')->nullable();
            $table->text('video_script_text')->nullable();
            $table->text('survey_documentation_text')->nullable();
            $table->text('location_map_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn([
                'program_multidisiplin_text',
                'program_sosmas_text',
                'program_lainnya_text',
                'storyboard_text',
                'video_script_text',
                'survey_documentation_text',
                'location_map_text',
            ]);
        });
    }
};
