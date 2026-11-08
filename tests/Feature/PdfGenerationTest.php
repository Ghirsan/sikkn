<?php

namespace Tests\Feature;

use App\Enums\LogStatus;
use App\Enums\ProgramStatus;
use App\Enums\Semester;
use App\Models\DailyLog;
use App\Models\Group;
use App\Models\MentoringLog;
use App\Models\Period;
use App\Models\Program;
use App\Models\ProgramParticipant;
use App\Models\ProgramType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfGenerationTest extends TestCase
{
    use RefreshDatabase;

    private function setupTestData(): array
    {
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-06-01',
            'end_date' => '2026-07-31',
        ]);

        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 1 Tembalang',
            'village' => 'Kramas',
            'district' => 'Tembalang',
            'regency' => 'Kota Semarang',
            'province' => 'Jawa Tengah',
            'start_date' => '2026-06-01',
            'end_date' => '2026-07-31',
        ]);

        $dpl = User::factory()->dpl()->create();
        $group->dpls()->attach($dpl->id);
        $group->update(['lead_dpl_id' => $dpl->id]);

        $student = User::factory()->mahasiswa()->create([
            'group_id' => $group->id,
            'nim' => '21120120140001',
        ]);

        $mdType = ProgramType::where('code', 'multidisiplin')->first();
        if (! $mdType) {
            $mdType = ProgramType::create([
                'name' => 'Multidisiplin',
                'code' => 'multidisiplin',
                'is_active' => true,
            ]);
        }

        $program = Program::create([
            'group_id' => $group->id,
            'student_id' => $student->id,
            'title' => 'Digitalisasi Bank Sampah',
            'program_type_id' => $mdType->id,
            'sequence' => 1,
        ]);

        ProgramParticipant::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'participant_code' => 'M1M1',
            'status' => ProgramStatus::Approved,
            'lpk_status' => ProgramStatus::Approved,
            'execution_date' => '2026-06-15',
        ]);

        // Daily Log
        $dailyLog = DailyLog::create([
            'student_id' => $student->id,
            'date' => '2026-06-05',
            'important_notes' => 'Survey awal lokasi bersama perangkat desa.',
        ]);

        $dailyLog->activities()->create([
            'start_time' => '08:00',
            'end_time' => '12:00',
            'activity_description' => 'Koordinasi dengan Kepala Desa Kramas.',
        ]);

        // Mentoring Log
        MentoringLog::create([
            'group_id' => $group->id,
            'student_id' => $student->id,
            'program_id' => $program->id,
            'date' => '2026-06-10',
            'topic' => 'Konsultasi Perencanaan Program Multidisiplin',
            'discussion_summary' => 'Membahas instrumen survei dan target luaran.',
            'dpl_feedback' => 'Lanjutkan koordinasi dengan kader lingkungan setempat.',
            'target_group' => 'Kader Desa',
            'student_count' => 5,
            'output' => 'Modul Pelatihan',
            'status' => LogStatus::Approved,
        ]);

        return [$period, $group, $dpl, $student, $program];
    }

    public function test_guest_cannot_access_pdf_endpoints(): void
    {
        [$period, $group, $dpl, $student] = $this->setupTestData();

        $this->get(route('lrk.pdf', $group))->assertRedirect(route('login'));
        $this->get(route('lpk.pdf', $group))->assertRedirect(route('login'));
        $this->get(route('logbook.pdf', $student))->assertRedirect(route('login'));
        $this->get(route('mentoring-logs.pdf', $student))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_download_lrk_pdf(): void
    {
        [$period, $group, $dpl, $student] = $this->setupTestData();

        $response = $this->actingAs($student)->get(route('lrk.pdf', $group));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('inline', (string) $response->headers->get('content-disposition'));
        $this->assertNotEmpty($response->getContent());
    }

    public function test_authenticated_user_can_download_lpk_pdf(): void
    {
        [$period, $group, $dpl, $student] = $this->setupTestData();

        $response = $this->actingAs($student)->get(route('lpk.pdf', $group));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('inline', (string) $response->headers->get('content-disposition'));
        $this->assertNotEmpty($response->getContent());
    }

    public function test_authenticated_user_can_preview_lpk_pdf(): void
    {
        [$period, $group, $dpl, $student] = $this->setupTestData();

        $response = $this->actingAs($student)->get(route('lpk.preview', $group));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('inline', (string) $response->headers->get('content-disposition'));
        $this->assertNotEmpty($response->getContent());
    }

    public function test_authenticated_user_can_download_logbook_pdf(): void
    {
        [$period, $group, $dpl, $student] = $this->setupTestData();

        $response = $this->actingAs($student)->get(route('logbook.pdf', $student));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('inline', (string) $response->headers->get('content-disposition'));
        $this->assertNotEmpty($response->getContent());
    }

    public function test_authenticated_user_can_download_mentoring_logs_pdf(): void
    {
        [$period, $group, $dpl, $student] = $this->setupTestData();

        $response = $this->actingAs($student)->get(route('mentoring-logs.pdf', $student));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('inline', (string) $response->headers->get('content-disposition'));
        $this->assertNotEmpty($response->getContent());
    }

    public function test_cannot_download_logbook_for_non_student(): void
    {
        [$period, $group, $dpl] = $this->setupTestData();

        $response = $this->actingAs($dpl)->get(route('logbook.pdf', $dpl));
        $response->assertNotFound();
    }

    public function test_cannot_download_mentoring_logs_for_non_student(): void
    {
        [$period, $group, $dpl] = $this->setupTestData();

        $response = $this->actingAs($dpl)->get(route('mentoring-logs.pdf', $dpl));
        $response->assertNotFound();
    }
}
