<?php

namespace Tests\Feature;

use App\Enums\ProgramStatus;
use App\Enums\Semester;
use App\Enums\UserRole;
use App\Livewire\Admin\Faculties;
use App\Livewire\Admin\GroupTypes;
use App\Livewire\Admin\Periods;
use App\Livewire\Admin\ProgramTypes;
use App\Livewire\Admin\SdgCategories;
use App\Livewire\Admin\Students as AdminStudents;
use App\Livewire\Admin\StudyPrograms;
use App\Livewire\Admin\Users as AdminUsers;
use App\Livewire\Dpl\TeamDocuments;
use App\Livewire\Mahasiswa\LpkDocuments;
use App\Livewire\Mahasiswa\LrkDocuments;
use App\Livewire\Mahasiswa\Programs as MahasiswaPrograms;
use App\Livewire\Observer\Documents as ObserverDocuments;
use App\Livewire\Observer\Dpls as ObserverDpls;
use App\Livewire\Observer\Grades as ObserverGrades;
use App\Livewire\Observer\Logbooks as ObserverLogbooks;
use App\Livewire\Observer\Mentoring as ObserverMentoring;
use App\Livewire\Observer\Programs as ObserverPrograms;
use App\Livewire\Observer\Students as ObserverStudents;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\GroupType;
use App\Models\Period;
use App\Models\Program;
use App\Models\ProgramParticipant;
use App\Models\ProgramType;
use App\Models\SdgCategory;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StatsCardCoverageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_admin_table_pages_render_informative_stats_cards(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $period = Period::create([
            'name' => 'KKN 2026',
            'semester' => Semester::Ganjil,
            'year' => '2026',
            'start_date' => '2026-07-01',
            'end_date' => '2026-08-31',
        ]);
        $faculty = Faculty::create(['name' => 'Fakultas Teknik', 'code' => 'FT', 'short_name' => 'FT']);
        $prodi = StudyProgram::create(['name' => 'Informatika', 'code' => 'IF', 'faculty_id' => $faculty->id]);
        $groupType = GroupType::firstOrCreate(['code' => 'REG'], ['name' => 'Reguler']);
        $programType = ProgramType::firstOrCreate(['code' => 'multidisiplin'], ['name' => 'Multidisiplin']);
        $sdg = SdgCategory::firstOrCreate(['code' => 1], ['name' => 'Tanpa Kemiskinan']);

        // Admin Students
        Livewire::actingAs($admin)
            ->test(AdminStudents::class)
            ->assertSee('Total Mahasiswa')
            ->assertSee('Sudah Ditempatkan')
            ->assertSee('Belum Ditempatkan');

        // Admin Users
        Livewire::actingAs($admin)
            ->test(AdminUsers::class)
            ->assertSee('Total Pengelola')
            ->assertSee('Admin P2KKN')
            ->assertSee('Akun Fakultas')
            ->assertSee('Akun Program Studi');

        // Admin Periods
        Livewire::actingAs($admin)
            ->test(Periods::class)
            ->assertSee('Total Periode')
            ->assertSee('Periode Aktif')
            ->assertSee('Total Kelompok KKN');

        // Admin Faculties
        Livewire::actingAs($admin)
            ->test(Faculties::class)
            ->assertSee('Total Fakultas')
            ->assertSee('Total Program Studi')
            ->assertSee('Mahasiswa Terdaftar');

        // Admin Study Programs
        Livewire::actingAs($admin)
            ->test(StudyPrograms::class)
            ->assertSee('Total Program Studi')
            ->assertSee('Fakultas Terafiliasi')
            ->assertSee('Mahasiswa Terdaftar');

        // Admin Group Types
        Livewire::actingAs($admin)
            ->test(GroupTypes::class)
            ->assertSee('Total Jenis Kelompok')
            ->assertSee('Kelompok Terdaftar')
            ->assertSee('Kelompok Periode Aktif');

        // Admin Program Types
        Livewire::actingAs($admin)
            ->test(ProgramTypes::class)
            ->assertSee('Total Sifat Program')
            ->assertSee('Program Terdaftar')
            ->assertSee('Partisipasi Mahasiswa');

        // Admin SDG Categories
        Livewire::actingAs($admin)
            ->test(SdgCategories::class)
            ->assertSee('Total Pilar SDG')
            ->assertSee('Partisipasi Terkait')
            ->assertSee('Pilar Diadopsi');
    }

    public function test_dpl_team_documents_renders_informative_stats_cards(): void
    {
        $dpl = User::factory()->dpl()->create();
        $period = Period::create([
            'name' => 'KKN 2026',
            'semester' => Semester::Ganjil,
            'year' => '2026',
            'start_date' => '2026-07-01',
            'end_date' => '2026-08-31',
        ]);
        $group = Group::create([
            'name' => 'Kelompok 1',
            'period_id' => $period->id,
            'lead_dpl_id' => $dpl->id,
        ]);
        $group->dpls()->attach($dpl->id);

        Livewire::actingAs($dpl)
            ->test(TeamDocuments::class)
            ->assertSee('Total Program Tim')
            ->assertSee('Program Disetujui')
            ->assertSee('Menunggu Review')
            ->assertSee('Tim Siap Cetak');
    }

    public function test_mahasiswa_table_pages_render_informative_stats_cards(): void
    {
        $faculty = Faculty::create(['name' => 'Fakultas Teknik', 'code' => 'FT']);
        $prodi = StudyProgram::create(['name' => 'Informatika', 'code' => 'IF', 'faculty_id' => $faculty->id]);
        $period = Period::create([
            'name' => 'KKN 2026',
            'semester' => Semester::Ganjil,
            'year' => '2026',
            'start_date' => '2026-07-01',
            'end_date' => '2026-08-31',
        ]);
        $group = Group::create([
            'name' => 'Kelompok 1',
            'period_id' => $period->id,
        ]);
        $student = User::factory()->mahasiswa()->create([
            'group_id' => $group->id,
            'faculty_id' => $faculty->id,
            'study_program_id' => $prodi->id,
        ]);
        $group->update(['student_leader_id' => $student->id]);

        $multiType = ProgramType::firstOrCreate(['code' => 'multidisiplin'], ['name' => 'Multidisiplin']);
        ProgramType::firstOrCreate(['code' => 'sosial_kemasyarakatan'], ['name' => 'Sosmas']);
        ProgramType::firstOrCreate(['code' => 'lainnya'], ['name' => 'Lainnya']);

        $program = Program::create([
            'group_id' => $group->id,
            'title' => 'Digitalisasi UMKM',
            'program_type_id' => $multiType->id,
        ]);
        ProgramParticipant::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'status' => ProgramStatus::Approved,
            'lpk_status' => ProgramStatus::Draft,
        ]);

        Livewire::actingAs($student)
            ->test(MahasiswaPrograms::class)
            ->assertSee('Total Program Kerja')
            ->assertSee('Multidisiplin')
            ->assertSee('Keilmuan / Mandiri')
            ->assertSee('Disetujui Dosen KKN');

        Livewire::actingAs($student)
            ->test(LrkDocuments::class)
            ->assertSee('Total Program Tim')
            ->assertSee('Rencana Disetujui')
            ->assertSee('Belum Disetujui')
            ->assertSee('Kesiapan LRK');

        Livewire::actingAs($student)
            ->test(LpkDocuments::class)
            ->assertSee('Total Program Tim')
            ->assertSee('Laporan Disetujui')
            ->assertSee('Belum Disetujui')
            ->assertSee('Kesiapan LPK');
    }

    public function test_observer_table_pages_render_informative_stats_cards(): void
    {
        $faculty = Faculty::create(['name' => 'Fakultas Teknik', 'code' => 'FT']);
        $prodi = StudyProgram::create(['name' => 'Informatika', 'code' => 'IF', 'faculty_id' => $faculty->id]);
        $observer = User::factory()->create([
            'role' => UserRole::Prodi,
            'faculty_id' => $faculty->id,
            'study_program_id' => $prodi->id,
        ]);

        Livewire::actingAs($observer)
            ->test(ObserverStudents::class)
            ->assertSee('Total Mahasiswa')
            ->assertSee('Sudah Ditempatkan')
            ->assertSee('Belum Ditempatkan');

        Livewire::actingAs($observer)
            ->test(ObserverDpls::class)
            ->assertSee('Total Dosen KKN')
            ->assertSee('Membimbing')
            ->assertSee('Belum Menugaskan');

        Livewire::actingAs($observer)
            ->test(ObserverPrograms::class)
            ->assertSee('Total Usulan Program')
            ->assertSee('LRK Disetujui')
            ->assertSee('LPK Disetujui')
            ->assertSee('Menunggu Review');

        Livewire::actingAs($observer)
            ->test(ObserverLogbooks::class)
            ->assertSee('Total Entri')
            ->assertSee('Disetujui')
            ->assertSee('Menunggu')
            ->assertSee('Total Jam Kerja');

        Livewire::actingAs($observer)
            ->test(ObserverMentoring::class)
            ->assertSee('Total Sesi')
            ->assertSee('Sudah Ditanggapi')
            ->assertSee('Menunggu Tanggapan');

        Livewire::actingAs($observer)
            ->test(ObserverGrades::class)
            ->assertSee('Total Mahasiswa')
            ->assertSee('Sudah Dinilai')
            ->assertSee('Belum Dinilai')
            ->assertSee('Rata-rata Nilai');

        Livewire::actingAs($observer)
            ->test(ObserverDocuments::class)
            ->assertSee('Total Kelompok')
            ->assertSee('LRK Difinalisasi')
            ->assertSee('LPK Difinalisasi')
            ->assertSee('Dalam Proses');
    }
}
