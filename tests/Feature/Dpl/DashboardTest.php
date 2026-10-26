<?php

namespace Tests\Feature\Dpl;

use App\Enums\LogStatus;
use App\Enums\ProgramStatus;
use App\Enums\Semester;
use App\Livewire\Dpl\Dashboard;
use App\Models\DailyLog;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Period;
use App\Models\Program;
use App\Models\ProgramParticipant;
use App\Models\ProgramType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dpl_without_groups_sees_empty_state(): void
    {
        $dpl = User::factory()->dpl()->create();

        Livewire::actingAs($dpl)
            ->test(Dashboard::class)
            ->assertSee('Belum Ada Kelompok Bimbingan Ditugaskan');
    }

    public function test_dpl_with_groups_sees_supervisory_metrics(): void
    {
        $period = Period::create([
            'name' => 'KKN Semester Genap 2026',
            'semester' => Semester::Genap->value,
            'year' => 2026,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(25)->toDateString(),
        ]);

        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 01 Desa Cendana',
            'village' => 'Desa Cendana',
            'district' => 'Kecamatan Asri',
            'regency' => 'Kabupaten Damai',
        ]);

        $dpl = User::factory()->dpl()->create();
        $group->dpls()->attach($dpl->id);

        $student1 = User::factory()->mahasiswa()->create(['group_id' => $group->id]);
        $student2 = User::factory()->mahasiswa()->create(['group_id' => $group->id]);

        Livewire::actingAs($dpl)
            ->test(Dashboard::class)
            ->assertSee('Kelompok 01 Desa Cendana')
            ->assertSee('Desa Cendana')
            ->assertSee('Mahasiswa Bimbingan')
            ->assertSee('Program Review')
            ->assertSee('Logbook Pending')
            ->assertSee('Bimbingan Pending')
            ->assertSee('Kesiapan Dokumen Kelompok')
            ->assertSee('Progres Penilaian SIAP');
    }

    public function test_dpl_sees_pending_reviews_in_queues(): void
    {
        $period = Period::create([
            'name' => 'KKN Semester Genap 2026',
            'semester' => Semester::Genap->value,
            'year' => 2026,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(25)->toDateString(),
        ]);

        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 02 Desa Melati',
            'village' => 'Desa Melati',
            'district' => 'Kecamatan Asri',
            'regency' => 'Kabupaten Damai',
        ]);

        $dpl = User::factory()->dpl()->create();
        $group->dpls()->attach($dpl->id);

        $student = User::factory()->mahasiswa()->create([
            'name' => 'Ahmad Mahasiswa',
            'group_id' => $group->id,
        ]);

        $programType = ProgramType::firstOrCreate(
            ['code' => 'multidisiplin'],
            ['name' => 'Multidisiplin']
        );

        $program = Program::create([
            'group_id' => $group->id,
            'title' => 'Pengembangan Website Desa Melati',
            'program_type_id' => $programType->id,
        ]);

        ProgramParticipant::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'participant_title' => 'Pengembangan Website Desa Melati',
            'status' => ProgramStatus::Submitted,
        ]);

        DailyLog::create([
            'student_id' => $student->id,
            'date' => now()->toDateString(),
            'important_notes' => 'Observasi balai desa dan wawancara kades',
            'status' => LogStatus::Pending,
        ]);

        Livewire::actingAs($dpl)
            ->test(Dashboard::class)
            ->assertSee('Pengembangan Website Desa Melati')
            ->assertSee('Ahmad Mahasiswa')
            ->assertSee('Observasi balai desa dan wawancara kades');
    }

    public function test_dpl_can_filter_metrics_by_group(): void
    {
        $period = Period::create([
            'name' => 'KKN 2026',
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(25)->toDateString(),
        ]);

        $groupA = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok Alpha',
            'village' => 'Desa Alpha',
        ]);

        $groupB = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok Beta',
            'village' => 'Desa Beta',
        ]);

        $dpl = User::factory()->dpl()->create();
        $groupA->dpls()->attach($dpl->id);
        $groupB->dpls()->attach($dpl->id);

        User::factory()->mahasiswa()->count(3)->create(['group_id' => $groupA->id]);
        User::factory()->mahasiswa()->count(5)->create(['group_id' => $groupB->id]);

        Livewire::actingAs($dpl)
            ->test(Dashboard::class)
            // Aggregated: 3 + 5 = 8 students
            ->assertSee('8')
            ->assertSee('Dalam 2 kelompok')
            // Switch filter to Group A
            ->set('selectedGroupId', $groupA->id)
            ->assertSee('3')
            ->assertSee('Dalam 1 kelompok');
    }

    public function test_dpl_grading_progress_reflects_actual_grades(): void
    {
        $period = Period::create([
            'name' => 'KKN 2026',
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(25)->toDateString(),
        ]);

        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok Gamma',
            'village' => 'Desa Gamma',
        ]);

        $dpl = User::factory()->dpl()->create();
        $group->dpls()->attach($dpl->id);

        $student1 = User::factory()->mahasiswa()->create(['group_id' => $group->id]);
        $student2 = User::factory()->mahasiswa()->create(['group_id' => $group->id]);

        // Student 1 has final grade
        Grade::create([
            'group_id' => $group->id,
            'student_id' => $student1->id,
            'dpl_id' => $dpl->id,
            'final_grade' => 85.50,
            'grade_letter' => 'A',
        ]);

        Livewire::actingAs($dpl)
            ->test(Dashboard::class)
            ->assertSee('1 / 2')
            ->assertSee('50% Selesai');
    }
}
