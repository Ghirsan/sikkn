<?php

namespace Database\Seeders;

use App\Models\GradeWeight;
use Illuminate\Database\Seeder;

class GradeWeightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $weights = [
            [
                'name' => 'Aktivitas Partisipatif',
                'code' => 'aktivitas_partisipatif',
                'weight' => 0.30,
            ],
            [
                'name' => 'Hasil Proyek',
                'code' => 'hasil_proyek',
                'weight' => 0.25,
            ],
            [
                'name' => 'Tugas',
                'code' => 'tugas',
                'weight' => 0.20,
            ],
            [
                'name' => 'Quiz',
                'code' => 'quiz',
                'weight' => 0.05,
            ],
            [
                'name' => 'UTS',
                'code' => 'uts',
                'weight' => 0.10,
            ],
            [
                'name' => 'UAS',
                'code' => 'uas',
                'weight' => 0.10,
            ],
        ];

        foreach ($weights as $data) {
            GradeWeight::updateOrCreate(
                ['code' => $data['code']],
                ['name' => $data['name'], 'weight' => $data['weight']]
            );
        }
    }
}
