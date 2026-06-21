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
        Schema::table('programs', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('group_id');
            $table->date('end_date')->nullable()->after('start_date');
            
            // Rename multidisciplinary_number to sequence (requires doctrine/dbal if not using Laravel 10/11 schema native, but we can do it safely via renameColumn)
            $table->renameColumn('multidisciplinary_number', 'sequence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
            $table->renameColumn('sequence', 'multidisciplinary_number');
        });
    }
};
