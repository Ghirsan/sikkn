<?php

namespace Tests\Feature\Admin;

use App\Enums\DocumentStatus;
use App\Enums\Semester;
use App\Livewire\Admin\Documents;
use App\Models\Group;
use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_admin_can_access_documents_page(): void
    {
        $admin = User::factory()->p2kkn()->create();

        $response = $this->actingAs($admin)->get(route('admin.documents.index'));

        $response->assertStatus(200);
        $response->assertSeeLivewire(Documents::class);
    }

    public function test_non_admin_cannot_access_documents_page(): void
    {
        $student = User::factory()->mahasiswa()->create();

        $this->actingAs($student)->get(route('admin.documents.index'))->assertStatus(403);
    }

    public function test_documents_stats_are_computed_correctly(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
        ]);

        // Group 1: Completed (both LRK and LPK locked/approved)
        Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 1',
            'village' => 'Desa Makmur',
            'is_lrk_locked' => true,
            'is_lpk_locked' => true,
        ]);

        // Group 2: Only LRK Approved
        Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 2',
            'village' => 'Desa Sejahtera',
            'lrk_status' => DocumentStatus::Approved->value,
            'is_lpk_locked' => false,
        ]);

        // Group 3: Only LPK Locked (edge case)
        Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 3',
            'village' => 'Desa Jaya',
            'lrk_status' => DocumentStatus::Draft->value,
            'is_lpk_locked' => true,
        ]);

        // Group 4: Neither approved
        Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 4',
            'village' => 'Desa Baru',
            'lrk_status' => DocumentStatus::Submitted->value,
            'is_lpk_locked' => false,
        ]);

        Livewire::actingAs($admin)
            ->test(Documents::class, ['periodId' => $period->id])
            ->assertViewHas('stats', [
                'total' => 4,
                'lrk_approved' => 2,
                'lpk_approved' => 2,
                'completed' => 1,
            ]);
    }

    public function test_admin_can_search_documents_by_name_location_and_dpl(): void
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
            'name' => 'Kelompok Mawar',
            'village' => 'Desa Karanganyar',
        ]);

        $groupB = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok Melati',
            'village' => 'Desa Sukamaju',
        ]);

        $dpl = User::factory()->dpl()->create(['name' => 'Dr. Suparman, M.Sc.']);
        $groupB->dpls()->attach($dpl);

        // Search by group name
        Livewire::actingAs($admin)
            ->test(Documents::class, ['periodId' => $period->id])
            ->set('search', 'Mawar')
            ->assertSee('Kelompok Mawar')
            ->assertDontSee('Kelompok Melati');

        // Search by location
        Livewire::actingAs($admin)
            ->test(Documents::class, ['periodId' => $period->id])
            ->set('search', 'Sukamaju')
            ->assertSee('Kelompok Melati')
            ->assertDontSee('Kelompok Mawar');

        // Search by DPL name
        Livewire::actingAs($admin)
            ->test(Documents::class, ['periodId' => $period->id])
            ->set('search', 'Suparman')
            ->assertSee('Kelompok Melati')
            ->assertDontSee('Kelompok Mawar');
    }

    public function test_admin_can_filter_documents_by_status(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
        ]);

        $completedGroup = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok Selesai',
            'is_lrk_locked' => true,
            'is_lpk_locked' => true,
        ]);

        $pendingLrkGroup = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok Pending LRK',
            'lrk_status' => DocumentStatus::Submitted->value,
            'is_lrk_locked' => false,
            'is_lpk_locked' => false,
        ]);

        // Filter completed
        Livewire::actingAs($admin)
            ->test(Documents::class, ['periodId' => $period->id])
            ->set('statusFilter', 'completed')
            ->assertSee('Kelompok Selesai')
            ->assertDontSee('Kelompok Pending LRK');

        // Filter lrk_pending
        Livewire::actingAs($admin)
            ->test(Documents::class, ['periodId' => $period->id])
            ->set('statusFilter', 'lrk_pending')
            ->assertSee('Kelompok Pending LRK')
            ->assertDontSee('Kelompok Selesai');
    }

    public function test_admin_can_filter_documents_by_period(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $period1 = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2025,
            'start_date' => '2025-01-01',
            'end_date' => '2025-02-01',
        ]);
        $period2 = Period::create([
            'semester' => Semester::Genap->value,
            'year' => 2026,
            'start_date' => '2026-07-01',
            'end_date' => '2026-08-01',
        ]);

        Group::create([
            'period_id' => $period1->id,
            'name' => 'Kelompok Periode Satu',
        ]);
        Group::create([
            'period_id' => $period2->id,
            'name' => 'Kelompok Periode Dua',
        ]);

        Livewire::actingAs($admin)
            ->test(Documents::class, ['periodId' => $period1->id])
            ->assertSee('Kelompok Periode Satu')
            ->assertDontSee('Kelompok Periode Dua')
            ->set('periodId', $period2->id)
            ->assertSee('Kelompok Periode Dua')
            ->assertDontSee('Kelompok Periode Satu');
    }

    public function test_signed_pdf_urls_are_generated(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
        ]);

        $groupFinal = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok Final',
            'is_lrk_locked' => true,
            'is_lpk_locked' => true,
        ]);

        $groupDraft = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok Draft',
            'is_lrk_locked' => false,
            'is_lpk_locked' => false,
        ]);

        Livewire::actingAs($admin)
            ->test(Documents::class, ['periodId' => $period->id])
            ->assertSee('Unduh LRK')
            ->assertSee('Unduh LPK')
            ->assertSee('Pratinjau LRK')
            ->assertSee('Pratinjau LPK');
    }
}
