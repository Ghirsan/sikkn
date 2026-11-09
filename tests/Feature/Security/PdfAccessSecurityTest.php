<?php

namespace Tests\Feature\Security;

use App\Enums\Semester;
use App\Models\Group;
use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PdfAccessSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createKknEnvironment(): array
    {
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-06-01',
            'end_date' => '2026-07-31',
        ]);

        $groupA = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 1 Tembalang',
            'village' => 'Kramas',
            'district' => 'Tembalang',
            'regency' => 'Kota Semarang',
            'province' => 'Jawa Tengah',
            'start_date' => '2026-06-01',
            'end_date' => '2026-07-31',
        ]);

        $groupB = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 2 Banyumanik',
            'village' => 'Gedawang',
            'district' => 'Banyumanik',
            'regency' => 'Kota Semarang',
            'province' => 'Jawa Tengah',
            'start_date' => '2026-06-01',
            'end_date' => '2026-07-31',
        ]);

        $dplA = User::factory()->dpl()->create();
        $groupA->dpls()->attach($dplA->id);
        $groupA->update(['lead_dpl_id' => $dplA->id]);

        $dplB = User::factory()->dpl()->create();
        $groupB->dpls()->attach($dplB->id);
        $groupB->update(['lead_dpl_id' => $dplB->id]);

        $studentA = User::factory()->mahasiswa()->create(['group_id' => $groupA->id]);
        $studentB = User::factory()->mahasiswa()->create(['group_id' => $groupB->id]);

        $admin = User::factory()->p2kkn()->create();

        return [$period, $groupA, $groupB, $dplA, $dplB, $studentA, $studentB, $admin];
    }

    public function test_unauthenticated_user_cannot_access_any_pdf(): void
    {
        [, $groupA, , , , $studentA] = $this->createKknEnvironment();

        $this->get(route('lrk.pdf', $groupA))->assertRedirect(route('login'));
        $this->get(route('lpk.pdf', $groupA))->assertRedirect(route('login'));
        $this->get(route('logbook.pdf', $studentA))->assertRedirect(route('login'));
        $this->get(route('mentoring-logs.pdf', $studentA))->assertRedirect(route('login'));
    }

    public function test_unsigned_access_is_rejected_for_authenticated_users(): void
    {
        [, $groupA, , , , $studentA] = $this->createKknEnvironment();

        // Even though Student A belongs to Group A, direct unsigned request is forbidden by signed middleware
        $this->actingAs($studentA)->get(route('lrk.pdf', $groupA))->assertForbidden();
        $this->actingAs($studentA)->get(route('lpk.pdf', $groupA))->assertForbidden();
        $this->actingAs($studentA)->get(route('logbook.pdf', $studentA))->assertForbidden();
        $this->actingAs($studentA)->get(route('mentoring-logs.pdf', $studentA))->assertForbidden();
    }

    public function test_tampered_signature_is_rejected(): void
    {
        [, $groupA, $groupB, , , $studentA] = $this->createKknEnvironment();

        $signedUrl = URL::signedRoute('lrk.pdf', $groupA);
        // Attacker tampers ID in URL from groupA to groupB while keeping signature
        $tamperedUrl = str_replace((string) $groupA->id, (string) $groupB->id, $signedUrl);

        $this->actingAs($studentA)->get($tamperedUrl)->assertForbidden();
    }

    public function test_student_cannot_access_other_group_lrk_or_lpk_even_if_signed(): void
    {
        [, , $groupB, , , $studentA] = $this->createKknEnvironment();

        // Even with a signed URL generated for Group B, Student A cannot view it due to GroupPolicy
        $this->actingAs($studentA)->get(URL::signedRoute('lrk.pdf', $groupB))->assertForbidden();
        $this->actingAs($studentA)->get(URL::signedRoute('lpk.pdf', $groupB))->assertForbidden();
        $this->actingAs($studentA)->get(URL::signedRoute('lrk.preview', $groupB))->assertForbidden();
        $this->actingAs($studentA)->get(URL::signedRoute('lpk.preview', $groupB))->assertForbidden();
    }

    public function test_student_cannot_access_other_student_logbook_or_mentoring_even_if_signed(): void
    {
        [, , , , , $studentA, $studentB] = $this->createKknEnvironment();

        // Student A attempting to access Student B's records
        $this->actingAs($studentA)->get(URL::signedRoute('logbook.pdf', $studentB))->assertForbidden();
        $this->actingAs($studentA)->get(URL::signedRoute('mentoring-logs.pdf', $studentB))->assertForbidden();
    }

    public function test_unassigned_dpl_cannot_access_other_group_or_student_records(): void
    {
        [, $groupA, , , $dplB, $studentA] = $this->createKknEnvironment();

        // DPL B supervises Group B, attempting to access Group A and Student A
        $this->actingAs($dplB)->get(URL::signedRoute('lrk.pdf', $groupA))->assertForbidden();
        $this->actingAs($dplB)->get(URL::signedRoute('lpk.pdf', $groupA))->assertForbidden();
        $this->actingAs($dplB)->get(URL::signedRoute('logbook.pdf', $studentA))->assertForbidden();
        $this->actingAs($dplB)->get(URL::signedRoute('mentoring-logs.pdf', $studentA))->assertForbidden();
    }

    public function test_authorized_student_can_access_own_group_and_records(): void
    {
        [, $groupA, , , , $studentA] = $this->createKknEnvironment();

        $this->actingAs($studentA)->get(URL::signedRoute('lrk.pdf', $groupA))->assertOk();
        $this->actingAs($studentA)->get(URL::signedRoute('lpk.pdf', $groupA))->assertOk();
        $this->actingAs($studentA)->get(URL::signedRoute('logbook.pdf', $studentA))->assertOk();
        $this->actingAs($studentA)->get(URL::signedRoute('mentoring-logs.pdf', $studentA))->assertOk();
    }

    public function test_assigned_dpl_can_access_supervised_group_and_student_records(): void
    {
        [, $groupA, , $dplA, , $studentA] = $this->createKknEnvironment();

        $this->actingAs($dplA)->get(URL::signedRoute('lrk.pdf', $groupA))->assertOk();
        $this->actingAs($dplA)->get(URL::signedRoute('lpk.pdf', $groupA))->assertOk();
        $this->actingAs($dplA)->get(URL::signedRoute('logbook.pdf', $studentA))->assertOk();
        $this->actingAs($dplA)->get(URL::signedRoute('mentoring-logs.pdf', $studentA))->assertOk();
    }

    public function test_admin_p2kkn_can_access_any_group_and_student_records(): void
    {
        [, $groupA, $groupB, , , $studentA, $studentB, $admin] = $this->createKknEnvironment();

        $this->actingAs($admin)->get(URL::signedRoute('lrk.pdf', $groupA))->assertOk();
        $this->actingAs($admin)->get(URL::signedRoute('lpk.pdf', $groupB))->assertOk();
        $this->actingAs($admin)->get(URL::signedRoute('logbook.pdf', $studentA))->assertOk();
        $this->actingAs($admin)->get(URL::signedRoute('mentoring-logs.pdf', $studentB))->assertOk();
    }
}
