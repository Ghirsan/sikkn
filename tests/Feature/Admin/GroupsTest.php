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
}
