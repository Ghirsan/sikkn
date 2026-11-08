<?php

namespace Tests\Feature\Mahasiswa;

use App\Enums\ProgramStatus;
use App\Enums\Semester;
use App\Livewire\Mahasiswa\LpkDocuments;
use App\Livewire\Mahasiswa\LrkDocuments;
use App\Livewire\Mahasiswa\LrkForm;
use App\Models\Group;
use App\Models\Period;
use App\Models\Program;
use App\Models\ProgramParticipant;
use App\Models\ProgramType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class LrkFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_student_leader_can_access_lrk_form(): void
    {
        [$period, $group, $leader, $member] = $this->createGroupWithLeaderAndMember();

        // Member cannot access
        Livewire::actingAs($member)
            ->test(LrkForm::class)
            ->assertStatus(403);

        // Leader can access
        Livewire::actingAs($leader)
            ->test(LrkForm::class)
            ->assertStatus(200)
            ->assertSet('background', $group->background ?? '');
    }

    public function test_lrk_form_loads_existing_external_urls(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();
        $group->update([
            'location_map_path' => 'https://drive.google.com/file/d/1mapFileId12345/view?usp=sharing',
            'survey_document_path' => 'https://drive.google.com/file/d/1surveyFileId12345/view?usp=sharing',
        ]);

        Livewire::actingAs($leader)
            ->test(LrkForm::class)
            ->assertSet('location_map_url', 'https://drive.google.com/file/d/1mapFileId12345/view?usp=sharing')
            ->assertSet('location_map_preview_url', 'https://drive.google.com/thumbnail?id=1mapFileId12345')
            ->assertSet('survey_document_url', 'https://drive.google.com/file/d/1surveyFileId12345/view?usp=sharing')
            ->assertSet('survey_document_preview_url', 'https://drive.google.com/thumbnail?id=1surveyFileId12345');
    }

    public function test_lrk_form_loads_existing_local_paths(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();
        $group->update([
            'location_map_path' => 'lrk/maps/map.png',
            'survey_document_path' => 'lrk/surveys/survey.png',
        ]);

        Livewire::actingAs($leader)
            ->test(LrkForm::class)
            ->assertSet('location_map_url', '')
            ->assertSet('location_map_preview_url', asset('storage/lrk/maps/map.png'))
            ->assertSet('survey_document_url', '')
            ->assertSet('survey_document_preview_url', asset('storage/lrk/surveys/survey.png'));
    }

    public function test_lrk_form_validates_google_drive_links(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();

        Livewire::actingAs($leader)
            ->test(LrkForm::class)
            ->set('location_map_url', 'https://drive.google.com/file/d/1qC_adcct3A6cxRYEZ_CfgDESC_U9w0qg/view?usp=sharing')
            ->call('validateLocationMap')
            ->assertSet('location_map_preview_url', 'https://drive.google.com/thumbnail?id=1qC_adcct3A6cxRYEZ_CfgDESC_U9w0qg')
            ->assertHasNoErrors('location_map_url')
            ->set('survey_document_url', 'https://drive.google.com/file/d/1surveyFileSample98765/view')
            ->call('validateSurveyDocument')
            ->assertSet('survey_document_preview_url', 'https://drive.google.com/thumbnail?id=1surveyFileSample98765')
            ->assertHasNoErrors('survey_document_url');
    }

    public function test_lrk_form_validates_imgbb_links(): void
    {
        Http::fake([
            'https://ibb.co.com/0p5rDN0j' => Http::response('<meta property="og:image" content="https://i.ibb.co/FkpzxvQb/sign.png">', 200),
        ]);

        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();

        Livewire::actingAs($leader)
            ->test(LrkForm::class)
            ->set('location_map_url', 'https://ibb.co.com/0p5rDN0j')
            ->call('validateLocationMap')
            ->assertSet('location_map_preview_url', 'https://i.ibb.co.com/0p5rDN0j/sign.png')
            ->assertHasNoErrors('location_map_url');
    }

    public function test_lrk_form_rejects_unsupported_link_hosts(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();

        Livewire::actingAs($leader)
            ->test(LrkForm::class)
            ->set('location_map_url', 'https://example.com/peta.jpg')
            ->call('validateLocationMap')
            ->assertHasErrors('location_map_url')
            ->set('survey_document_url', 'https://not-a-valid-url')
            ->call('validateSurveyDocument')
            ->assertHasErrors('survey_document_url');
    }

    public function test_lrk_form_persists_link_attachments_and_narratives(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();

        Livewire::actingAs($leader)
            ->test(LrkForm::class)
            ->set('background', 'Latar belakang KKN Desa Sukamaju.')
            ->set('program_multidisiplin_text', 'Program multidisiplin tema pencegahan stunting.')
            ->set('program_sosmas_text', 'Program sosial kemasyarakatan kerja bakti.')
            ->set('program_lainnya_text', 'Program peringatan hari kemerdekaan.')
            ->set('storyboard_text', 'Scene 1: Balai Desa.')
            ->set('video_script_text', 'Opening: Selamat datang.')
            ->set('survey_documentation_text', 'Survey lapangan dilakukan bersama perangkat desa.')
            ->set('location_map_text', 'Peta batas desa dan posko KKN.')
            ->set('location_map_url', 'https://drive.google.com/file/d/1mapSavedId12345/view?usp=sharing')
            ->set('survey_document_url', 'https://drive.google.com/file/d/1surveySavedId12345/view?usp=sharing')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'background' => 'Latar belakang KKN Desa Sukamaju.',
            'location_map_path' => 'https://drive.google.com/file/d/1mapSavedId12345/view?usp=sharing',
            'survey_document_path' => 'https://drive.google.com/file/d/1surveySavedId12345/view?usp=sharing',
        ]);
    }

    public function test_group_model_url_helpers_resolve_external_and_local_urls(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();

        // 1. External URL
        $group->update([
            'location_map_path' => 'https://drive.google.com/file/d/1sampleGroupMap/view',
            'survey_document_path' => 'https://drive.google.com/file/d/1sampleGroupSurvey/view',
        ]);

        $this->assertSame(
            'https://drive.google.com/thumbnail?id=1sampleGroupMap',
            $group->refresh()->locationMapUrl()
        );
        $this->assertSame(
            'https://drive.google.com/thumbnail?id=1sampleGroupSurvey',
            $group->surveyDocumentUrl()
        );

        // 2. Local storage path
        $group->update([
            'location_map_path' => 'lrk/maps/local-map.jpg',
            'survey_document_path' => 'lrk/surveys/local-survey.jpg',
        ]);

        $this->assertSame(
            asset('storage/lrk/maps/local-map.jpg'),
            $group->refresh()->locationMapUrl()
        );
        $this->assertSame(
            asset('storage/lrk/surveys/local-survey.jpg'),
            $group->surveyDocumentUrl()
        );

        // 3. Null path
        $group->update([
            'location_map_path' => null,
            'survey_document_path' => null,
        ]);

        $this->assertNull($group->refresh()->locationMapUrl());
        $this->assertNull($group->surveyDocumentUrl());
    }

    public function test_lrk_form_preview_action_persists_data_and_sets_preview_url(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();

        Livewire::actingAs($leader)
            ->test(LrkForm::class)
            ->set('background', 'Latar belakang diperbarui untuk pratinjau.')
            ->set('storyboard_text', 'Storyboard scene baru.')
            ->call('preview')
            ->assertHasNoErrors()
            ->assertSet('previewUrl', function ($url) use ($group) {
                return ! empty($url) && str_contains($url, route('lrk.preview', $group));
            });

        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'background' => 'Latar belakang diperbarui untuk pratinjau.',
            'storyboard_text' => 'Storyboard scene baru.',
        ]);
    }

    public function test_lrk_preview_endpoint_returns_inline_pdf_disposition(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();

        $response = $this->actingAs($leader)->get(route('lrk.preview', $group));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('inline', (string) $response->headers->get('content-disposition'));
    }

    public function test_lrk_pdf_endpoint_defaults_to_inline_and_supports_download_query_parameter(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();

        // Default: inline preview
        $previewResponse = $this->actingAs($leader)->get(route('lrk.pdf', $group));
        $previewResponse->assertOk();
        $this->assertStringContainsString('inline', (string) $previewResponse->headers->get('content-disposition'));

        // With download=1: attachment
        $downloadResponse = $this->actingAs($leader)->get(route('lrk.pdf', [$group, 'download' => 1]));
        $downloadResponse->assertOk();
        $this->assertStringContainsString('attachment', (string) $downloadResponse->headers->get('content-disposition'));
    }

    public function test_lrk_form_cannot_finalize_when_document_not_ready(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();

        Livewire::actingAs($leader)
            ->test(LrkForm::class)
            ->assertSet('isReady', false)
            ->assertSet('isFinalized', false)
            ->call('finalize');

        $this->assertFalse($group->refresh()->is_lrk_locked);
    }

    public function test_lrk_form_can_finalize_and_unfinalize_when_ready(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();

        $mdType = ProgramType::firstOrCreate(['code' => 'multidisiplin'], ['name' => 'Multidisiplin', 'is_active' => true]);
        $sosmasType = ProgramType::firstOrCreate(['code' => 'sosial_kemasyarakatan'], ['name' => 'Sosial Kemasyarakatan', 'is_active' => true]);
        $lainnyaType = ProgramType::firstOrCreate(['code' => 'lainnya'], ['name' => 'Lainnya', 'is_active' => true]);

        // Remove the extra member so only leader is in group
        $group->students()->where('id', '!=', $leader->id)->update(['group_id' => null]);

        foreach ([$mdType, $mdType, $sosmasType, $lainnyaType] as $type) {
            $p = Program::create([
                'group_id' => $group->id,
                'student_id' => $leader->id,
                'title' => 'Program '.$type->code,
                'program_type_id' => $type->id,
            ]);
            ProgramParticipant::create([
                'program_id' => $p->id,
                'student_id' => $leader->id,
                'status' => ProgramStatus::Approved,
            ]);
        }

        $this->assertTrue($group->refresh()->isLrkReady());

        Livewire::actingAs($leader)
            ->test(LrkForm::class)
            ->assertSet('isReady', true)
            ->assertSet('isFinalized', false)
            ->assertSeeHtml('confirm-finalize-modal')
            ->assertSee(__('Finalisasi LRK'))
            ->assertSee(__('Finalisasi Dokumen LRK?'))
            ->call('finalize')
            ->assertSet('isFinalized', true);

        $this->assertTrue($group->refresh()->is_lrk_locked);

        // Can unfinalize
        Livewire::actingAs($leader)
            ->test(LrkForm::class)
            ->assertSet('isFinalized', true)
            ->assertSeeHtml('confirm-unfinalize-modal')
            ->assertSee(__('Ubah Kembali'))
            ->assertSee(__('Ubah Kembali Dokumen LRK?'))
            ->assertSee(__('Cetak PDF'))
            ->call('unfinalize')
            ->assertSet('isFinalized', false);

        $this->assertFalse($group->refresh()->is_lrk_locked);
    }

    public function test_lrk_documents_switches_preview_to_print_when_ready_and_finalized(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();

        // Not finalized: shows Pratinjau PDF, not Cetak PDF
        Livewire::actingAs($leader)
            ->test(LrkDocuments::class)
            ->assertSee(__('Pratinjau PDF'))
            ->assertDontSee(__('Cetak PDF'));

        // Lock group and approve all programs
        $mdType = ProgramType::firstOrCreate(['code' => 'multidisiplin'], ['name' => 'Multidisiplin', 'is_active' => true]);
        $sosmasType = ProgramType::firstOrCreate(['code' => 'sosial_kemasyarakatan'], ['name' => 'Sosial Kemasyarakatan', 'is_active' => true]);
        $lainnyaType = ProgramType::firstOrCreate(['code' => 'lainnya'], ['name' => 'Lainnya', 'is_active' => true]);

        $group->students()->where('id', '!=', $leader->id)->update(['group_id' => null]);

        foreach ([$mdType, $mdType, $sosmasType, $lainnyaType] as $type) {
            $p = Program::create([
                'group_id' => $group->id,
                'student_id' => $leader->id,
                'title' => 'Program '.$type->code,
                'program_type_id' => $type->id,
            ]);
            ProgramParticipant::create([
                'program_id' => $p->id,
                'student_id' => $leader->id,
                'status' => ProgramStatus::Approved,
            ]);
        }

        $group->update(['is_lrk_locked' => true]);

        // Ready and Finalized: Preview changes to Print
        Livewire::actingAs($leader->fresh())
            ->test(LrkDocuments::class)
            ->assertSee(__('Cetak PDF'))
            ->assertDontSee(__('Pratinjau PDF'));
    }

    public function test_lpk_documents_switches_preview_to_print_when_ready_and_finalized(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();

        // Not finalized: shows Pratinjau PDF, not Cetak PDF
        Livewire::actingAs($leader)
            ->test(LpkDocuments::class)
            ->assertSee(__('Pratinjau PDF'))
            ->assertDontSee(__('Cetak PDF'));

        // Lock group and approve all programs
        $mdType = ProgramType::firstOrCreate(['code' => 'multidisiplin'], ['name' => 'Multidisiplin', 'is_active' => true]);
        $group->students()->where('id', '!=', $leader->id)->update(['group_id' => null]);

        $p = Program::create([
            'group_id' => $group->id,
            'student_id' => $leader->id,
            'title' => 'Program LPK Test',
            'program_type_id' => $mdType->id,
        ]);
        ProgramParticipant::create([
            'program_id' => $p->id,
            'student_id' => $leader->id,
            'status' => ProgramStatus::Approved,
            'lpk_status' => ProgramStatus::Approved,
        ]);

        $group->update(['is_lpk_locked' => true]);

        // Ready and Finalized: Preview changes to Print
        Livewire::actingAs($leader->fresh())
            ->test(LpkDocuments::class)
            ->assertSee(__('Cetak PDF'))
            ->assertDontSee(__('Pratinjau PDF'));
    }

    private function createGroupWithLeaderAndMember(): array
    {
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);

        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 10 Sukamaju',
            'background' => 'Latar belakang awal',
        ]);

        $leader = User::factory()->mahasiswa()->create([
            'group_id' => $group->id,
            'name' => 'Ketua Kelompok',
        ]);

        $member = User::factory()->mahasiswa()->create([
            'group_id' => $group->id,
            'name' => 'Anggota Kelompok',
        ]);

        $group->update([
            'student_leader_id' => $leader->id,
        ]);

        return [$period, $group, $leader, $member];
    }
}
