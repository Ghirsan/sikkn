<?php

namespace Tests\Feature\Dpl;

use App\Models\Grade;
use App\Models\Group;
use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepeatingStudentGradeTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeating_student_can_have_separate_grades_in_different_groups(): void
    {
        $period1 = Period::create([
            'name' => 'KKN 2026/1',
            'semester' => 'Ganjil',
            'year' => 2026,
            'start_date' => '2026-07-01',
            'end_date' => '2026-08-31',
        ]);

        $period2 = Period::create([
            'name' => 'KKN 2027/1',
            'semester' => 'Genap',
            'year' => 2027,
            'start_date' => '2027-01-01',
            'end_date' => '2027-02-28',
        ]);

        $group1 = Group::create([
            'period_id' => $period1->id,
            'name' => 'Kelompok 1 (2026)',
        ]);

        $group2 = Group::create([
            'period_id' => $period2->id,
            'name' => 'Kelompok 2 (2027)',
        ]);

        $dpl1 = User::factory()->dpl()->create();
        $dpl2 = User::factory()->dpl()->create();

        $student = User::factory()->mahasiswa()->create([
            'group_id' => $group1->id,
        ]);

        // Grade in period 1 / group 1
        $grade1 = Grade::create([
            'student_id' => $student->id,
            'dpl_id' => $dpl1->id,
            'group_id' => $group1->id,
            'final_grade' => 45.00,
            'grade_letter' => 'E',
            'keterangan' => 'Belum menyelesaikan syarat kelulusan.',
        ]);

        $this->assertSame($group1->id, $grade1->group_id);
        $this->assertFalse($grade1->isPassing());

        // Student repeats KKN in next period / group 2
        $student->update(['group_id' => $group2->id]);

        // Grade in period 2 / group 2
        $grade2 = Grade::create([
            'student_id' => $student->id,
            'dpl_id' => $dpl2->id,
            'group_id' => $group2->id,
            'final_grade' => 88.50,
            'grade_letter' => 'A',
            'keterangan' => 'Mengulang dengan sangat memuaskan.',
        ]);

        $this->assertSame($group2->id, $grade2->group_id);
        $this->assertTrue($grade2->isPassing());

        // Refresh relations
        $student = $student->fresh();
        $group1 = $group1->fresh();
        $group2 = $group2->fresh();

        // Check group grade scoping
        $this->assertCount(1, $group1->grades);
        $this->assertSame($grade1->id, $group1->grades->first()->id);

        $this->assertCount(1, $group2->grades);
        $this->assertSame($grade2->id, $group2->grades->first()->id);

        // Check student historical grades and latestOfMany
        $this->assertCount(2, $student->grades);
        $this->assertSame($grade2->id, $student->grade->id);

        // Check Grade::scopeForGroup
        $this->assertSame(1, Grade::forGroup($group1->id)->count());
        $this->assertSame($grade1->id, Grade::forGroup($group1->id)->first()->id);
        $this->assertSame(1, Grade::forGroup($group2->id)->count());
        $this->assertSame($grade2->id, Grade::forGroup($group2->id)->first()->id);
    }

    public function test_grade_automatically_resolves_group_id_from_student_when_omitted(): void
    {
        $period = Period::create([
            'name' => 'KKN 2026',
            'semester' => 'Ganjil',
            'year' => 2026,
            'start_date' => '2026-07-01',
            'end_date' => '2026-08-31',
        ]);

        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok Otomatis',
        ]);

        $dpl = User::factory()->dpl()->create();
        $student = User::factory()->mahasiswa()->create([
            'group_id' => $group->id,
        ]);

        // Create grade without explicitly supplying group_id
        $grade = Grade::create([
            'student_id' => $student->id,
            'dpl_id' => $dpl->id,
            'final_grade' => 82.00,
            'grade_letter' => 'A',
        ]);

        $this->assertNotNull($grade->group_id);
        $this->assertSame($group->id, $grade->group_id);
    }
}
