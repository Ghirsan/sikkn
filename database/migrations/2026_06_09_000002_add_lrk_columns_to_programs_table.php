<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->string('theme')->nullable()->after('type');
            $table->tinyInteger('multidisciplinary_number')->unsigned()->nullable()->after('theme');
            $table->text('problem_potential')->nullable()->after('multidisciplinary_number');
            $table->string('role_in_program')->nullable()->after('problem_potential');
            $table->text('responsibility')->nullable()->after('role_in_program');
            $table->text('storyboard')->nullable()->after('output_target');
            $table->text('video_script')->nullable()->after('storyboard');
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn([
                'theme', 'multidisciplinary_number', 'problem_potential',
                'role_in_program', 'responsibility', 'storyboard', 'video_script',
            ]);
        });
    }
};
