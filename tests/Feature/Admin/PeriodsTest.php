<?php

namespace Tests\Feature\Admin;

use App\Enums\Semester;
use App\Livewire\Admin\Periods;
use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PeriodsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_admin_can_access_periods_page(): void
    {
        $admin = User::factory()->p2kkn()->create();

        $response = $this->actingAs($admin)->get(route('admin.periods.index'));

        $response->assertStatus(200);
        $response->assertSeeLivewire(Periods::class);
    }

    public function test_admin_can_create_period(): void
    {
        $admin = User::factory()->p2kkn()->create();

        Livewire::actingAs($admin)
            ->test(Periods::class)
            ->call('startCreating')
            ->set('name', 'KKN TIM I 2026')
            ->set('semester', Semester::Ganjil->value)
            ->set('year', '2026')
            ->set('start_date', '2026-06-01')
            ->set('end_date', '2026-07-31')
            ->call('savePeriod')
            ->assertHasNoErrors()
            ->assertDispatched('modal-close', name: 'period-modal');

        $this->assertDatabaseHas('periods', [
            'name' => 'KKN TIM I 2026',
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
        ]);
    }

    public function test_admin_can_edit_period(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $period = Period::create([
            'name' => 'KKN TIM A',
            'semester' => Semester::Ganjil,
            'year' => 2026,
            'start_date' => '2026-06-01',
            'end_date' => '2026-07-31',
        ]);

        Livewire::actingAs($admin)
            ->test(Periods::class)
            ->call('editPeriod', $period->id)
            ->assertSet('isEditing', true)
            ->assertSet('editingId', $period->id)
            ->assertSet('name', 'KKN TIM A')
            ->set('name', 'KKN TIM A Updated')
            ->call('savePeriod')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('periods', [
            'id' => $period->id,
            'name' => 'KKN TIM A Updated',
        ]);
    }
}
