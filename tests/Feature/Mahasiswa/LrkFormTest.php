<?php

namespace Tests\Feature\Mahasiswa;

use App\Enums\DocumentStatus;
use App\Enums\ProgramStatus;
use App\Enums\Semester;
use App\Livewire\Dpl\TeamDocuments;
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
use Illuminate\Support\Facades\URL;
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

        $response = $this->actingAs($leader)->get(URL::signedRoute('lrk.preview', $group));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('inline', (string) $response->headers->get('content-disposition'));
    }

    public function test_lrk_pdf_endpoint_defaults_to_inline_and_supports_download_query_parameter(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();

        // Default: inline preview
        $previewResponse = $this->actingAs($leader)->get(URL::signedRoute('lrk.pdf', $group));
        $previewResponse->assertOk();
        $this->assertStringContainsString('inline', (string) $previewResponse->headers->get('content-disposition'));

        // With download=1: attachment
        $downloadResponse = $this->actingAs($leader)->get(URL::signedRoute('lrk.pdf', [$group, 'download' => 1]));
        $downloadResponse->assertOk();
        $this->assertStringContainsString('attachment', (string) $downloadResponse->headers->get('content-disposition'));
    }

    public function test_lrk_form_can_save_structured_storyboard_video_script_and_macro_schedules(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();

        Livewire::actingAs($leader)
            ->test(LrkForm::class)
            ->assertCount('storyboard_scenes', 0)
            ->assertCount('video_script_scenes', 0)
            ->assertCount('macro_schedules', 5)
            ->call('addStoryboardScene')
            ->assertCount('storyboard_scenes', 1)
            ->assertSet('storyboard_scenes.0.scene', 'Scene 1')
            ->set('storyboard_scenes.0.image_url', 'https://drive.google.com/file/d/test123/view')
            ->set('storyboard_scenes.0.visual', 'Drone view persawahan dan embung desa')
            ->call('addVideoScriptScene')
            ->assertCount('video_script_scenes', 1)
            ->assertSet('video_script_scenes.0.scene', 'Scene 1')
            ->set('video_script_scenes.0.visual', 'Teks visual penutup')
            ->set('video_script_scenes.0.audio', 'Narasi penutup KKN Undip')
            ->set('video_script_scenes.0.duration', '15s')
            ->call('addMacroSchedule')
            ->assertCount('macro_schedules', 6)
            ->set('macro_schedules.5.kegiatan', 'Penerimaan dan Koordinasi Kecamatan')
            ->set('macro_schedules.5.m1', true)
            ->call('save');

        $group->refresh();
        $this->assertCount(1, $group->storyboard_scenes);
        $this->assertEquals('Scene 1', $group->storyboard_scenes[0]['scene']);
        $this->assertEquals('https://drive.google.com/file/d/test123/view', $group->storyboard_scenes[0]['image_url']);
        $this->assertEquals('Drone view persawahan dan embung desa', $group->storyboard_scenes[0]['visual']);
        $this->assertCount(1, $group->video_script_scenes);
        $this->assertEquals('Scene 1', $group->video_script_scenes[0]['scene']);
        $this->assertEquals('Teks visual penutup', $group->video_script_scenes[0]['visual']);
        $this->assertEquals('Narasi penutup KKN Undip', $group->video_script_scenes[0]['audio']);
        $this->assertEquals('15s', $group->video_script_scenes[0]['duration']);
        $this->assertCount(6, $group->macro_schedules);
        $this->assertEquals('Penerimaan dan Koordinasi Kecamatan', $group->macro_schedules[5]['kegiatan']);
        $this->assertTrue($group->macro_schedules[5]['m1']);
    }

    public function test_storyboard_scenes_are_incrementally_numbered_and_reindexed_on_removal(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();

        Livewire::actingAs($leader)
            ->test(LrkForm::class)
            ->assertCount('storyboard_scenes', 0)
            ->call('addStoryboardScene')
            ->call('addStoryboardScene')
            ->call('addStoryboardScene')
            ->assertCount('storyboard_scenes', 3)
            ->assertSet('storyboard_scenes.0.scene', 'Scene 1')
            ->assertSet('storyboard_scenes.1.scene', 'Scene 2')
            ->assertSet('storyboard_scenes.2.scene', 'Scene 3')
            ->set('storyboard_scenes.0.visual', 'Visual Scene 1')
            ->set('storyboard_scenes.1.visual', 'Visual Scene 2')
            ->set('storyboard_scenes.2.visual', 'Visual Scene 3')
            // Remove middle scene (Scene 2)
            ->call('removeStoryboardScene', 1)
            ->assertCount('storyboard_scenes', 2)
            ->assertSet('storyboard_scenes.0.scene', 'Scene 1')
            ->assertSet('storyboard_scenes.0.visual', 'Visual Scene 1')
            ->assertSet('storyboard_scenes.1.scene', 'Scene 2')
            ->assertSet('storyboard_scenes.1.visual', 'Visual Scene 3');
    }

    public function test_video_script_scenes_are_incrementally_numbered_and_reindexed_on_removal(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();

        Livewire::actingAs($leader)
            ->test(LrkForm::class)
            ->assertCount('video_script_scenes', 0)
            ->call('addVideoScriptScene')
            ->call('addVideoScriptScene')
            ->call('addVideoScriptScene')
            ->assertCount('video_script_scenes', 3)
            ->assertSet('video_script_scenes.0.scene', 'Scene 1')
            ->assertSet('video_script_scenes.1.scene', 'Scene 2')
            ->assertSet('video_script_scenes.2.scene', 'Scene 3')
            ->set('video_script_scenes.0.visual', 'Visual 1')
            ->set('video_script_scenes.0.audio', 'Narasi 1')
            ->set('video_script_scenes.0.duration', '10s')
            ->set('video_script_scenes.1.visual', 'Visual 2')
            ->set('video_script_scenes.1.audio', 'Narasi 2')
            ->set('video_script_scenes.1.duration', '15s')
            ->set('video_script_scenes.2.visual', 'Visual 3')
            ->set('video_script_scenes.2.audio', 'Narasi 3')
            ->set('video_script_scenes.2.duration', '20s')
            // Remove middle scene (Scene 2)
            ->call('removeVideoScriptScene', 1)
            ->assertCount('video_script_scenes', 2)
            ->assertSet('video_script_scenes.0.scene', 'Scene 1')
            ->assertSet('video_script_scenes.0.visual', 'Visual 1')
            ->assertSet('video_script_scenes.0.duration', '10s')
            ->assertSet('video_script_scenes.1.scene', 'Scene 2')
            ->assertSet('video_script_scenes.1.visual', 'Visual 3')
            ->assertSet('video_script_scenes.1.duration', '20s');
    }

    public function test_lrk_form_adapts_macro_schedule_weeks_to_kkn_period(): void
    {
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-28', // Exactly 4 weeks (28 days, Mon-Sun)
        ]);

        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 4 Minggu',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-28',
        ]);

        $leader = User::factory()->mahasiswa()->create([
            'group_id' => $group->id,
            'name' => 'Ketua Kelompok 4 Minggu',
        ]);

        $group->update(['student_leader_id' => $leader->id]);

        $this->assertEquals(4, $group->getWeeksCount());
        $this->assertEquals(['m1', 'm2', 'm3', 'm4'], $group->getWeekKeys());

        Livewire::actingAs($leader)
            ->test(LrkForm::class)
            ->assertSet('weekKeys', ['m1', 'm2', 'm3', 'm4'])
            ->assertCount('macro_schedules', 5)
            ->call('addMacroSchedule')
            ->assertCount('macro_schedules', 6)
            ->assertSet('macro_schedules.5.m1', false)
            ->assertSet('macro_schedules.5.m2', false)
            ->assertSet('macro_schedules.5.m3', false)
            ->assertSet('macro_schedules.5.m4', false)
            ->set('macro_schedules.5.kegiatan', 'Kegiatan Minggu Ke-4')
            ->set('macro_schedules.5.m4', true)
            ->call('save')
            ->assertHasNoErrors();

        $group->refresh();
        $this->assertCount(6, $group->macro_schedules);
        $this->assertTrue($group->macro_schedules[5]['m4']);
        $this->assertFalse($group->macro_schedules[5]['m1']);
    }

    public function test_lrk_form_cannot_submit_when_document_not_ready(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();

        Livewire::actingAs($leader)
            ->test(LrkForm::class)
            ->assertSet('isReady', false)
            ->assertSet('isSubmitted', false)
            ->call('submit');

        $this->assertEquals(DocumentStatus::Draft, $group->refresh()->lrk_status);
        $this->assertFalse($group->is_lrk_locked);
    }

    public function test_lrk_form_can_submit_and_withdraw_when_ready(): void
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
            ->assertSet('isSubmitted', false)
            ->assertSeeHtml('confirm-submit-modal')
            ->assertSee(__('Ajukan LRK ke DPL'))
            ->assertSee(__('Ajukan Dokumen LRK ke DPL?'))
            ->call('submit')
            ->assertSet('isSubmitted', true)
            ->assertSet('canEdit', false);

        $this->assertEquals(DocumentStatus::Submitted, $group->refresh()->lrk_status);
        $this->assertNotNull($group->lrk_submitted_at);

        // Can withdraw
        Livewire::actingAs($leader)
            ->test(LrkForm::class)
            ->assertSet('isSubmitted', true)
            ->assertSeeHtml('confirm-withdraw-modal')
            ->assertSee(__('Tarik Pengajuan'))
            ->assertSee(__('Tarik Pengajuan Dokumen LRK?'))
            ->call('withdraw')
            ->assertSet('isSubmitted', false)
            ->assertSet('canEdit', true);

        $this->assertEquals(DocumentStatus::Draft, $group->refresh()->lrk_status);
    }

    public function test_dpl_can_inspect_and_request_revision_or_approve_lrk(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();
        $dpl = User::factory()->dpl()->create();
        $group->dpls()->attach($dpl->id);

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

        // Leader submits LRK
        $group->update([
            'lrk_status' => DocumentStatus::Submitted,
            'lrk_submitted_at' => now(),
            'background' => 'Latar belakang kelompok 10',
        ]);

        // DPL inspects and requests revision
        Livewire::actingAs($dpl)
            ->test(TeamDocuments::class)
            ->call('inspectLrk', $group->id)
            ->assertSet('reviewingGroupId', $group->id)
            ->set('lrkReviewNote', 'Mohon lengkapi data potensi desa lebih mendalam.')
            ->call('requestLrkRevision');

        $this->assertEquals(DocumentStatus::NeedsRevision, $group->refresh()->lrk_status);
        $this->assertEquals('Mohon lengkapi data potensi desa lebih mendalam.', $group->lrk_notes);

        // Leader sees revision note in form
        Livewire::actingAs($leader)
            ->test(LrkForm::class)
            ->assertSet('isNeedsRevision', true)
            ->assertSet('canEdit', true)
            ->assertSee('Mohon lengkapi data potensi desa lebih mendalam.')
            ->assertSee(__('Ajukan Ulang LRK'));

        // DPL inspects and approves LRK
        Livewire::actingAs($dpl)
            ->test(TeamDocuments::class)
            ->call('inspectLrk', $group->id)
            ->call('approveLrk');

        $this->assertEquals(DocumentStatus::Approved, $group->refresh()->lrk_status);
        $this->assertTrue($group->is_lrk_locked);

        // Leader sees approved state and Cetak PDF
        Livewire::actingAs($leader->fresh())
            ->test(LrkForm::class)
            ->assertSet('isFinalized', true)
            ->assertSee(__('Dokumen LRK Telah Disetujui DPL'))
            ->assertSee(__('Cetak PDF'));
    }

    public function test_lrk_documents_switches_preview_to_print_when_ready_and_finalized(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();

        // Not finalized: shows Pratinjau LRK, not Cetak LRK
        Livewire::actingAs($leader)
            ->test(LrkDocuments::class)
            ->assertSee(__('Pratinjau LRK'))
            ->assertDontSee(__('Cetak LRK'));

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
            ->assertSee(__('Cetak LRK'))
            ->assertDontSee(__('Pratinjau LRK'));
    }

    public function test_lpk_documents_switches_preview_to_print_when_ready_and_finalized(): void
    {
        [$period, $group, $leader] = $this->createGroupWithLeaderAndMember();

        // Not finalized: shows Pratinjau LPK, not Cetak LPK
        Livewire::actingAs($leader)
            ->test(LpkDocuments::class)
            ->assertSee(__('Pratinjau LPK'))
            ->assertDontSee(__('Cetak LPK'));

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
            ->assertSee(__('Cetak LPK'))
            ->assertDontSee(__('Pratinjau LPK'));
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
