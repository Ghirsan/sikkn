<?php

namespace Tests\Feature\Observer;

use App\Enums\Semester;
use App\Enums\UserRole;
use App\Livewire\Observer\Grades;
use App\Models\Faculty;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Period;
use App\Models\StudyProgram;
use App\Models\User;
use Database\Seeders\GradeWeightSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class FacultySiapGradesTest extends TestCase
{
    use RefreshDatabase;

    protected Faculty $facultyA;

    protected Faculty $facultyB;

    protected StudyProgram $prodiA;

    protected User $facultyUser;

    protected User $studentA;

    protected User $studentB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GradeWeightSeeder::class);

        $this->facultyA = Faculty::create(['name' => 'Fakultas Sains dan Matematika', 'code' => 'FSM', 'short_name' => 'FSM']);
        $this->facultyB = Faculty::create(['name' => 'Fakultas Teknik', 'code' => 'FT', 'short_name' => 'FT']);

        $this->prodiA = StudyProgram::create([
            'faculty_id' => $this->facultyA->id,
            'name' => 'Informatika',
            'code' => 'INF',
        ]);

        $this->facultyUser = User::factory()->create([
            'role' => UserRole::Fakultas,
            'faculty_id' => $this->facultyA->id,
        ]);

        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
        ]);

        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 1',
            'village' => 'Desa Makmur',
        ]);

        $this->studentA = User::factory()->mahasiswa()->create([
            'name' => 'Budi FSM',
            'nim' => '24060120120001',
            'faculty_id' => $this->facultyA->id,
            'study_program_id' => $this->prodiA->id,
            'group_id' => $group->id,
        ]);

        $this->studentB = User::factory()->mahasiswa()->create([
            'name' => 'Andi FT',
            'nim' => '21060120120002',
            'faculty_id' => $this->facultyB->id,
            'group_id' => $group->id,
        ]);

        // Give grade to student A
        $grade = Grade::create([
            'student_id' => $this->studentA->id,
            'dpl_id' => User::factory()->dpl()->create()->id,
            'pembekalan' => 80,
            'gelar_karya' => 80,
            'kehadiran' => 80,
            'lrk' => 80,
            'integritas' => 80,
            'sosial_kemasyarakatan' => 80,
            'lpk' => 80,
            'ujian_akhir' => 80,
        ]);
        $grade->calculateGrade();
        $grade->save();
    }

    public function test_faculty_can_export_siap_grades_excel(): void
    {
        Carbon::setTestNow('2026-10-13 12:00:00');

        Livewire::actingAs($this->facultyUser)
            ->test(Grades::class)
            ->call('exportSiapExcel')
            ->assertFileDownloaded('Export_Nilai_SIAP_Fakultas_Sains_dan_Matematika_20261013_120000.xlsx');
    }

    public function test_faculty_export_button_visible_to_faculty_only(): void
    {
        Livewire::actingAs($this->facultyUser)
            ->test(Grades::class)
            ->assertSee(__('Export Nilai SIAP'));

        $prodiUser = User::factory()->create([
            'role' => UserRole::Prodi,
            'study_program_id' => $this->prodiA->id,
        ]);

        Livewire::actingAs($prodiUser)
            ->test(Grades::class)
            ->assertDontSee(__('Export Nilai SIAP'));
    }
}
