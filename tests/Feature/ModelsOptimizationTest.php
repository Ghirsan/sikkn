<?php

namespace Tests\Feature;

use App\Enums\LogStatus;
use App\Enums\PeriodStatus;
use App\Enums\ProgramOutputType;
use App\Enums\ProgramStatus;
use App\Enums\Semester;
use App\Models\DailyLog;
use App\Models\DailyLogActivity;
use App\Models\Faculty;
use App\Models\Grade;
use App\Models\GradeWeight;
use App\Models\Group;
use App\Models\GroupType;
use App\Models\ParticipantOutput;
use App\Models\Period;
use App\Models\Program;
use App\Models\ProgramParticipant;
use App\Models\ProgramType;
use App\Models\SdgCategory;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelsOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_faculty_and_study_program_relations_and_scopes(): void
    {
        $faculty = Faculty::create([
            'code' => 'FT',
            'name' => 'Fakultas Teknik',
            'short_name' => 'FT',
        ]);

        $prodi = StudyProgram::create([
            'code' => '55201',
            'faculty_id' => $faculty->id,
            'name' => 'Teknik Informatika',
        ]);

        $student = User::factory()->mahasiswa()->create([
            'faculty_id' => $faculty->id,
            'study_program_id' => $prodi->id,
            'name' => 'Budi Santoso',
            'nim' => '552012001',
        ]);

        $dpl = User::factory()->dpl()->create([
            'faculty_id' => $faculty->id,
            'study_program_id' => $prodi->id,
            'name' => 'Dr. Bambang',
            'nip' => '19800101',
        ]);

        $this->assertCount(1, $faculty->studyPrograms);
        $this->assertCount(1, $faculty->students);
        $this->assertCount(1, $faculty->dpls);
        $this->assertCount(1, $prodi->students);
        $this->assertCount(1, $prodi->dpls);

        $this->assertTrue(Faculty::search('Teknik')->exists());
        $this->assertTrue(StudyProgram::search('Informatika')->exists());
        $this->assertTrue(StudyProgram::forFaculty($faculty->id)->exists());
    }

    public function test_group_types_program_types_and_sdg_categories(): void
    {
        $groupType = GroupType::firstOrCreate(
            ['code' => 'reguler'],
            ['name' => 'KKN Reguler']
        );
        $this->assertSame('KKN Reguler', $groupType->label());
        $this->assertTrue(GroupType::search('Reguler')->exists());

        $programType = ProgramType::firstOrCreate(
            ['code' => 'multidisiplin'],
            ['name' => 'Multidisiplin']
        );
        $this->assertSame('Multidisiplin', $programType->label());
        $this->assertTrue(ProgramType::search('Multidisiplin')->exists());

        $sdg = SdgCategory::where('code', 1)->first();
        $this->assertNotNull($sdg);
        $this->assertSame(1, $sdg->code);
        $this->assertTrue(str_contains($sdg->label(), 'Poverty'));
        $this->assertTrue(SdgCategory::ordered()->exists());
        $this->assertTrue(SdgCategory::search('Poverty')->exists());
    }

    public function test_period_attributes_and_scopes(): void
    {
        $period = Period::create([
            'name' => 'KKN Ganjil 2026',
            'semester' => Semester::Ganjil,
            'year' => 2026,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(25)->toDateString(),
        ]);

        $this->assertSame('KKN Ganjil 2026 (Ganjil 2026)', $period->display_name);
        $this->assertSame(PeriodStatus::Active, $period->status);
        $this->assertTrue(Period::active()->where('id', $period->id)->exists());
        $this->assertTrue(Period::ordered()->where('id', $period->id)->exists());
    }

    public function test_group_relations_accessors_and_scopes(): void
    {
        $period = Period::create([
            'name' => 'KKN 2026',
            'semester' => Semester::Ganjil,
            'year' => 2026,
            'start_date' => '2026-10-01',
            'end_date' => '2026-11-01',
        ]);

        $groupType = GroupType::firstOrCreate(
            ['code' => 'reguler'],
            ['name' => 'Reguler']
        );

        $dpl = User::factory()->dpl()->create();
        $student = User::factory()->mahasiswa()->create();

        $group = Group::create([
            'period_id' => $period->id,
            'group_type_id' => $groupType->id,
            'name' => 'Tim 01 Desa Sukamaju',
            'village' => 'Sukamaju',
            'district' => 'Cibadak',
            'regency' => 'Sukabumi',
            'province' => 'Jawa Barat',
            'partner_name' => 'Kades Sukamaju',
            'lead_dpl_id' => $dpl->id,
            'student_leader_id' => $student->id,
            'is_lrk_locked' => false,
            'is_lpk_locked' => true,
        ]);

        $student->update(['group_id' => $group->id]);

        $grade = Grade::create([
            'student_id' => $student->id,
            'dpl_id' => $dpl->id,
            'final_grade' => 85.50,
            'grade_letter' => 'A',
        ]);

        $this->assertSame('Sukamaju, Cibadak, Sukabumi, Jawa Barat', $group->location);
        $this->assertSame('2026-10-01', $group->effective_start_date?->toDateString());
        $this->assertSame('2026-11-01', $group->effective_end_date?->toDateString());
        $this->assertTrue($group->isLrkEditable());
        $this->assertFalse($group->isLpkEditable());

        $this->assertCount(1, $group->grades);
        $this->assertSame($grade->id, $group->grades->first()->id);

        $this->assertTrue(Group::forPeriod($period->id)->where('id', $group->id)->exists());
        $this->assertTrue(Group::search('Sukamaju')->where('id', $group->id)->exists());
        $this->assertTrue(Group::lrkLocked(false)->where('id', $group->id)->exists());
        $this->assertTrue(Group::lpkLocked(true)->where('id', $group->id)->exists());
    }

    public function test_daily_log_and_activity_duration_accessors(): void
    {
        $student = User::factory()->mahasiswa()->create();

        $log = DailyLog::create([
            'student_id' => $student->id,
            'date' => '2026-10-15',
            'important_notes' => 'Catatan harian',
            'status' => LogStatus::Approved,
        ]);

        $activity1 = DailyLogActivity::create([
            'daily_log_id' => $log->id,
            'start_time' => '08:00',
            'end_time' => '10:30',
            'activity_description' => 'Sosialisasi warga',
        ]);

        $activity2 = DailyLogActivity::create([
            'daily_log_id' => $log->id,
            'start_time' => '13:00',
            'end_time' => '14:00',
            'activity_description' => 'Evaluasi tim',
        ]);

        $this->assertSame(150, $activity1->duration_minutes);
        $this->assertSame('2 jam 30 menit', $activity1->duration_formatted);
        $this->assertSame('08:00 - 10:30', $activity1->time_range);

        $this->assertSame(60, $activity2->duration_minutes);
        $this->assertSame('1 jam', $activity2->duration_formatted);

        $log->load('activities');
        $this->assertSame(210, $log->total_minutes);
        $this->assertSame('3 jam 30 menit', $log->total_hours_formatted);
        $this->assertTrue($log->isApproved());
        $this->assertFalse($log->isEditable());

        $this->assertTrue(DailyLog::approved()->where('id', $log->id)->exists());
        $this->assertTrue(DailyLog::forStudent($student->id)->where('id', $log->id)->exists());
        $this->assertTrue(DailyLog::betweenDates('2026-10-01', '2026-10-31')->where('id', $log->id)->exists());
    }

    public function test_program_participant_outputs_and_user_scopes(): void
    {
        $groupType = GroupType::firstOrCreate(['code' => 'reguler'], ['name' => 'Reguler']);
        $programType = ProgramType::firstOrCreate(['code' => 'multidisiplin'], ['name' => 'Multidisiplin']);

        $period = Period::create([
            'name' => 'KKN 2026',
            'semester' => Semester::Ganjil,
            'year' => 2026,
            'start_date' => '2026-10-01',
            'end_date' => '2026-11-01',
        ]);

        $group = Group::create([
            'period_id' => $period->id,
            'group_type_id' => $groupType->id,
            'name' => 'Tim 02',
        ]);

        $student = User::factory()->mahasiswa()->create([
            'name' => 'Ahmad Dahlan',
            'email' => 'ahmad@example.com',
            'nim' => '123456789',
            'group_id' => $group->id,
        ]);

        $program = Program::create([
            'group_id' => $group->id,
            'program_type_id' => $programType->id,
            'title' => 'Pembuatan Video Profil Desa',
            'sequence' => 1,
        ]);

        $participant = ProgramParticipant::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'status' => ProgramStatus::Approved,
            'lpk_status' => ProgramStatus::Approved,
            'documentation_image_path' => 'https://example.com/photo.jpg',
        ]);

        $output = ParticipantOutput::create([
            'program_participant_id' => $participant->id,
            'output_code' => 'LM1M'.$student->id,
            'name' => 'Video Profil',
            'type' => ProgramOutputType::Video,
            'url' => 'https://youtube.com/watch?v=123',
        ]);

        $this->assertCount(1, $student->programParticipants);
        $this->assertCount(1, $student->participantOutputs);
        $this->assertCount(1, $student->programOutputs);
        $this->assertSame($output->id, $student->participantOutputs->first()->id);

        $this->assertSame('https://example.com/photo.jpg', $participant->documentation_image_url);

        $this->assertTrue(User::mahasiswa()->where('id', $student->id)->exists());
        $this->assertTrue(User::inGroup($group->id)->where('id', $student->id)->exists());
        $this->assertTrue(User::search('Ahmad')->where('id', $student->id)->exists());
        $this->assertTrue(User::search('123456789')->where('id', $student->id)->exists());
        $this->assertFalse(User::dpl()->where('id', $student->id)->exists());

        $this->assertTrue(Program::multidisiplin()->where('id', $program->id)->exists());
        $this->assertTrue(Program::forGroup($group->id)->where('id', $program->id)->exists());
        $this->assertTrue(ProgramParticipant::approved()->where('id', $participant->id)->exists());
        $this->assertTrue(ProgramParticipant::lpkApproved()->where('id', $participant->id)->exists());
        $this->assertTrue(ParticipantOutput::type(ProgramOutputType::Video)->where('id', $output->id)->exists());
    }

    public function test_grade_weights_runtime_cache_and_flush(): void
    {
        GradeWeight::flushCache();

        GradeWeight::create([
            'name' => 'Aktivitas Partisipatif',
            'code' => 'aktivitas_partisipatif',
            'weight' => 30.00,
        ]);

        $weights1 = GradeWeight::getWeights();
        $this->assertSame(0.30, $weights1['aktivitas_partisipatif']);

        // Check percentages
        $percentages = GradeWeight::getPercentages();
        $this->assertSame(30.0, (float) $percentages['aktivitas_partisipatif']);

        // Cache hit test
        GradeWeight::flushCache();
        $weights2 = GradeWeight::getWeights();
        $this->assertIsArray($weights2);
    }

    public function test_grade_methods_and_scopes(): void
    {
        $dpl = User::factory()->dpl()->create();
        $student = User::factory()->mahasiswa()->create();

        $passingGrade = Grade::create([
            'student_id' => $student->id,
            'dpl_id' => $dpl->id,
            'final_grade' => 78.50,
            'grade_letter' => 'B',
        ]);

        $this->assertTrue($passingGrade->isPassing());
        $this->assertTrue(Grade::passed()->where('id', $passingGrade->id)->exists());
        $this->assertFalse(Grade::failed()->where('id', $passingGrade->id)->exists());
        $this->assertTrue(Grade::forDpl($dpl->id)->where('id', $passingGrade->id)->exists());

        $studentFailed = User::factory()->mahasiswa()->create();
        $failingGrade = Grade::create([
            'student_id' => $studentFailed->id,
            'dpl_id' => $dpl->id,
            'final_grade' => 45.00,
            'grade_letter' => 'E',
        ]);

        $this->assertFalse($failingGrade->isPassing());
        $this->assertTrue(Grade::failed()->where('id', $failingGrade->id)->exists());
    }
}
