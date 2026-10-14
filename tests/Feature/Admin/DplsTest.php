<?php

namespace Tests\Feature\Admin;

use App\Enums\Semester;
use App\Livewire\Admin\Dpls;
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
}
