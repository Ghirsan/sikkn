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
        Schema::create('sdg_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('code')->unique();
            $table->timestamps();
        });

        $sdgs = [
            1 => '1. No Poverty',
            2 => '2. Zero Hunger',
            3 => '3. Good Health and Well-being',
            4 => '4. Quality Education',
            5 => '5. Gender Equality',
            6 => '6. Clean Water and Sanitation',
            7 => '7. Affordable and Clean Energy',
            8 => '8. Decent Work and Economic Growth',
            9 => '9. Industry, Innovation and Infrastructure',
            10 => '10. Reduced Inequalities',
            11 => '11. Sustainable Cities and Communities',
            12 => '12. Responsible Consumption and Production',
            13 => '13. Climate Action',
            14 => '14. Life Below Water',
            15 => '15. Life on Land',
            16 => '16. Peace, Justice and Strong Institutions',
            17 => '17. Partnerships for the Goals',
        ];

        $now = now();
        $records = [];
        foreach ($sdgs as $code => $name) {
            $records[] = [
                'code' => $code,
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('sdg_categories')->insert($records);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sdg_categories');
    }
};
