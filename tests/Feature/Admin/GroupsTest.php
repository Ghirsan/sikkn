<?php

namespace Tests\Feature\Admin;

use App\Enums\Semester;
use App\Livewire\Admin\Groups;
use App\Models\Group;
use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GroupsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_admin_can_access_groups_page(): void
    {
        $admin = User::factory()->p2kkn()->create();

        $response = $this->actingAs($admin)->get(route('admin.groups.index'));

        $response->assertStatus(200);
        $response->assertSeeLivewire(Groups::class);
    }

    public function test_non_admin_cannot_access_groups_page(): void
    {
        $student = User::factory()->mahasiswa()->create();

        $this->actingAs($student)->get(route('admin.groups.index'))->assertStatus(403);
    }

    public function test_admin_can_paginate_groups(): void
    {
        $admin = User::factory()->p2kkn()->create();

        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
        ]);

        for ($i = 1; $i <= 15; $i++) {
            Group::create([
                'period_id' => $period->id,
                'name' => "Kelompok {$i}",
                'village' => "Desa {$i}",
            ]);
        }

        Livewire::actingAs($admin)
            ->test(Groups::class)
            ->assertSet('paginators.page', 1)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->assertStatus(200);
    }

    public function test_searching_resets_pagination_page(): void
    {
        $admin = User::factory()->p2kkn()->create();

        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
        ]);

        for ($i = 1; $i <= 15; $i++) {
            Group::create([
                'period_id' => $period->id,
                'name' => "Kelompok {$i}",
                'village' => "Desa {$i}",
            ]);
        }

        Livewire::actingAs($admin)
            ->test(Groups::class)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('search', 'Kelompok 1')
            ->assertSet('paginators.page', 1);
    }

    public function test_changing_period_resets_pagination_page(): void
    {
        $admin = User::factory()->p2kkn()->create();

        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
        ]);

        for ($i = 1; $i <= 15; $i++) {
            Group::create([
                'period_id' => $period->id,
                'name' => "Kelompok {$i}",
                'village' => "Desa {$i}",
            ]);
        }

        Livewire::actingAs($admin)
            ->test(Groups::class)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('periodId', 999)
            ->assertSet('paginators.page', 1);
    }

    public function test_stats_are_computed_accurately_across_pages(): void
    {
        $admin = User::factory()->p2kkn()->create();

        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
        ]);

        $dpl = User::factory()->dpl()->create();

        for ($i = 1; $i <= 12; $i++) {
            $group = Group::create([
                'period_id' => $period->id,
                'name' => "Kelompok {$i}",
                'village' => "Desa {$i}",
            ]);

            // Assign DPL to first 4 groups
            if ($i <= 4) {
                $group->dpls()->attach($dpl);
            }
        }

        Livewire::actingAs($admin)
            ->test(Groups::class)
            ->assertViewHas('stats', [
                'total' => 12,
                'with_dpl' => 4,
                'without_dpl' => 8,
            ]);
    }

    public function test_admin_can_open_create_group_modal(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
        ]);

        Livewire::actingAs($admin)
            ->test(Groups::class)
            ->call('openCreateModal')
            ->assertSet('showCreateModal', true)
            ->assertSet('create_period_id', $period->id);
    }

    public function test_admin_can_create_group_successfully(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
        ]);

        Livewire::actingAs($admin)
            ->test(Groups::class)
            ->call('openCreateModal')
            ->set('create_period_id', $period->id)
            ->set('create_name', 'Kelompok 99')
            ->set('create_village', 'Desa Sukamaju')
            ->set('create_district', 'Kecamatan Makmur')
            ->call('createGroup')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);

        $this->assertDatabaseHas('groups', [
            'period_id' => $period->id,
            'name' => 'Kelompok 99',
            'village' => 'Desa Sukamaju',
            'district' => 'Kecamatan Makmur',
        ]);

        $createdGroup = Group::where('name', 'Kelompok 99')->first();
        $this->assertNotNull($createdGroup->ulid);
        $this->assertEquals(26, strlen($createdGroup->ulid));
    }

    public function test_create_group_validates_required_fields(): void
    {
        $admin = User::factory()->p2kkn()->create();

        Livewire::actingAs($admin)
            ->test(Groups::class)
            ->call('openCreateModal')
            ->set('create_period_id', null)
            ->set('create_name', '')
            ->set('create_village', '')
            ->set('create_district', '')
            ->call('createGroup')
            ->assertHasErrors(['create_period_id', 'create_name', 'create_village', 'create_district']);
    }

    public function test_create_group_enforces_unique_name_per_period(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
        ]);

        Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 1',
            'village' => 'Desa A',
            'district' => 'Kecamatan B',
        ]);

        Livewire::actingAs($admin)
            ->test(Groups::class)
            ->call('openCreateModal')
            ->set('create_period_id', $period->id)
            ->set('create_name', 'Kelompok 1')
            ->set('create_village', 'Desa Lain')
            ->set('create_district', 'Kecamatan Lain')
            ->call('createGroup')
            ->assertHasErrors(['create_name']);
    }

    public function test_admin_can_edit_group(): void
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
            'name' => 'Kelompok Lama',
            'village' => 'Desa Lama',
            'district' => 'Kecamatan Lama',
        ]);

        Livewire::actingAs($admin)
            ->test(Groups::class)
            ->call('openEditModal', $group->id)
            ->assertSet('isEditing', true)
            ->assertSet('editingGroupId', $group->id)
            ->assertSet('create_name', 'Kelompok Lama')
            ->assertSet('create_village', 'Desa Lama')
            ->set('create_name', 'Kelompok Baru Diedit')
            ->set('create_village', 'Desa Baru')
            ->call('saveGroup')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false)
            ->assertSet('isEditing', false)
            ->assertSet('editingGroupId', null);

        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'name' => 'Kelompok Baru Diedit',
            'village' => 'Desa Baru',
        ]);
    }
}
