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
            $table->dropColumn(['theme', 'target', 'budget', 'source_of_fund']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->string('theme')->nullable();
            $table->text('target')->nullable();
            $table->decimal('budget', 12, 2)->default(0);
            $table->string('source_of_fund')->nullable();
        });
    }
};
