<?php

namespace Database\Seeders;

use App\Models\Faculty;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(StudyProgramSeeder::class);

        // Demo users for each role
        User::factory()->p2kkn()->create([
            'name' => 'Admin P2KKN',
            'email' => 'admin@sikkn.test',
        ]);

        User::factory()->dpl()->create([
            'name' => 'Dr. Budi Santoso',
            'email' => 'dpl@sikkn.test',
            'study_program_id' => StudyProgram::where('code', '240601')->first()->id, // Informatika
        ]);

        User::factory()->mahasiswa()->create([
            'name' => 'Andi Mahasiswa',
            'email' => 'mahasiswa@sikkn.test',
            'nim' => '24060122100001',
        ]);

        User::factory()->prodi()->create([
            'name' => 'Kaprodi Informatika',
            'email' => 'prodi@sikkn.test',
            'study_program_id' => StudyProgram::where('code', '240601')->first()->id,
        ]);

        User::factory()->fakultas()->create([
            'name' => 'Dekan Fakultas Teknik',
            'email' => 'fakultas@sikkn.test',
            'faculty_id' => Faculty::where('code', '21')->first()->id,
        ]);

        // Seed KKN data (periods, groups, students, programs, logs)
        $this->call(KKNSeeder::class);
        $this->call(GroupLeadershipSeeder::class);
    }
}
