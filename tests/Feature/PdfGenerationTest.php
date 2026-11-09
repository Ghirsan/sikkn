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
use Illuminate\Support\Facades\URL;
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

        $group->update([
            'storyboard_scenes' => [
                ['scene' => 'Scene 1', 'image_url' => '', 'visual' => 'Opening lanskap persawahan desa'],
                ['scene' => 'Scene 2', 'image_url' => '', 'visual' => 'Wawancara dengan perangkat desa'],
            ],
            'video_script_scenes' => [
                ['scene' => 'Scene 1', 'visual' => 'Lanskap desa pagi hari', 'audio' => 'Selamat datang di Desa Sukamaju', 'duration' => '15s'],
                ['scene' => 'Scene 2', 'visual' => 'Warga memanen padi', 'audio' => 'Potensi pertanian menjadi tulang punggung', 'duration' => '20s'],
            ],
        ]);

        $response = $this->actingAs($student)->get(URL::signedRoute('lrk.pdf', $group));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('inline', (string) $response->headers->get('content-disposition'));
        $content = (string) $response->getContent();
        $this->assertNotEmpty($content);
        $this->assertStringContainsString('/Subtype /Link', $content);
        $this->assertStringContainsString('/S /GoTo', $content);
        preg_match_all('/\/Rect\s*\[([^\]]+)\]/', $content, $matches);
        $this->assertNotEmpty($matches[1]);
        $firstCoords = array_map('floatval', preg_split('/\s+/', trim($matches[1][0])));
        $this->assertGreaterThan(700, $firstCoords[3], 'First TOC link annotation Y coordinate must align with the top of portrait page');
    }

    public function test_authenticated_user_can_download_lpk_pdf(): void
    {
        [$period, $group, $dpl, $student] = $this->setupTestData();

        $response = $this->actingAs($student)->get(URL::signedRoute('lpk.pdf', $group));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('inline', (string) $response->headers->get('content-disposition'));
        $content = (string) $response->getContent();
        $this->assertNotEmpty($content);
        $this->assertStringContainsString('/Subtype /Link', $content);
        $this->assertStringContainsString('/S /GoTo', $content);
        preg_match_all('/\/Rect\s*\[([^\]]+)\]/', $content, $matches);
        $this->assertNotEmpty($matches[1]);
        $firstCoords = array_map('floatval', preg_split('/\s+/', trim($matches[1][0])));
        $this->assertGreaterThan(700, $firstCoords[3], 'First TOC link annotation Y coordinate must align with the top of portrait page');
    }

    public function test_authenticated_user_can_preview_lpk_pdf(): void
    {
        [$period, $group, $dpl, $student] = $this->setupTestData();

        $response = $this->actingAs($student)->get(URL::signedRoute('lpk.preview', $group));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('inline', (string) $response->headers->get('content-disposition'));
        $content = (string) $response->getContent();
        $this->assertNotEmpty($content);
        $this->assertStringContainsString('/Subtype /Link', $content);
        $this->assertStringContainsString('/S /GoTo', $content);
        preg_match_all('/\/Rect\s*\[([^\]]+)\]/', $content, $matches);
        $this->assertNotEmpty($matches[1]);
        $firstCoords = array_map('floatval', preg_split('/\s+/', trim($matches[1][0])));
        $this->assertGreaterThan(700, $firstCoords[3], 'First TOC link annotation Y coordinate must align with the top of portrait page');
    }

    public function test_authenticated_user_can_download_logbook_pdf(): void
    {
        [$period, $group, $dpl, $student] = $this->setupTestData();

        $response = $this->actingAs($student)->get(URL::signedRoute('logbook.pdf', $student));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('inline', (string) $response->headers->get('content-disposition'));
        $this->assertNotEmpty($response->getContent());
    }

    public function test_authenticated_user_can_download_mentoring_logs_pdf(): void
    {
        [$period, $group, $dpl, $student] = $this->setupTestData();

        $response = $this->actingAs($student)->get(URL::signedRoute('mentoring-logs.pdf', $student));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('inline', (string) $response->headers->get('content-disposition'));
        $this->assertNotEmpty($response->getContent());
    }

    public function test_authenticated_user_can_download_lrk_pdf_with_multiple_grouped_sosmas_and_lainnya(): void
    {
        [$period, $group, $dpl, $student] = $this->setupTestData();

        $sosmasType = ProgramType::firstOrCreate(
            ['code' => 'sosial_kemasyarakatan'],
            ['name' => 'Sosial Kemasyarakatan', 'is_active' => true]
        );
        $lainnyaType = ProgramType::firstOrCreate(
            ['code' => 'lainnya'],
            ['name' => 'Lainnya', 'is_active' => true]
        );

        $studentSaintekMember = User::factory()->mahasiswa()->create([
            'group_id' => $group->id,
            'nim' => '21120120140002',
            'name' => 'Saintek Member',
        ]);
        $studentSoshumInitiator = User::factory()->mahasiswa()->create([
            'group_id' => $group->id,
            'nim' => '11000120140001',
            'name' => 'Soshum Initiator',
        ]);
        $studentLainnyaInitiator = User::factory()->mahasiswa()->create([
            'group_id' => $group->id,
            'nim' => '21120120140003',
            'name' => 'Lainnya Initiator',
        ]);

        // Sosmas 1 (Saintek) - Initiator: $student, Member: $studentSaintekMember
        $sosmasProg1 = Program::create([
            'group_id' => $group->id,
            'student_id' => $student->id,
            'title' => 'Edukasi Pengolahan Sampah Organik',
            'program_type_id' => $sosmasType->id,
        ]);
        ProgramParticipant::create([
            'program_id' => $sosmasProg1->id,
            'student_id' => $student->id,
            'participant_code' => 'S1P1',
            'status' => ProgramStatus::Approved,
            'lpk_status' => ProgramStatus::Approved,
            'role_in_program' => 'Penggagas',
            'responsibility' => 'Perencanaan dan materi',
        ]);
        ProgramParticipant::create([
            'program_id' => $sosmasProg1->id,
            'student_id' => $studentSaintekMember->id,
            'participant_code' => 'S1P2',
            'status' => ProgramStatus::Approved,
            'lpk_status' => ProgramStatus::Approved,
            'role_in_program' => 'Anggota',
            'responsibility' => 'Logistik dan demonstrasi',
        ]);

        // Sosmas 2 (Soshum) - Initiator: $studentSoshumInitiator
        $sosmasProg2 = Program::create([
            'group_id' => $group->id,
            'student_id' => $studentSoshumInitiator->id,
            'title' => 'Penyuluhan Hukum Pertanahan Warga',
            'program_type_id' => $sosmasType->id,
        ]);
        ProgramParticipant::create([
            'program_id' => $sosmasProg2->id,
            'student_id' => $studentSoshumInitiator->id,
            'participant_code' => 'S2P1',
            'status' => ProgramStatus::Approved,
            'lpk_status' => ProgramStatus::Approved,
            'role_in_program' => 'Penggagas',
            'responsibility' => 'Penyusunan materi dan pemaparan',
        ]);

        // Lainnya 1 - Initiator: $studentLainnyaInitiator, Member: $student
        $lainnyaProg1 = Program::create([
            'group_id' => $group->id,
            'student_id' => $studentLainnyaInitiator->id,
            'title' => 'Bakti Sosial Kerja Bakti Lingkungan',
            'program_type_id' => $lainnyaType->id,
        ]);
        ProgramParticipant::create([
            'program_id' => $lainnyaProg1->id,
            'student_id' => $studentLainnyaInitiator->id,
            'participant_code' => 'L1P1',
            'status' => ProgramStatus::Approved,
            'lpk_status' => ProgramStatus::Approved,
            'role_in_program' => 'Penggagas',
            'responsibility' => 'Koordinasi warga',
        ]);
        ProgramParticipant::create([
            'program_id' => $lainnyaProg1->id,
            'student_id' => $student->id,
            'participant_code' => 'L1P2',
            'status' => ProgramStatus::Approved,
            'lpk_status' => ProgramStatus::Approved,
            'role_in_program' => 'Anggota',
            'responsibility' => 'Bantuan teknis lapangan',
        ]);

        $group->update([
            'storyboard_scenes' => [
                ['scene' => 'Scene 1', 'image_url' => '', 'visual' => 'Opening lanskap persawahan desa'],
                ['scene' => 'Scene 2', 'image_url' => '', 'visual' => 'Wawancara dengan perangkat desa'],
            ],
            'video_script_scenes' => [
                ['scene' => 'Scene 1', 'visual' => 'Lanskap desa pagi hari', 'audio' => 'Selamat datang di Desa Sukamaju', 'duration' => '15s'],
                ['scene' => 'Scene 2', 'visual' => 'Warga memanen padi', 'audio' => 'Potensi pertanian menjadi tulang punggung', 'duration' => '20s'],
            ],
        ]);

        $response = $this->actingAs($student)->get(URL::signedRoute('lrk.pdf', $group));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertNotEmpty($response->getContent());
    }

    public function test_authenticated_user_can_download_lpk_pdf_with_multiple_grouped_sosmas_and_lainnya(): void
    {
        [$period, $group, $dpl, $student] = $this->setupTestData();

        $sosmasType = ProgramType::firstOrCreate(
            ['code' => 'sosial_kemasyarakatan'],
            ['name' => 'Sosial Kemasyarakatan', 'is_active' => true]
        );
        $lainnyaType = ProgramType::firstOrCreate(
            ['code' => 'lainnya'],
            ['name' => 'Lainnya', 'is_active' => true]
        );

        $studentSaintekMember = User::factory()->mahasiswa()->create([
            'group_id' => $group->id,
            'nim' => '21120120140002',
            'name' => 'Saintek Member',
        ]);

        $sosmasProg1 = Program::create([
            'group_id' => $group->id,
            'student_id' => $student->id,
            'title' => 'Edukasi Pengolahan Sampah Organik',
            'program_type_id' => $sosmasType->id,
        ]);
        ProgramParticipant::create([
            'program_id' => $sosmasProg1->id,
            'student_id' => $student->id,
            'participant_code' => 'S1P1',
            'status' => ProgramStatus::Approved,
            'lpk_status' => ProgramStatus::Approved,
            'achievement' => '100% warga RT 01 memahami pemilahan sampah organik.',
        ]);
        ProgramParticipant::create([
            'program_id' => $sosmasProg1->id,
            'student_id' => $studentSaintekMember->id,
            'participant_code' => 'S1P2',
            'status' => ProgramStatus::Approved,
            'lpk_status' => ProgramStatus::Approved,
            'achievement' => 'Membantu pembuatan 10 unit komposter skala rumah tangga.',
        ]);

        $response = $this->actingAs($student)->get(URL::signedRoute('lpk.pdf', $group));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertNotEmpty($response->getContent());
    }

    public function test_cannot_download_logbook_for_non_student(): void
    {
        [$period, $group, $dpl] = $this->setupTestData();

        $response = $this->actingAs($dpl)->get(URL::signedRoute('logbook.pdf', $dpl));
        $response->assertNotFound();
    }

    public function test_cannot_download_mentoring_logs_for_non_student(): void
    {
        [$period, $group, $dpl] = $this->setupTestData();

        $response = $this->actingAs($dpl)->get(URL::signedRoute('mentoring-logs.pdf', $dpl));
        $response->assertNotFound();
    }

    public function test_lrk_pdf_renders_correctly_with_long_legend_names_and_titles(): void
    {
        [$period, $group, $dpl, $student] = $this->setupTestData();

        $longNameStudent = User::factory()->mahasiswa()->create([
            'group_id' => $group->id,
            'name' => 'Raden Mas Muhammad Fakhri Abdurrahman Kusumaningrat Danubroto',
        ]);

        $mdType = ProgramType::firstOrCreate(
            ['code' => 'multidisiplin'],
            ['name' => 'Multidisiplin', 'is_active' => true]
        );

        $prog = Program::create([
            'group_id' => $group->id,
            'student_id' => $longNameStudent->id,
            'title' => 'Optimalisasi Digitalisasi dan Tata Kelola Sampah Berbasis Aplikasi Mobile serta Pemanfaatan Energi Terbarukan untuk Masyarakat Desa',
            'program_type_id' => $mdType->id,
        ]);

        ProgramParticipant::create([
            'program_id' => $prog->id,
            'student_id' => $longNameStudent->id,
            'participant_code' => 'M1-RMFA',
            'status' => ProgramStatus::Approved,
            'role_in_program' => 'Koordinator Lapangan',
            'responsibility' => 'Perencanaan dan pelaksanaan menyeluruh',
        ]);

        $group->update([
            'storyboard_scenes' => [
                ['scene' => 'Scene 1', 'image_url' => '', 'visual' => 'Opening lanskap persawahan desa'],
                ['scene' => 'Scene 2', 'image_url' => '', 'visual' => 'Wawancara dengan perangkat desa'],
            ],
            'video_script_scenes' => [
                ['scene' => 'Scene 1', 'visual' => 'Lanskap desa pagi hari', 'audio' => 'Selamat datang di Desa Sukamaju', 'duration' => '15s'],
                ['scene' => 'Scene 2', 'visual' => 'Warga memanen padi', 'audio' => 'Potensi pertanian menjadi tulang punggung', 'duration' => '20s'],
            ],
        ]);

        $response = $this->actingAs($student)->get(URL::signedRoute('lrk.pdf', $group));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertNotEmpty($response->getContent());
    }
}
