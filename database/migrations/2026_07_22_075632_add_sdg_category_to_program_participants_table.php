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
            $table->unsignedTinyInteger('sdg_category')->nullable()->after('output_target');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_participants', function (Blueprint $table) {
            $table->dropColumn('sdg_category');
        });
    }
};
