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
        Schema::create('participant_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_participant_id')->constrained('program_participants')->cascadeOnDelete();
            $table->string('output_code')->nullable();
            $table->string('name');
            $table->string('type');
            $table->string('url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participant_outputs');
    }
};
