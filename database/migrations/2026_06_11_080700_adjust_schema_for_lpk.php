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
        // 1. Add lpk_background to groups
        Schema::table('groups', function (Blueprint $table) {
            $table->text('lpk_background')->nullable()->after('background');
        });

        // 2. Add actual_result to lpks
        Schema::table('lpks', function (Blueprint $table) {
            $table->text('actual_result')->nullable()->after('solution');
        });

        // 3. Update schedule_events
        Schema::table('schedule_events', function (Blueprint $table) {
            $table->foreignId('program_id')->nullable()->after('group_id')->constrained('programs')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->after('program_id')->constrained('users')->cascadeOnDelete();
            $table->string('event_code', 20)->nullable()->after('student_id');
            $table->boolean('is_realized')->default(false)->after('description');
        });

        // 4. Create program_documentations
        Schema::create('program_documentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->string('image_path');
            $table->text('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 5. Create program_outputs
        Schema::create('program_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('output_code', 20); // e.g. LM1A1
            $table->string('output_type'); // medsos, berita, poster, video, dll
            $table->string('title')->nullable();
            $table->string('file_path')->nullable();
            $table->string('url', 500)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_outputs');
        Schema::dropIfExists('program_documentations');

        Schema::table('schedule_events', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropForeign(['student_id']);
            $table->dropColumn(['program_id', 'student_id', 'event_code', 'is_realized']);
        });

        Schema::table('lpks', function (Blueprint $table) {
            $table->dropColumn('actual_result');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('lpk_background');
        });
    }
};
