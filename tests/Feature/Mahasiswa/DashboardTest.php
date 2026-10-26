<?php

namespace Tests\Feature\Mahasiswa;

use App\Enums\LogStatus;
use App\Enums\ProgramStatus;
use App\Enums\Semester;
use App\Livewire\Mahasiswa\Dashboard;
use App\Models\DailyLog;
use App\Models\DailyLogActivity;
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

    public function test_student_without_group_sees_unallocated_notice(): void
    {
        $student = User::factory()->mahasiswa()->create();

        Livewire::actingAs($student)
            ->test(Dashboard::class)
            ->assertSee('Belum Dialokasikan ke Kelompok KKN')
            ->assertDontSee('Daftar Mahasiswa Bimbingan');
    }

    public function test_student_with_group_sees_group_and_period_details(): void
    {
        $period = Period::create([
            'name' => 'KKN Tematik 2026',
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(25)->toDateString(),
        ]);

        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 10 Sukamaju',
            'village' => 'Desa Sukamaju',
            'district' => 'Kecamatan Ceria',
            'regency' => 'Kabupaten Makmur',
        ]);

        $dpl = User::factory()->dpl()->create([
            'name' => 'Dr. Budi Santoso, M.Kom',
            'nip' => '198001012005011001',
            'phone' => '081234567890',
        ]);
        $group->dpls()->attach($dpl->id);

        $student = User::factory()->mahasiswa()->create([
            'group_id' => $group->id,
        ]);

        Livewire::actingAs($student)
            ->test(Dashboard::class)
            ->assertSee('Kelompok 10 Sukamaju')
            ->assertSee('Desa Sukamaju')
            ->assertSee('Dr. Budi Santoso, M.Kom')
            ->assertSee('Program Kerja')
            ->assertSee('Status Dokumen')
            ->assertSee('Logbook Terisi')
            ->assertSee('Buku Bimbingan');
    }

    public function test_student_sees_revision_alert_when_program_needs_revision(): void
    {
        $period = Period::create([
            'name' => 'KKN Tematik 2026',
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => now()->subDays(2)->toDateString(),
            'end_date' => now()->addDays(28)->toDateString(),
        ]);

        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 12 Harapan',
            'village' => 'Desa Harapan',
            'district' => 'Kecamatan Makmur',
            'regency' => 'Kabupaten Damai',
        ]);

        $student = User::factory()->mahasiswa()->create([
            'group_id' => $group->id,
        ]);

        $programType = ProgramType::firstOrCreate(
            ['code' => 'keilmuan'],
            ['name' => 'Keilmuan']
        );

        $program = Program::create([
            'group_id' => $group->id,
            'student_id' => $student->id,
            'title' => 'Pelatihan Literasi Keuangan Desa',
            'program_type_id' => $programType->id,
        ]);

        ProgramParticipant::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'participant_title' => 'Pelatihan Literasi Keuangan Desa',
            'status' => ProgramStatus::NeedsRevision,
            'revision_note' => 'Mohon perjelas target sasaran warga',
        ]);

        Livewire::actingAs($student)
            ->test(Dashboard::class)
            ->assertSee('Terdapat 1 Program Kerja Memerlukan Revisi')
            ->assertSee('Pelatihan Literasi Keuangan Desa')
            ->assertSee('Mohon perjelas target sasaran warga');
    }

    public function test_student_calculates_approved_logbook_hours_accurately(): void
    {
        $period = Period::create([
            'name' => 'KKN Tematik 2026',
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(25)->toDateString(),
        ]);

        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 05 Mandiri',
            'village' => 'Desa Mandiri',
            'district' => 'Kecamatan Berkah',
            'regency' => 'Kabupaten Rukun',
        ]);

        $student = User::factory()->mahasiswa()->create([
            'group_id' => $group->id,
        ]);

        // Create an approved daily log with 2 activities (120 min + 60 min = 3 hours)
        $approvedLog = DailyLog::create([
            'student_id' => $student->id,
            'date' => now()->subDay()->toDateString(),
            'status' => LogStatus::Approved,
        ]);

        DailyLogActivity::create([
            'daily_log_id' => $approvedLog->id,
            'start_time' => '08:00',
            'end_time' => '10:00', // 120 minutes
            'activity_description' => 'Sosialisasi program kerja ke warga',
        ]);

        DailyLogActivity::create([
            'daily_log_id' => $approvedLog->id,
            'start_time' => '13:00',
            'end_time' => '14:00', // 60 minutes
            'activity_description' => 'Evaluasi tim',
        ]);

        // Create a pending daily log (should not add to approved hours)
        $pendingLog = DailyLog::create([
            'student_id' => $student->id,
            'date' => now()->toDateString(),
            'status' => LogStatus::Pending,
        ]);

        DailyLogActivity::create([
            'daily_log_id' => $pendingLog->id,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'activity_description' => 'Persiapan materi',
        ]);

        Livewire::actingAs($student)
            ->test(Dashboard::class)
            ->assertSee('2 Hari') // Total logs
            ->assertSee('1 Disetujui') // Approved count
            ->assertSee('3 jam'); // Approved hours
    }
}
