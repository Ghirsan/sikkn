<?php

namespace Tests\Feature\Observer;

use App\Enums\Semester;
use App\Livewire\Observer\FakultasDashboard;
use App\Livewire\Observer\ProdiDashboard;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\Period;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_prodi_observer_sees_scoped_metrics(): void
    {
        $faculty = Faculty::create([
            'code' => 'FT',
            'name' => 'Fakultas Teknik',
            'short_name' => 'Teknik',
        ]);

        $prodi = StudyProgram::create([
            'code' => 'IF101',
            'name' => 'Informatika',
            'faculty_id' => $faculty->id,
        ]);

        $period = Period::create([
            'name' => 'KKN 2026',
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(25)->toDateString(),
        ]);

        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 03',
        ]);

        $prodiUser = User::factory()->prodi()->create([
            'study_program_id' => $prodi->id,
            'faculty_id' => $faculty->id,
        ]);

        // Student in this prodi
        User::factory()->mahasiswa()->count(4)->create([
            'study_program_id' => $prodi->id,
            'faculty_id' => $faculty->id,
            'group_id' => $group->id,
        ]);

        // DPL in this prodi
        User::factory()->dpl()->create([
            'study_program_id' => $prodi->id,
            'faculty_id' => $faculty->id,
        ]);

        Livewire::actingAs($prodiUser)
            ->test(ProdiDashboard::class)
            ->assertSee('Informatika')
            ->assertSee('Fakultas Teknik')
            ->assertSee('Mahasiswa KKN')
            ->assertSee('4')
            ->assertSee('1')
            ->assertSee('Program Kerja')
            ->assertSee('Logbook Harian')
            ->assertSee('Aksi Cepat Program Studi');
    }

    public function test_fakultas_observer_sees_faculty_wide_metrics(): void
    {
        $faculty = Faculty::create([
            'code' => 'FEB',
            'name' => 'Fakultas Ekonomi dan Bisnis',
            'short_name' => 'FEB',
        ]);

        $prodi1 = StudyProgram::create([
            'code' => 'MNJ',
            'name' => 'Manajemen',
            'faculty_id' => $faculty->id,
        ]);

        $prodi2 = StudyProgram::create([
            'code' => 'AKT',
            'name' => 'Akuntansi',
            'faculty_id' => $faculty->id,
        ]);

        $period = Period::create([
            'name' => 'KKN 2026',
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(25)->toDateString(),
        ]);

        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 04',
        ]);

        $fakultasUser = User::factory()->fakultas()->create([
            'faculty_id' => $faculty->id,
        ]);

        // Students in FEB
        User::factory()->mahasiswa()->count(3)->create([
            'study_program_id' => $prodi1->id,
            'faculty_id' => $faculty->id,
            'group_id' => $group->id,
        ]);

        User::factory()->mahasiswa()->count(2)->create([
            'study_program_id' => $prodi2->id,
            'faculty_id' => $faculty->id,
            'group_id' => $group->id,
        ]);

        Livewire::actingAs($fakultasUser)
            ->test(FakultasDashboard::class)
            ->assertSee('Fakultas Ekonomi dan Bisnis')
            ->assertSee('FEB')
            ->assertSee('Mahasiswa KKN')
            ->assertSee('5')
            ->assertSee('Program Studi')
            ->assertSee('2')
            ->assertSee('Aksi Cepat Fakultas');
    }
}
