<?php

namespace Tests\Feature\Admin;

use App\Enums\Semester;
use App\Livewire\Admin\Dashboard;
use App\Models\Group;
use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_active_period_and_kpi_counters(): void
    {
        $admin = User::factory()->p2kkn()->create();

        $period = Period::create([
            'name' => 'KKN Tematik 2026',
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(25)->toDateString(),
        ]);

        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 01 Merdeka',
            'village' => 'Desa Merdeka',
            'is_lrk_locked' => true,
        ]);

        $dpl = User::factory()->dpl()->create();
        $group->dpls()->attach($dpl->id);

        User::factory()->mahasiswa()->count(3)->create([
            'group_id' => $group->id,
        ]);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertSee('KKN Tematik 2026')
            ->assertSee('Periode KKN')
            ->assertSee('Peserta KKN')
            ->assertSee('Dosen KKN (DPL)')
            ->assertSee('Kelompok KKN')
            ->assertSee('Status Dokumen Kelompok')
            ->assertSee('Aktivitas Program Kerja')
            ->assertSee('Aksi Cepat Admin P2KKN');
    }

    public function test_admin_dashboard_handles_no_active_period(): void
    {
        $admin = User::factory()->p2kkn()->create();

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertSee('Tidak Ada Periode Aktif')
            ->assertSee('0');
    }
}
