<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('program_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->timestamps();
        });

        DB::table('program_types')->insert([
            ['name' => 'Multidisiplin', 'code' => 'multidisiplin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sosial Kemasyarakatan', 'code' => 'sosial_kemasyarakatan', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lainnya', 'code' => 'lainnya', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_types');
    }
};
