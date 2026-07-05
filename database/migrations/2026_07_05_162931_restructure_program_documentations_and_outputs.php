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
        // 1. Drop old tables
        Schema::dropIfExists('program_documentations');
        Schema::dropIfExists('program_outputs');

        // 2. Add columns to program_participants
        Schema::table('program_participants', function (Blueprint $table) {
            $table->string('documentation_image_path')->nullable()->after('output_target');
            $table->string('documentation_caption')->nullable()->after('documentation_image_path');
            
            $table->string('output_type')->nullable()->after('documentation_caption');
            $table->string('output_title')->nullable()->after('output_type');
            $table->string('output_file_path')->nullable()->after('output_title');
            $table->string('output_url')->nullable()->after('output_file_path');
            $table->text('output_description')->nullable()->after('output_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_participants', function (Blueprint $table) {
            $table->dropColumn([
                'documentation_image_path',
                'documentation_caption',
                'output_type',
                'output_title',
                'output_file_path',
                'output_url',
                'output_description',
            ]);
        });

        Schema::create('program_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('output_code')->nullable();
            $table->string('output_type')->nullable();
            $table->string('title');
            $table->string('file_path')->nullable();
            $table->string('url')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('program_documentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }
};
