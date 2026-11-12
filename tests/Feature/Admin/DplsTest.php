<?php

namespace Tests\Feature\Admin;

use App\Enums\Semester;
use App\Enums\UserRole;
use App\Livewire\Admin\Dpls;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DplsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_admin_can_access_dpls_page(): void
    {
        $admin = User::factory()->p2kkn()->create();

        $response = $this->actingAs($admin)->get(route('admin.dpl.index'));

        $response->assertStatus(200);
        $response->assertSeeLivewire(Dpls::class);
    }

    public function test_non_admin_cannot_access_dpls_page(): void
    {
        $student = User::factory()->mahasiswa()->create();

        $this->actingAs($student)->get(route('admin.dpl.index'))->assertStatus(403);
    }

    public function test_admin_can_paginate_dpls(): void
    {
        $admin = User::factory()->p2kkn()->create();

        User::factory()->count(15)->dpl()->create();

        Livewire::actingAs($admin)
            ->test(Dpls::class)
            ->set('periodId', null)
            ->assertSet('paginators.page', 1)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->assertStatus(200);
    }

    public function test_searching_resets_pagination_page(): void
    {
        $admin = User::factory()->p2kkn()->create();

        User::factory()->count(15)->dpl()->create();

        Livewire::actingAs($admin)
            ->test(Dpls::class)
            ->set('periodId', null)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('search', 'Dosen')
            ->assertSet('paginators.page', 1);
    }

    public function test_changing_period_resets_pagination_page(): void
    {
        $admin = User::factory()->p2kkn()->create();

        User::factory()->count(15)->dpl()->create();

        Livewire::actingAs($admin)
            ->test(Dpls::class)
            ->set('periodId', null)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('periodId', 999)
            ->assertSet('paginators.page', 1);
    }

    public function test_stats_are_computed_accurately(): void
    {
        $admin = User::factory()->p2kkn()->create();

        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
        ]);

        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 1',
            'village' => 'Desa Makmur',
        ]);

        $assignedDpls = User::factory()->count(3)->dpl()->create();
        $unassignedDpls = User::factory()->count(5)->dpl()->create();

        foreach ($assignedDpls as $dpl) {
            $group->dpls()->attach($dpl);
        }

        Livewire::actingAs($admin)
            ->test(Dpls::class)
            ->set('periodId', null)
            ->assertViewHas('stats', [
                'total' => 8,
                'assigned' => 3,
                'unassigned' => 5,
            ]);
    }

    public function test_admin_can_open_create_dpl_modal(): void
    {
        $admin = User::factory()->p2kkn()->create();

        Livewire::actingAs($admin)
            ->test(Dpls::class)
            ->call('openCreateModal')
            ->assertSet('showCreateModal', true);
    }

    public function test_admin_can_create_dpl_successfully(): void
    {
        $admin = User::factory()->p2kkn()->create();

        Livewire::actingAs($admin)
            ->test(Dpls::class)
            ->call('openCreateModal')
            ->set('nip', '198001012005011001')
            ->set('name', 'Dr. Budi Dosen, M.Kom.')
            ->set('phone', '08123456789')
            ->call('createDpl')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);

        $this->assertDatabaseHas('users', [
            'nip' => '198001012005011001',
            'name' => 'Dr. Budi Dosen, M.Kom.',
            'role' => UserRole::Dpl->value,
            'email' => '198001012005011001@lecturer.sikkn.ac.id',
        ]);
    }

    public function test_create_dpl_validates_required_fields(): void
    {
        $admin = User::factory()->p2kkn()->create();

        Livewire::actingAs($admin)
            ->test(Dpls::class)
            ->call('openCreateModal')
            ->set('nip', '')
            ->set('name', '')
            ->call('createDpl')
            ->assertHasErrors(['nip', 'name']);
    }

    public function test_create_dpl_can_assign_to_group_and_set_lead(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
        ]);

        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 1',
            'village' => 'Desa Makmur',
        ]);

        Livewire::actingAs($admin)
            ->test(Dpls::class)
            ->call('openCreateModal')
            ->set('nip', '198505052010011002')
            ->set('name', 'Prof. Dr. Ir. Siti, M.T.')
            ->set('group_id', $group->id)
            ->set('is_lead', true)
            ->call('createDpl')
            ->assertHasNoErrors();

        $dpl = User::where('nip', '198505052010011002')->first();
        $this->assertNotNull($dpl);
        $this->assertTrue($group->fresh()->dpls->contains($dpl->id));
        $this->assertEquals($dpl->id, $group->fresh()->lead_dpl_id);
    }

    public function test_admin_can_edit_dpl_via_modal(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $dpl = User::factory()->dpl()->create([
            'nip' => '198101012006011003',
            'name' => 'Original Lecturer Name',
            'phone' => '081234567890',
        ]);

        Livewire::actingAs($admin)
            ->test(Dpls::class)
            ->call('openEditModal', $dpl->id)
            ->assertSet('isEditing', true)
            ->assertSet('editingDplId', $dpl->id)
            ->assertSet('nip', '198101012006011003')
            ->assertSet('name', 'Original Lecturer Name')
            ->assertSet('phone', '081234567890')
            ->set('name', 'Updated Lecturer Name')
            ->set('phone', '089876543210')
            ->call('saveDpl')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false)
            ->assertSet('isEditing', false)
            ->assertSet('editingDplId', null);

        $this->assertDatabaseHas('users', [
            'id' => $dpl->id,
            'name' => 'Updated Lecturer Name',
            'phone' => '089876543210',
        ]);
    }

    public function test_edit_dpl_validates_duplicate_nip(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $dplA = User::factory()->dpl()->create(['nip' => '19800000001']);
        $dplB = User::factory()->dpl()->create(['nip' => '19800000002']);

        Livewire::actingAs($admin)
            ->test(Dpls::class)
            ->call('openEditModal', $dplA->id)
            ->set('nip', '19800000002')
            ->call('saveDpl')
            ->assertHasErrors(['nip']);
    }

    public function test_editing_dpl_can_detach_or_switch_group_without_disturbing_other_groups(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
        ]);

        $groupA = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok A',
            'village' => 'Desa A',
        ]);
        $groupB = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok B',
            'village' => 'Desa B',
        ]);
        $groupOther = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok C',
            'village' => 'Desa C',
        ]);

        $dpl = User::factory()->dpl()->create(['nip' => '19890000111']);
        $dpl->dplGroups()->attach([$groupA->id, $groupOther->id]);
        $groupA->update(['lead_dpl_id' => $dpl->id]);

        // Switch from Group A to Group B
        Livewire::actingAs($admin)
            ->test(Dpls::class)
            ->set('periodId', $period->id)
            ->call('openEditModal', $dpl->id)
            ->assertSet('initialGroupId', $groupA->id)
            ->set('group_id', $groupB->id)
            ->set('is_lead', true)
            ->call('saveDpl')
            ->assertHasNoErrors();

        $this->assertFalse($groupA->fresh()->dpls->contains($dpl->id));
        $this->assertNull($groupA->fresh()->lead_dpl_id);
        $this->assertTrue($groupB->fresh()->dpls->contains($dpl->id));
        $this->assertSame($dpl->id, $groupB->fresh()->lead_dpl_id);
        $this->assertTrue($groupOther->fresh()->dpls->contains($dpl->id));

        // Now detach selected group cleanly by choosing Belum Ditugaskan (null)
        Livewire::actingAs($admin)
            ->test(Dpls::class)
            ->set('periodId', $period->id)
            ->call('openEditModal', $dpl->id)
            ->set('group_id', null)
            ->call('saveDpl')
            ->assertHasNoErrors();

        $dpl->refresh();
        $remainingGroupIds = $dpl->dplGroups()->pluck('groups.id')->all();
        $this->assertCount(1, $remainingGroupIds);
        $this->assertNotContains($groupA->id, $remainingGroupIds);
    }

    public function test_admin_can_delete_dpl_without_activity_records_and_detaches_groups(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
        ]);
        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok Uji',
            'village' => 'Desa Uji',
        ]);

        $dpl = User::factory()->dpl()->create();
        $group->dpls()->attach($dpl->id);
        $group->update(['lead_dpl_id' => $dpl->id]);

        Livewire::actingAs($admin)
            ->test(Dpls::class)
            ->assertSeeHtml('aria-disabled="false"')
            ->call('confirmDelete', $dpl->id)
            ->assertSet('deletingDplId', $dpl->id)
            ->assertDispatched('modal-show', name: 'delete-dpl-modal')
            ->assertNotDispatched('toast-show')
            ->call('delete')
            ->assertDispatched('toast-show')
            ->assertDispatched('modal-close', name: 'delete-dpl-modal');

        $this->assertDatabaseMissing('users', ['id' => $dpl->id]);
        $this->assertDatabaseMissing('dpl_group', ['dpl_id' => $dpl->id]);
        $this->assertNull($group->fresh()->lead_dpl_id);
    }

    public function test_admin_cannot_delete_dpl_who_has_submitted_grades(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
        ]);
        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok Nilai',
            'village' => 'Desa Nilai',
        ]);

        $dpl = User::factory()->dpl()->create();
        $student = User::factory()->mahasiswa()->create(['group_id' => $group->id]);
        $group->dpls()->attach($dpl->id);

        Grade::create([
            'student_id' => $student->id,
            'dpl_id' => $dpl->id,
            'group_id' => $group->id,
        ]);

        Livewire::actingAs($admin)
            ->test(Dpls::class)
            ->assertSeeHtml('aria-disabled="true"')
            ->assertSeeHtml('opacity-50')
            ->call('confirmDelete', $dpl->id)
            ->assertDispatched('toast-show')
            ->assertNotDispatched('modal-show')
            ->assertSet('deletingDplId', null);

        $this->assertDatabaseHas('users', ['id' => $dpl->id]);
        $this->assertDatabaseHas('grades', ['dpl_id' => $dpl->id, 'student_id' => $student->id]);
    }
}
