<?php

use App\Enums\ProgramStatus;
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
        Schema::create('lpks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->text('achievement')->nullable(); // Pencapaian target
            $table->text('obstacle')->nullable(); // Hambatan
            $table->text('solution')->nullable(); // Solusi
            $table->decimal('realized_budget', 12, 2)->default(0); // Dana terpakai
            $table->string('status')->default(ProgramStatus::Draft->value);
            $table->text('revision_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpks');
    }
};
