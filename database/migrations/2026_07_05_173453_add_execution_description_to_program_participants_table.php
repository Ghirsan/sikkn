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
            $table->text('execution_description')->nullable()->after('execution_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_participants', function (Blueprint $table) {
            $table->dropColumn('execution_description');
        });
    }
};
