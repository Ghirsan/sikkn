<?php

namespace Tests\Feature;

use App\Enums\PeriodStatus;
use App\Enums\Semester;
use App\Livewire\Dpl\StudentGradeForm;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Period;
use App\Models\User;
use Database\Seeders\GradeWeightSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GradeLetterCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GradeWeightSeeder::class);
    }

    public function test_extract_year_from_nim(): void
    {
        $this->assertSame(2024, Grade::extractYearFromNim('24060124130001'));
        $this->assertSame(2023, Grade::extractYearFromNim('24060123140002'));
        $this->assertSame(2020, Grade::extractYearFromNim('24060120120001'));
        $this->assertSame(1999, Grade::extractYearFromNim('24060199120001'));

        $this->assertNull(Grade::extractYearFromNim(null));
        $this->assertNull(Grade::extractYearFromNim(''));
        $this->assertNull(Grade::extractYearFromNim('12345'));
        $this->assertNull(Grade::extractYearFromNim('240601XX120001'));
    }

    public function test_determine_grade_letter_for_2024_and_later_batch(): void
    {
        $nim2024 = '24060124130001';

        $this->assertSame('A', Grade::determineGradeLetter(85.00, $nim2024));
        $this->assertSame('A', Grade::determineGradeLetter(100.00, $nim2024));

        $this->assertSame('AB', Grade::determineGradeLetter(84.99, $nim2024));
        $this->assertSame('AB', Grade::determineGradeLetter(80.00, $nim2024));

        $this->assertSame('B', Grade::determineGradeLetter(79.99, $nim2024));
        $this->assertSame('B', Grade::determineGradeLetter(75.00, $nim2024));

        $this->assertSame('BC', Grade::determineGradeLetter(74.99, $nim2024));
        $this->assertSame('BC', Grade::determineGradeLetter(70.00, $nim2024));

        $this->assertSame('C', Grade::determineGradeLetter(69.99, $nim2024));
        $this->assertSame('C', Grade::determineGradeLetter(60.00, $nim2024));

        $this->assertSame('D', Grade::determineGradeLetter(59.99, $nim2024));
        $this->assertSame('D', Grade::determineGradeLetter(40.00, $nim2024));

        $this->assertSame('E', Grade::determineGradeLetter(39.99, $nim2024));
        $this->assertSame('E', Grade::determineGradeLetter(0.00, $nim2024));
    }

    public function test_determine_grade_letter_defaults_to_new_system_when_nim_is_missing_or_invalid(): void
    {
        $this->assertSame('A', Grade::determineGradeLetter(85.00, null));
        $this->assertSame('AB', Grade::determineGradeLetter(82.50, null));
        $this->assertSame('B', Grade::determineGradeLetter(76.00, ''));
        $this->assertSame('BC', Grade::determineGradeLetter(72.00, 'invalid'));
        $this->assertSame('C', Grade::determineGradeLetter(65.00, null));
        $this->assertSame('D', Grade::determineGradeLetter(45.00, null));
        $this->assertSame('E', Grade::determineGradeLetter(35.00, null));
    }

    public function test_determine_grade_letter_for_pre_2024_batch(): void
    {
        $nim2023 = '24060123140001';

        $this->assertSame('A', Grade::determineGradeLetter(80.00, $nim2023));
        $this->assertSame('A', Grade::determineGradeLetter(84.99, $nim2023));

        $this->assertSame('B', Grade::determineGradeLetter(79.99, $nim2023));
        $this->assertSame('B', Grade::determineGradeLetter(70.00, $nim2023));

        $this->assertSame('C', Grade::determineGradeLetter(69.99, $nim2023));
        $this->assertSame('C', Grade::determineGradeLetter(60.00, $nim2023));

        $this->assertSame('D', Grade::determineGradeLetter(59.99, $nim2023));
        $this->assertSame('D', Grade::determineGradeLetter(50.00, $nim2023));

        $this->assertSame('E', Grade::determineGradeLetter(49.99, $nim2023));
        $this->assertSame('E', Grade::determineGradeLetter(0.00, $nim2023));
    }

    public function test_grade_is_passing_requires_minimum_c(): void
    {
        $gradeA = new Grade(['grade_letter' => 'A']);
        $gradeAB = new Grade(['grade_letter' => 'AB']);
        $gradeB = new Grade(['grade_letter' => 'B']);
        $gradeBC = new Grade(['grade_letter' => 'BC']);
        $gradeC = new Grade(['grade_letter' => 'C']);
        $gradeD = new Grade(['grade_letter' => 'D']);
        $gradeE = new Grade(['grade_letter' => 'E']);

        $this->assertTrue($gradeA->isPassing());
        $this->assertTrue($gradeAB->isPassing());
        $this->assertTrue($gradeB->isPassing());
        $this->assertTrue($gradeBC->isPassing());
        $this->assertTrue($gradeC->isPassing());

        $this->assertFalse($gradeD->isPassing());
        $this->assertFalse($gradeE->isPassing());
    }

    public function test_calculate_grade_applies_correct_letter_system_based_on_student_nim(): void
    {
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
            'status' => PeriodStatus::Completed,
        ]);
        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 10',
            'village' => 'Desa Sukamaju',
        ]);
        $dpl = User::factory()->dpl()->create();

        // 2024 student with score 82.00 -> should be AB
        $student2024 = User::factory()->mahasiswa()->create([
            'nim' => '24060124130001',
            'group_id' => $group->id,
        ]);

        $grade2024 = Grade::make([
            'student_id' => $student2024->id,
            'dpl_id' => $dpl->id,
            'group_id' => $group->id,
            'pembekalan' => 82.00,
            'gelar_karya' => 82.00,
            'kehadiran' => 82.00,
            'lrk' => 82.00,
            'integritas' => 82.00,
            'sosial_kemasyarakatan' => 82.00,
            'lpk' => 82.00,
            'ujian_akhir' => 82.00,
        ]);
        $grade2024->setRelation('student', $student2024);
        $grade2024->calculateGrade();

        $this->assertEquals(82.00, (float) $grade2024->final_grade);
        $this->assertSame('AB', $grade2024->grade_letter);

        // 2023 student with score 82.00 -> should be A
        $student2023 = User::factory()->mahasiswa()->create([
            'nim' => '24060123140001',
            'group_id' => $group->id,
        ]);

        $grade2023 = Grade::make([
            'student_id' => $student2023->id,
            'dpl_id' => $dpl->id,
            'group_id' => $group->id,
            'pembekalan' => 82.00,
            'gelar_karya' => 82.00,
            'kehadiran' => 82.00,
            'lrk' => 82.00,
            'integritas' => 82.00,
            'sosial_kemasyarakatan' => 82.00,
            'lpk' => 82.00,
            'ujian_akhir' => 82.00,
        ]);
        $grade2023->setRelation('student', $student2023);
        $grade2023->calculateGrade();

        $this->assertEquals(82.00, (float) $grade2023->final_grade);
        $this->assertSame('A', $grade2023->grade_letter);
    }

    public function test_livewire_student_grade_form_computes_new_grade_letter(): void
    {
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
            'status' => PeriodStatus::Active,
        ]);
        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 10',
            'village' => 'Desa Sukamaju',
        ]);
        $dpl = User::factory()->dpl()->create();
        $dpl->dplGroups()->attach($group);

        $student2024 = User::factory()->mahasiswa()->create([
            'nim' => '24060124130001',
            'group_id' => $group->id,
        ]);

        Livewire::actingAs($dpl)
            ->test(StudentGradeForm::class, ['student' => $student2024->id])
            ->set('pembekalan', 82)
            ->set('gelar_karya', 82)
            ->set('kehadiran', 82)
            ->set('lrk', 82)
            ->set('integritas', 82)
            ->set('sosial_kemasyarakatan', 82)
            ->set('lpk', 82)
            ->set('ujian_akhir', 82)
            ->assertSee('AB');
    }
}
