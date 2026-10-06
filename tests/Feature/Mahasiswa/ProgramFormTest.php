<?php

namespace Tests\Feature\Mahasiswa;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Enums\Semester;
use App\Livewire\Mahasiswa\ProgramForm;
use App\Models\Group;
use App\Models\Period;
use App\Models\Program;
use App\Models\ProgramParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ProgramFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_lpk_form_resolves_google_drive_thumbnail_for_preview(): void
    {
        [$student, $participant] = $this->createApprovedParticipant();

        Livewire::actingAs($student)
            ->test(ProgramForm::class, ['action' => 'lpk', 'participantId' => $participant->id])
            ->set('documentation_image_url', 'https://drive.google.com/file/d/1qC_adcct3A6cxRYEZ_CfgDESC_U9w0qg/view?usp=sharing')
            ->call('validateDocumentationImage')
            ->assertSet('documentation_image_preview_url', 'https://drive.google.com/thumbnail?id=1qC_adcct3A6cxRYEZ_CfgDESC_U9w0qg')
            ->assertHasNoErrors('documentation_image_url');
    }

    public function test_output_url_is_not_auto_completed(): void
    {
        [$student, $participant] = $this->createApprovedParticipant();

        Livewire::actingAs($student)
            ->test(ProgramForm::class, ['action' => 'lpk', 'participantId' => $participant->id])
            ->set('outputs', [[
                'id' => null,
                'name' => '',
                'type' => 'pdf',
                'url' => 'example.com/report.pdf',
                'metadata' => null,
            ]])
            ->call('inferOutputType', 0)
            ->assertSet('outputs.0.url', 'example.com/report.pdf');
    }

    public function test_output_url_validation_rejects_invalid_url(): void
    {
        [$student, $participant] = $this->createApprovedParticipant();

        Livewire::actingAs($student)
            ->test(ProgramForm::class, ['action' => 'lpk', 'participantId' => $participant->id])
            ->set('outputs', [[
                'id' => null,
                'name' => 'Laporan kegiatan',
                'type' => 'pdf',
                'url' => 'not-a-url',
                'metadata' => null,
            ]])
            ->call('inferOutputType', 0)
            ->assertHasErrors('outputs.0.url')
            ->assertSet('outputs.0.url_valid', false);
    }

    public function test_lpk_form_persists_external_documentation_url(): void
    {
        Http::fake([
            'https://ibb.co.com/0p5rDN0j' => Http::response('<meta property="og:image" content="https://i.ibb.co/FkpzxvQb/sign.png">', 200),
        ]);

        [$student, $participant] = $this->createApprovedParticipant();

        Livewire::actingAs($student)
            ->test(ProgramForm::class, ['action' => 'lpk', 'participantId' => $participant->id])
            ->set('achievement', 'Kegiatan terlaksana')
            ->set('documentation_image_url', 'https://ibb.co.com/0p5rDN0j')
            ->call('validateDocumentationImage')
            ->assertSet('documentation_image_preview_url', 'https://i.ibb.co.com/0p5rDN0j/sign.png')
            ->set('documentation_image_verified', true)
            ->set('documentation_caption', 'Dokumentasi kegiatan')
            ->set('outputs', [[
                'id' => null,
                'name' => 'Laporan kegiatan',
                'type' => 'pdf',
                'url' => 'https://example.com/laporan',
            ]])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('program_participants', [
            'id' => $participant->id,
            'documentation_image_path' => 'https://ibb.co.com/0p5rDN0j',
        ]);

        $this->assertSame(
            'https://i.ibb.co.com/0p5rDN0j/sign.png',
            $participant->refresh()->documentationImageUrl(),
        );
    }

    public function test_lpk_form_rejects_unsupported_documentation_host(): void
    {
        [$student, $participant] = $this->createApprovedParticipant();

        Livewire::actingAs($student)
            ->test(ProgramForm::class, ['action' => 'lpk', 'participantId' => $participant->id])
            ->set('achievement', 'Kegiatan terlaksana')
            ->set('documentation_image_url', 'https://example.com/image.jpg')
            ->set('documentation_caption', 'Dokumentasi kegiatan')
            ->set('outputs', [[
                'id' => null,
                'name' => 'Laporan kegiatan',
                'type' => 'pdf',
                'url' => 'https://example.com/laporan',
            ]])
            ->call('save')
            ->assertHasErrors('documentation_image_url');
    }

    private function createApprovedParticipant(): array
    {
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);
        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok Uji',
        ]);
        $student = User::factory()->mahasiswa()->create([
            'group_id' => $group->id,
        ]);
        $program = Program::create([
            'group_id' => $group->id,
            'student_id' => $student->id,
            'title' => 'Program Dokumentasi',
            'type' => ProgramType::Lainnya,
        ]);
        $participant = ProgramParticipant::create([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'status' => ProgramStatus::Approved,
            'lpk_status' => ProgramStatus::Draft,
        ]);

        return [$student, $participant];
    }
}
