<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Demo users for each role
        User::factory()->p2kkn()->create([
            'name' => 'Admin P2KKN',
            'email' => 'admin@sikkn.test',
        ]);

        User::factory()->dpl()->create([
            'name' => 'Dr. Budi Santoso',
            'email' => 'dpl@sikkn.test',
        ]);

        User::factory()->mahasiswa()->create([
            'name' => 'Andi Mahasiswa',
            'email' => 'mahasiswa@sikkn.test',
        ]);

        User::factory()->prodi()->create([
            'name' => 'Kaprodi Informatika',
            'email' => 'prodi@sikkn.test',
        ]);

        User::factory()->fakultas()->create([
            'name' => 'Dekan Fakultas Teknik',
            'email' => 'fakultas@sikkn.test',
        ]);

        // Seed KKN data (periods, groups, students, programs, logs)
        $this->call(KKNSeeder::class);
    }
}
