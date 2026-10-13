<?php

namespace Tests\Feature\Mahasiswa;

use App\Enums\ProgramStatus;
use App\Enums\Semester;
use App\Livewire\Mahasiswa\ProgramForm;
use App\Models\Group;
use App\Models\Period;
use App\Models\Program;
use App\Models\ProgramParticipant;
use App\Models\ProgramType;
use App\Models\SdgCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ProgramFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_open_create_form_for_all_types(): void
    {
        [$student, $participant] = $this->createApprovedParticipant();

        // Type: multidisiplin
        Livewire::actingAs($student)
            ->test(ProgramForm::class, ['action' => 'create', 'type' => 'multidisiplin'])
            ->assertSet('formMode', 'create_multidisiplin')
            ->assertSet('type', 'multidisiplin')
            ->assertHasNoErrors();

        // Type: sosial_kemasyarakatan
        Livewire::actingAs($student)
            ->test(ProgramForm::class, ['action' => 'create', 'type' => 'sosial_kemasyarakatan'])
            ->assertSet('formMode', 'create_individual')
            ->assertSet('type', 'sosial_kemasyarakatan')
            ->assertHasNoErrors();

        // Type: lainnya
        Livewire::actingAs($student)
            ->test(ProgramForm::class, ['action' => 'create', 'type' => 'lainnya'])
            ->assertSet('formMode', 'create_individual')
            ->assertSet('type', 'lainnya')
            ->assertHasNoErrors();

        // Default type
        Livewire::actingAs($student)
            ->test(ProgramForm::class, ['action' => 'create'])
            ->assertSet('formMode', 'create_individual')
            ->assertSet('type', 'lainnya')
            ->assertHasNoErrors();
    }

    public function test_student_can_create_individual_program_with_sdg_category(): void
    {
        [$student, $participant] = $this->createApprovedParticipant();

        $sdg = SdgCategory::first();

        Livewire::actingAs($student)
            ->test(ProgramForm::class, ['action' => 'create', 'type' => 'sosial_kemasyarakatan'])
            ->set('title', 'Bakti Sosial Lingkungan')
            ->set('execution_date', '2026-06-01')
            ->set('role_in_program', 'Koordinator Lapangan')
            ->set('responsibility', 'Mengatur logistik dan koordinasi warga')
            ->set('sdg_category', (string) $sdg->code)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('programs.index'));

        $this->assertDatabaseHas('programs', [
            'group_id' => $student->group_id,
            'student_id' => $student->id,
            'title' => 'Bakti Sosial Lingkungan',
            'program_type_id' => ProgramType::where('code', 'sosial_kemasyarakatan')->value('id'),
        ]);

        $createdProgram = Program::where('title', 'Bakti Sosial Lingkungan')->first();
        $this->assertNotNull($createdProgram);
        $this->assertDatabaseHas('program_participants', [
            'program_id' => $createdProgram->id,
            'student_id' => $student->id,
            'sdg_category_id' => $sdg->id,
        ]);
    }

    public function test_student_can_join_multidisiplin_theme_with_sdg_category(): void
    {
        [$student, $participant] = $this->createApprovedParticipant();

        $theme = Program::create([
            'group_id' => $student->group_id,
            'student_id' => null,
            'title' => 'Pemberdayaan UMKM Desa',
            'program_type_id' => ProgramType::where('code', 'multidisiplin')->value('id'),
            'sequence' => 1,
        ]);

        $sdg = SdgCategory::where('code', 8)->first() ?? SdgCategory::first();

        Livewire::actingAs($student)
            ->test(ProgramForm::class, ['action' => 'create', 'type' => 'multidisiplin'])
            ->set('programId', (string) $theme->id)
            ->assertSet('formMode', 'edit_program')
            ->set('participant_title', 'Pelatihan Digital Marketing')
            ->set('problem_potential', 'Kurangnya pemahaman promosi online')
            ->set('location', 'Balai Desa')
            ->set('method', 'Workshop dan pendampingan')
            ->set('target_audience', 'Pelaku UMKM')
            ->set('output_target', '10 UMKM memiliki akun media sosial bisnis')
            ->set('execution_date', '2026-06-15')
            ->set('sdg_category', (string) $sdg->code)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('programs.index'));

        $this->assertDatabaseHas('program_participants', [
            'program_id' => $theme->id,
            'student_id' => $student->id,
            'participant_title' => 'Pelatihan Digital Marketing',
            'sdg_category_id' => $sdg->id,
        ]);
    }

    public function test_student_can_edit_program_and_sdg_category_is_preserved(): void
    {
        [$student, $participant] = $this->createApprovedParticipant();

        $sdg = SdgCategory::where('code', 4)->first() ?? SdgCategory::first();
        $participant->update([
            'sdg_category_id' => $sdg->id,
            'role_in_program' => 'Anggota Tim',
            'responsibility' => 'Tugas dokumentasi',
            'execution_date' => '2026-06-20',
        ]);

        Livewire::actingAs($student)
            ->test(ProgramForm::class, [
                'action' => 'edit',
                'programId' => $participant->program_id,
                'participantId' => $participant->id,
            ])
            ->assertSet('sdg_category', (string) $sdg->code)
            ->set('title', 'Program Dokumentasi Diperbarui')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('program_participants', [
            'id' => $participant->id,
            'sdg_category_id' => $sdg->id,
        ]);
    }

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
            'program_type_id' => ProgramType::where('code', 'lainnya')->first()->id,
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
