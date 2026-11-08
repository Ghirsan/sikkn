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
            $table->json('storyboard_scenes')->nullable()->after('storyboard_text');
            $table->json('video_script_scenes')->nullable()->after('video_script_text');
            $table->json('macro_schedules')->nullable()->after('location_map_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['storyboard_scenes', 'video_script_scenes', 'macro_schedules']);
        });
    }
};
