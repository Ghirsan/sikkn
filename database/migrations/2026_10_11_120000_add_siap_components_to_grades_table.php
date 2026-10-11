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
        Schema::table('grades', function (Blueprint $table) {
            $table->decimal('aktivitas_partisipatif', 5, 2)->default(0)->after('ujian_akhir');
            $table->decimal('hasil_proyek', 5, 2)->default(0)->after('aktivitas_partisipatif');
            $table->decimal('tugas', 5, 2)->default(0)->after('hasil_proyek');
            $table->decimal('quiz', 5, 2)->default(0)->after('tugas');
            $table->decimal('uts', 5, 2)->default(0)->after('quiz');
            $table->decimal('uas', 5, 2)->default(0)->after('uts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn([
                'aktivitas_partisipatif',
                'hasil_proyek',
                'tugas',
                'quiz',
                'uts',
                'uas',
            ]);
        });
    }
};
