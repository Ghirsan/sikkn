<?php

namespace Tests\Feature\Dpl;

use App\Enums\PeriodStatus;
use App\Enums\Semester;
use App\Livewire\Dpl\StudentGradeForm;
use App\Livewire\Dpl\StudentGrades;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Period;
use App\Models\User;
use App\Services\GradeExcelService;
use Database\Seeders\GradeWeightSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class StudentGradesExcelTest extends TestCase
{
    use RefreshDatabase;

    protected User $dpl;

    protected Group $group;

    protected Period $period;

    protected User $student1;

    protected User $student2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GradeWeightSeeder::class);

        $this->period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
            'status' => PeriodStatus::Completed,
        ]);

        $this->group = Group::create([
            'period_id' => $this->period->id,
            'name' => 'Kelompok 10',
            'village' => 'Desa Sukamaju',
        ]);

        $this->dpl = User::factory()->dpl()->create();
        $this->dpl->dplGroups()->attach($this->group);

        $this->student1 = User::factory()->mahasiswa()->create([
            'name' => 'Budi Santoso',
            'nim' => '24060120120001',
            'group_id' => $this->group->id,
        ]);

        $this->student2 = User::factory()->mahasiswa()->create([
            'name' => 'Siti Rahma',
            'nim' => '24060120120002',
            'group_id' => $this->group->id,
        ]);
    }

    /**
     * Helper to generate a test .xlsx file.
     *
     * @param  array<int, array<int, mixed>>  $dataRows
     */
    protected function createExcelFile(array $dataRows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'No', 'Nama', 'NIM', 'Prodi', 'Fakultas', 'Desa',
            'Pembekalan', 'Gelar Karya', 'Kehadiran', 'LRK', 'Integritas', 'Sosial Kemasyarakatan', 'LPK', 'Ujian Akhir',
            'Keterangan',
        ];
        $sheet->fromArray($headers, null, 'A1');

        $rowIdx = 2;
        foreach ($dataRows as $row) {
            $sheet->fromArray($row, null, "A{$rowIdx}");
            $rowIdx++;
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'test_grade_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $content = file_get_contents($tempPath);
        unlink($tempPath);

        return UploadedFile::fake()->createWithContent('nilai_test.xlsx', $content);
    }

    public function test_dpl_can_export_grades_excel(): void
    {
        Carbon::setTestNow('2026-10-13 12:00:00');

        Livewire::actingAs($this->dpl)
            ->test(StudentGrades::class)
            ->call('exportExcel')
            ->assertFileDownloaded('Format_Nilai_KKN_20261013_120000.xlsx');
    }

    public function test_dpl_can_import_valid_grades_excel(): void
    {
        $file = $this->createExcelFile([
            [1, 'Budi Santoso', '24060120120001', 'Informatika', 'Sains dan Matematika', 'Desa Sukamaju', 80, 85, 90, 80, 90, 85, 80, 85, 'Sangat baik'],
            [2, 'Siti Rahma', '24060120120002', 'Informatika', 'Sains dan Matematika', 'Desa Sukamaju', 75, 80, 85, 75, 85, 80, 75, 80, 'Baik'],
        ]);

        Livewire::actingAs($this->dpl)
            ->test(StudentGrades::class)
            ->set('gradeFile', $file)
            ->call('importExcel')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('grades', [
            'student_id' => $this->student1->id,
            'dpl_id' => $this->dpl->id,
            'pembekalan' => 80.00,
            'gelar_karya' => 85.00,
            'kehadiran' => 90.00,
            'lrk' => 80.00,
            'integritas' => 90.00,
            'sosial_kemasyarakatan' => 85.00,
            'lpk' => 80.00,
            'ujian_akhir' => 85.00,
            'keterangan' => 'Sangat baik',
        ]);

        $grade1 = Grade::where('student_id', $this->student1->id)->first();
        $this->assertNotNull($grade1);
        $this->assertNotNull($grade1->final_grade);
        $this->assertEquals('A', $grade1->grade_letter);

        $this->assertDatabaseHas('grades', [
            'student_id' => $this->student2->id,
            'dpl_id' => $this->dpl->id,
            'pembekalan' => 75.00,
        ]);
    }

    public function test_dpl_import_skips_blank_student_rows_and_only_saves_filled(): void
    {
        $file = $this->createExcelFile([
            [1, 'Budi Santoso', '24060120120001', 'Informatika', 'Sains dan Matematika', 'Desa Sukamaju', 85, 85, 90, 85, 90, 85, 85, 85, ''],
            [2, 'Siti Rahma', '24060120120002', 'Informatika', 'Sains dan Matematika', 'Desa Sukamaju', '', '', '', '', '', '', '', '', ''],
        ]);

        Livewire::actingAs($this->dpl)
            ->test(StudentGrades::class)
            ->set('gradeFile', $file)
            ->call('importExcel')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('grades', [
            'student_id' => $this->student1->id,
        ]);

        $this->assertDatabaseMissing('grades', [
            'student_id' => $this->student2->id,
        ]);
    }

    public function test_dpl_import_strict_validation_rejects_invalid_score_and_rolls_back(): void
    {
        $file = $this->createExcelFile([
            [1, 'Budi Santoso', '24060120120001', 'Informatika', 'Sains dan Matematika', 'Desa Sukamaju', 85, 85, 90, 85, 90, 85, 85, 85, ''],
            // Invalid score: 150
            [2, 'Siti Rahma', '24060120120002', 'Informatika', 'Sains dan Matematika', 'Desa Sukamaju', 80, 80, 80, 150, 80, 80, 80, 80, ''],
        ]);

        $component = Livewire::actingAs($this->dpl)
            ->test(StudentGrades::class)
            ->set('gradeFile', $file)
            ->call('importExcel');

        $this->assertNotEmpty($component->get('importErrors'));

        // All-or-nothing: Neither student is saved
        $this->assertDatabaseMissing('grades', [
            'student_id' => $this->student1->id,
        ]);
        $this->assertDatabaseMissing('grades', [
            'student_id' => $this->student2->id,
        ]);
    }

    public function test_dpl_import_rejects_student_not_in_dpl_group(): void
    {
        $strangerStudent = User::factory()->mahasiswa()->create([
            'name' => 'Mahasiswa Lain',
            'nim' => '99999999999999',
        ]);

        $file = $this->createExcelFile([
            [1, 'Mahasiswa Lain', '99999999999999', 'Informatika', 'Sains dan Matematika', 'Desa Lain', 80, 80, 80, 80, 80, 80, 80, 80, ''],
        ]);

        $component = Livewire::actingAs($this->dpl)
            ->test(StudentGrades::class)
            ->set('gradeFile', $file)
            ->call('importExcel');

        $this->assertNotEmpty($component->get('importErrors'));
        $this->assertDatabaseMissing('grades', [
            'student_id' => $strangerStudent->id,
        ]);
    }

    public function test_dpl_can_import_and_grade_bit_by_bit_while_period_is_active(): void
    {
        $this->period->update([
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(10),
        ]);

        $file = $this->createExcelFile([
            [1, 'Budi Santoso', '24060120120001', 'Informatika', 'Sains dan Matematika', 'Desa Sukamaju', 85, 85, 85, 85, 85, 85, 85, 85, 'Bit by bit grading'],
        ]);

        Livewire::actingAs($this->dpl)
            ->test(StudentGrades::class)
            ->set('gradeFile', $file)
            ->call('importExcel')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('grades', [
            'student_id' => $this->student1->id,
            'dpl_id' => $this->dpl->id,
            'keterangan' => 'Bit by bit grading',
        ]);
    }

    public function test_dpl_cannot_import_if_period_superseded_by_new_period(): void
    {
        // Admin creates a newer period, making $this->period no longer the current period
        Period::create([
            'semester' => Semester::Genap->value,
            'year' => 2026,
            'start_date' => '2026-07-01',
            'end_date' => '2026-08-01',
        ]);

        $file = $this->createExcelFile([
            [1, 'Budi Santoso', '24060120120001', 'Informatika', 'Sains dan Matematika', 'Desa Sukamaju', 85, 85, 85, 85, 85, 85, 85, 85, ''],
        ]);

        $component = Livewire::actingAs($this->dpl)
            ->test(StudentGrades::class)
            ->set('gradeFile', $file)
            ->call('importExcel');

        $this->assertNotEmpty($component->get('importErrors'));
        $this->assertContains(
            'Kelompok yang dipilih tidak berada pada periode KKN saat ini. Penilaian telah ditutup karena periode baru telah dibuat.',
            $component->get('importErrors')
        );
        $this->assertDatabaseMissing('grades', [
            'student_id' => $this->student1->id,
        ]);
    }

    public function test_dpl_single_student_grade_form_blocked_when_period_superseded(): void
    {
        // Admin creates a newer period
        Period::create([
            'semester' => Semester::Genap->value,
            'year' => 2026,
            'start_date' => '2026-07-01',
            'end_date' => '2026-08-01',
        ]);

        $component = Livewire::actingAs($this->dpl)
            ->test(StudentGradeForm::class, ['student' => $this->student1->id]);

        $this->assertFalse($component->get('isGradingOpen'));

        $component->call('saveGrade')
            ->assertStatus(403);
    }

    public function test_dpl_import_handles_nim_in_scientific_notation_or_float(): void
    {
        // 2.4060120120001E+13 is scientific notation for 24060120120001
        $file = $this->createExcelFile([
            [1, 'Budi Santoso', '2.4060120120001E+13', 'Informatika', 'Sains dan Matematika', 'Desa Sukamaju', 85, 85, 85, 85, 85, 85, 85, 85, ''],
        ]);

        Livewire::actingAs($this->dpl)
            ->test(StudentGrades::class)
            ->set('gradeFile', $file)
            ->call('importExcel')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('grades', [
            'student_id' => $this->student1->id,
            'dpl_id' => $this->dpl->id,
        ]);
    }

    public function test_export_sets_nim_strictly_as_string_data_type(): void
    {
        $service = app(GradeExcelService::class);
        $response = $service->exportDplGrades(collect([$this->student1]));

        // Read output from streamed response
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $tempPath = tempnam(sys_get_temp_dir(), 'check_nim_').'.xlsx';
        file_put_contents($tempPath, $content);

        $spreadsheet = IOFactory::load($tempPath);
        $sheet = $spreadsheet->getActiveSheet();

        // Row 2 Column C is NIM
        $cell = $sheet->getCell('C2');
        $this->assertEquals(DataType::TYPE_STRING, $cell->getDataType());
        $this->assertEquals('24060120120001', $cell->getValue());

        unlink($tempPath);
    }
}
