<?php

namespace Tests\Feature\Observer;

use App\Enums\Semester;
use App\Enums\UserRole;
use App\Livewire\Observer\GradeDetail;
use App\Livewire\Observer\Grades;
use App\Models\Faculty;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Period;
use App\Models\StudyProgram;
use App\Models\User;
use Database\Seeders\GradeWeightSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ObserverGradeDetailTest extends TestCase
{
    use RefreshDatabase;

    protected Faculty $facultyA;

    protected Faculty $facultyB;

    protected StudyProgram $prodiA;

    protected StudyProgram $prodiB;

    protected User $prodiUser;

    protected User $facultyUser;

    protected User $dpl;

    protected User $studentA;

    protected User $studentB;

    protected Group $group;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GradeWeightSeeder::class);

        $this->facultyA = Faculty::create([
            'name' => 'Fakultas Sains dan Matematika',
            'code' => 'FSM',
            'short_name' => 'FSM',
        ]);

        $this->facultyB = Faculty::create([
            'name' => 'Fakultas Teknik',
            'code' => 'FT',
            'short_name' => 'FT',
        ]);

        $this->prodiA = StudyProgram::create([
            'faculty_id' => $this->facultyA->id,
            'name' => 'Informatika',
            'code' => 'INF',
        ]);

        $this->prodiB = StudyProgram::create([
            'faculty_id' => $this->facultyA->id,
            'name' => 'Statistika',
            'code' => 'STA',
        ]);

        $this->prodiUser = User::factory()->create([
            'role' => UserRole::Prodi,
            'study_program_id' => $this->prodiA->id,
            'faculty_id' => $this->facultyA->id,
        ]);

        $this->facultyUser = User::factory()->create([
            'role' => UserRole::Fakultas,
            'faculty_id' => $this->facultyA->id,
        ]);

        $this->dpl = User::factory()->dpl()->create([
            'name' => 'Dr. Pembimbing',
            'nip' => '198001012005011001',
        ]);

        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
        ]);

        $this->group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 1',
            'village' => 'Desa Makmur',
            'lead_dpl_id' => $this->dpl->id,
        ]);

        $this->studentA = User::factory()->mahasiswa()->create([
            'name' => 'Budi Informatika',
            'nim' => '24060120120001',
            'faculty_id' => $this->facultyA->id,
            'study_program_id' => $this->prodiA->id,
            'group_id' => $this->group->id,
        ]);

        $this->studentB = User::factory()->mahasiswa()->create([
            'name' => 'Siti Statistika',
            'nim' => '24060120120002',
            'faculty_id' => $this->facultyA->id,
            'study_program_id' => $this->prodiB->id,
            'group_id' => $this->group->id,
        ]);
    }

    public function test_observer_grades_list_shows_action_column_and_view_grade_button(): void
    {
        $this->actingAs($this->prodiUser);

        Livewire::test(Grades::class)
            ->assertSee('Aksi')
            ->assertSee('Lihat Nilai')
            ->assertSee(route('prodi.grades.show', $this->studentA->id));
    }

    public function test_prodi_user_can_view_grade_detail_for_students_in_same_prodi(): void
    {
        $this->actingAs($this->prodiUser);

        Grade::create([
            'student_id' => $this->studentA->id,
            'dpl_id' => $this->dpl->id,
            'group_id' => $this->group->id,
            'pembekalan' => 85,
            'gelar_karya' => 90,
            'kehadiran' => 95,
            'lrk' => 88,
            'integritas' => 92,
            'sosial_kemasyarakatan' => 90,
            'lpk' => 86,
            'ujian_akhir' => 89,
            'aktivitas_partisipatif' => 90,
            'hasil_proyek' => 88,
            'tugas' => 93.5,
            'quiz' => 87.5,
            'uts' => 86,
            'uas' => 89,
            'final_grade' => 89.15,
            'grade_letter' => 'A',
            'keterangan' => 'Sangat aktif dan berkontribusi baik.',
        ]);

        $response = $this->get(route('prodi.grades.show', $this->studentA->id));
        $response->assertOk();
        $response->assertSee('Detail Penilaian Mahasiswa');
        $response->assertSee('Budi Informatika');
        $response->assertSee('Dr. Pembimbing');
        $response->assertSee('89.15');
        $response->assertSee('A');
    }

    public function test_prodi_user_cannot_view_grade_detail_for_students_in_different_prodi(): void
    {
        $this->actingAs($this->prodiUser);

        $response = $this->get(route('prodi.grades.show', $this->studentB->id));
        $response->assertForbidden();
    }

    public function test_fakultas_user_can_view_grade_detail_for_students_in_same_faculty(): void
    {
        $this->actingAs($this->facultyUser);

        $responseA = $this->get(route('fakultas.grades.show', $this->studentA->id));
        $responseA->assertOk();

        $responseB = $this->get(route('fakultas.grades.show', $this->studentB->id));
        $responseB->assertOk();
    }

    public function test_fakultas_user_cannot_view_grade_detail_for_students_in_different_faculty(): void
    {
        $studentFT = User::factory()->mahasiswa()->create([
            'name' => 'Anton FT',
            'nim' => '21060120120003',
            'faculty_id' => $this->facultyB->id,
            'group_id' => $this->group->id,
        ]);

        $this->actingAs($this->facultyUser);

        $response = $this->get(route('fakultas.grades.show', $studentFT->id));
        $response->assertForbidden();
    }

    public function test_ungraded_student_shows_notice_and_disabled_export(): void
    {
        $this->actingAs($this->prodiUser);

        Livewire::test(GradeDetail::class, ['student' => $this->studentA->id])
            ->assertSee('Belum Dinilai')
            ->assertSee('Ekspor Excel tersedia setelah mahasiswa dinilai oleh DPL.')
            ->call('exportExcel')
            ->assertDispatched('toast');
    }

    public function test_graded_student_can_export_single_siap_excel(): void
    {
        $this->actingAs($this->prodiUser);

        Grade::create([
            'student_id' => $this->studentA->id,
            'dpl_id' => $this->dpl->id,
            'group_id' => $this->group->id,
            'pembekalan' => 85,
            'gelar_karya' => 90,
            'kehadiran' => 95,
            'lrk' => 88,
            'integritas' => 92,
            'sosial_kemasyarakatan' => 90,
            'lpk' => 86,
            'ujian_akhir' => 89,
            'aktivitas_partisipatif' => 90,
            'hasil_proyek' => 88,
            'tugas' => 93.5,
            'quiz' => 87.5,
            'uts' => 86,
            'uas' => 89,
            'final_grade' => 89.15,
            'grade_letter' => 'A',
            'keterangan' => 'Sangat aktif.',
        ]);

        $component = Livewire::test(GradeDetail::class, ['student' => $this->studentA->id])
            ->assertSee('Mode Hanya Baca')
            ->call('exportExcel');

        $component->assertFileDownloaded('Nilai_SIAP_24060120120001_Budi_Informatika.xlsx');
    }
}

