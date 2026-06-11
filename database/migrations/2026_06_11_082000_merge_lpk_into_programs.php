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
        Schema::dropIfExists('lpks');

        Schema::table('programs', function (Blueprint $table) {
            $table->text('achievement')->nullable()->after('revision_note');
            $table->text('obstacle')->nullable()->after('achievement');
            $table->text('solution')->nullable()->after('obstacle');
            $table->text('actual_result')->nullable()->after('solution');
            $table->decimal('realized_budget', 12, 2)->default(0)->after('actual_result');
            $table->string('lpk_status')->default('draft')->after('realized_budget');
            $table->text('lpk_revision_note')->nullable()->after('lpk_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn([
                'achievement',
                'obstacle',
                'solution',
                'actual_result',
                'realized_budget',
                'lpk_status',
                'lpk_revision_note',
            ]);
        });

        Schema::create('lpks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->text('achievement')->nullable();
            $table->text('obstacle')->nullable();
            $table->text('solution')->nullable();
            $table->text('actual_result')->nullable();
            $table->decimal('realized_budget', 12, 2)->default(0);
            $table->string('status')->default('draft');
            $table->text('revision_note')->nullable();
            $table->timestamps();
        });
    }
};
