<?php

namespace Tests\Feature\Mahasiswa;

use App\Enums\Semester;
use App\Livewire\Mahasiswa\LrkForm;
use App\Models\Group;
use App\Models\Period;
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
