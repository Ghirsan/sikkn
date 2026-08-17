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
            $table->dropColumn([
                'output_type',
                'output_title',
                'output_file_path',
                'output_url',
                'output_description',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_participants', function (Blueprint $table) {
            $table->string('output_type')->nullable()->after('documentation_caption');
            $table->string('output_title')->nullable()->after('output_type');
            $table->string('output_file_path')->nullable()->after('output_title');
            $table->string('output_url')->nullable()->after('output_file_path');
            $table->text('output_description')->nullable()->after('output_url');
        });
    }
};
