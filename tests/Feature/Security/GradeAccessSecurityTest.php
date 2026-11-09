<?php

namespace Tests\Feature\Security;

use App\Enums\Semester;
use App\Livewire\Dpl\StudentGradeForm;
use App\Models\Group;
use App\Models\Period;
use App\Models\User;
use Database\Seeders\GradeWeightSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GradeAccessSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GradeWeightSeeder::class);
    }

    private function createGradingEnvironment(): array
    {
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => now()->subDays(5)->format('Y-m-d'),
            'end_date' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $groupA = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 1',
        ]);

        $groupB = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 2',
        ]);

        $dplA = User::factory()->dpl()->create();
        $groupA->dpls()->attach($dplA->id);

        $dplB = User::factory()->dpl()->create();
        $groupB->dpls()->attach($dplB->id);

        $studentA = User::factory()->mahasiswa()->create(['group_id' => $groupA->id]);
        $studentB = User::factory()->mahasiswa()->create(['group_id' => $groupB->id]);

        return [$period, $groupA, $groupB, $dplA, $dplB, $studentA, $studentB];
    }

    public function test_unassigned_dpl_cannot_access_or_submit_grade_for_unsupervised_student(): void
    {
        [, , , , $dplB, $studentA] = $this->createGradingEnvironment();

        // DPL B tries to access the grading form for Student A (who belongs to DPL A)
        $this->actingAs($dplB)->get(route('dpl.grades.form', $studentA))->assertForbidden();

        // DPL B tries to execute Livewire grading component directly for Student A
        Livewire::actingAs($dplB)
            ->test(StudentGradeForm::class, ['student' => $studentA->id])
            ->assertForbidden();
    }

    public function test_assigned_dpl_can_access_and_save_grade_for_their_student(): void
    {
        [, , , $dplA, , $studentA] = $this->createGradingEnvironment();

        // DPL A accesses Student A
        $this->actingAs($dplA)->get(route('dpl.grades.form', $studentA))->assertOk();

        // DPL A submits grades
        Livewire::actingAs($dplA)
            ->test(StudentGradeForm::class, ['student' => $studentA->id])
            ->set('pembekalan', 80)
            ->set('gelar_karya', 85)
            ->set('kehadiran', 90)
            ->set('lrk', 90)
            ->set('integritas', 95)
            ->set('sosial_kemasyarakatan', 90)
            ->set('lpk', 85)
            ->set('ujian_akhir', 88)
            ->call('saveGrade')
            ->assertHasNoErrors()
            ->assertRedirect(route('dpl.grades.index'));

        $this->assertDatabaseHas('grades', [
            'student_id' => $studentA->id,
            'dpl_id' => $dplA->id,
        ]);
    }

    public function test_student_cannot_access_dpl_grade_form(): void
    {
        [, , , , , $studentA] = $this->createGradingEnvironment();

        $this->actingAs($studentA)->get(route('dpl.grades.form', $studentA))->assertForbidden();
    }
}
