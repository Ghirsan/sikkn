<?php

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('type')->default(ProgramType::SosialKemasyarakatan->value);
            $table->text('target')->nullable();
            $table->text('target_audience')->nullable();
            $table->decimal('budget', 12, 2)->default(0);
            $table->string('source_of_fund')->nullable();
            $table->text('method')->nullable();
            $table->text('output_target')->nullable();
            $table->text('timeline')->nullable();
            $table->string('status')->default(ProgramStatus::Draft->value);
            $table->text('revision_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
