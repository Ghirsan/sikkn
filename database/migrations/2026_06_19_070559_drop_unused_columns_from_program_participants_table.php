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
            $table->dropColumn(['timeline', 'actual_result', 'realized_budget']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_participants', function (Blueprint $table) {
            $table->text('timeline')->nullable();
            $table->text('actual_result')->nullable();
            $table->decimal('realized_budget', 12, 2)->default(0);
        });
    }
};
