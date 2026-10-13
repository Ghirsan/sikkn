<?php

namespace Tests\Feature\Mahasiswa;

use App\Enums\LogStatus;
use App\Enums\ProgramStatus;
use App\Enums\Semester;
use App\Livewire\Dpl\MentoringBook;
use App\Livewire\Mahasiswa\LpkDocuments;
use App\Livewire\Mahasiswa\LrkDocuments;
use App\Livewire\Mahasiswa\MentoringLogForm;
use App\Livewire\Mahasiswa\MentoringLogs;
use App\Models\Group;
use App\Models\MentoringLog;
use App\Models\Period;
use App\Models\Program;
use App\Models\ProgramParticipant;
use App\Models\ProgramType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MentoringLogsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_open_mentoring_log_form(): void
    {
        [$student, $group, $program] = $this->createStudentWithGroupAndProgram();

        Livewire::actingAs($student)
            ->test(MentoringLogForm::class)
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertSee($program->title);

        $response = $this->actingAs($student)->get(route('mentoring-logs.form'));
        $response->assertStatus(200);
    }

    public function test_student_can_save_mentoring_log_with_associated_program(): void
    {
        [$student, $group, $program] = $this->createStudentWithGroupAndProgram();

        Livewire::actingAs($student)
            ->test(MentoringLogForm::class)
            ->set('date', '2026-06-10')
            ->set('topic', 'Diskusi Program Kerja KKN')
            ->set('discussion_summary', 'Membahas pembagian tugas dan kebutuhan alat peraga.')
            ->set('program_id', $program->id)
            ->set('target_group', 'Masyarakat Desa')
            ->set('student_count', 1)
            ->set('output', 'Notulensi Rapat')
            ->call('saveLog')
            ->assertHasNoErrors()
            ->assertRedirect(route('mentoring-logs.index'));

        $this->assertDatabaseHas('mentoring_logs', [
            'student_id' => $student->id,
            'group_id' => $group->id,
            'program_id' => $program->id,
            'topic' => 'Diskusi Program Kerja KKN',
            'status' => LogStatus::Draft,
        ]);
    }

    public function test_student_can_view_and_submit_mentoring_log(): void
    {
        [$student, $group, $program] = $this->createStudentWithGroupAndProgram();

        $log = MentoringLog::create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'program_id' => $program->id,
            'date' => '2026-06-11',
            'topic' => 'Evaluasi Minggu Pertama',
            'discussion_summary' => 'Kegiatan awal berjalan sesuai timeline.',
            'status' => LogStatus::Draft,
        ]);

        Livewire::actingAs($student)
            ->test(MentoringLogs::class)
            ->assertStatus(200)
            ->assertSee($log->topic)
            ->call('viewLog', $log->id)
            ->assertSet('viewLogData.id', $log->id)
            ->call('submitLog', $log->id);

        $this->assertSame(LogStatus::Pending, $log->fresh()->status);
    }

    public function test_dpl_can_view_mentoring_logs_with_program_type(): void
    {
        [$student, $group, $program, $dpl] = $this->createStudentWithGroupAndProgram(includeDpl: true);

        MentoringLog::create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'program_id' => $program->id,
            'date' => '2026-06-12',
            'topic' => 'Konsultasi Modul Pelatihan',
            'discussion_summary' => 'Dosen memberikan masukan untuk materi presentasi.',
            'status' => LogStatus::Pending,
        ]);

        Livewire::actingAs($dpl)
            ->test(MentoringBook::class)
            ->assertStatus(200)
            ->assertSee('Konsultasi Modul Pelatihan')
            ->assertSee($program->title);
    }

    public function test_student_can_view_lrk_and_lpk_documents_with_program_type_filter(): void
    {
        [$student, $group, $program] = $this->createStudentWithGroupAndProgram();

        Livewire::actingAs($student)
            ->test(LrkDocuments::class)
            ->set('filterType', 'multidisiplin')
            ->assertStatus(200)
            ->assertHasNoErrors();

        Livewire::actingAs($student)
            ->test(LpkDocuments::class)
            ->set('filterType', 'multidisiplin')
            ->assertStatus(200)
            ->assertHasNoErrors();
    }

    private function createStudentWithGroupAndProgram(bool $includeDpl = false): array
    {
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);

        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok Mentoring',
            'start_date' => '2026-06-01',
            'end_date' => '2026-07-31',
        ]);

        $student = User::factory()->mahasiswa()->create([
            'group_id' => $group->id,
        ]);

        $type = ProgramType::where('code', 'multidisiplin')->first();
        $program = Program::create([
            'group_id' => $group->id,
            'student_id' => $student->id,
            'title' => 'Program Literasi Inovatif',
            'program_type_id' => $type?->id,
            'sequence' => 1,
        ]);

        ProgramParticipant::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'participant_code' => 'M1M1',
            'status' => ProgramStatus::Draft,
            'lpk_status' => ProgramStatus::Draft,
        ]);

        $dpl = null;
        if ($includeDpl) {
            $dpl = User::factory()->dpl()->create();
            $group->dpls()->attach($dpl->id);
        }

        return [$student, $group, $program, $dpl];
    }
}
