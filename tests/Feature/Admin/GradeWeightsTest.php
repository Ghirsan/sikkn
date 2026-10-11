<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\GradeWeights;
use App\Livewire\Dpl\StudentGradeForm;
use App\Models\Grade;
use App\Models\GradeWeight;
use App\Models\User;
use Database\Seeders\GradeWeightSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GradeWeightsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(GradeWeightSeeder::class);
    }

    public function test_admin_can_access_grade_weights_page(): void
    {
        $admin = User::factory()->p2kkn()->create();

        $response = $this->actingAs($admin)->get(route('admin.grade-weights.index'));

        $response->assertStatus(200);
        $response->assertSeeLivewire(GradeWeights::class);
    }

    public function test_dpl_or_mahasiswa_cannot_access_grade_weights_page(): void
    {
        $dpl = User::factory()->dpl()->create();
        $mahasiswa = User::factory()->mahasiswa()->create();

        $this->actingAs($dpl)->get(route('admin.grade-weights.index'))->assertStatus(403);
        $this->actingAs($mahasiswa)->get(route('admin.grade-weights.index'))->assertStatus(403);
    }

    public function test_admin_can_update_all_grade_weights(): void
    {
        $admin = User::factory()->p2kkn()->create();

        Livewire::actingAs($admin)
            ->test(GradeWeights::class)
            ->set('weights.aktivitas_partisipatif', 35)
            ->set('weights.hasil_proyek', 20)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.grade-weights.index'))
            ->assertDispatched('toast');

        $this->assertDatabaseHas('grade_weights', [
            'code' => 'aktivitas_partisipatif',
            'weight' => 0.35,
        ]);

        $this->assertDatabaseHas('grade_weights', [
            'code' => 'hasil_proyek',
            'weight' => 0.20,
        ]);
    }

    public function test_admin_can_save_and_reload_floating_point_weights(): void
    {
        $admin = User::factory()->p2kkn()->create();

        // 25.5 + 29.5 + 20 + 5 + 10 + 10 = 100
        Livewire::actingAs($admin)
            ->test(GradeWeights::class)
            ->set('weights.aktivitas_partisipatif', 25.5)
            ->set('weights.hasil_proyek', 29.5)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('grade_weights', [
            'code' => 'aktivitas_partisipatif',
            'weight' => 0.2550,
        ]);

        $this->assertDatabaseHas('grade_weights', [
            'code' => 'hasil_proyek',
            'weight' => 0.2950,
        ]);

        // Reload component
        $test = Livewire::actingAs($admin)->test(GradeWeights::class);
        $this->assertEquals(25.5, $test->get('weights.aktivitas_partisipatif'));
        $this->assertEquals(29.5, $test->get('weights.hasil_proyek'));
    }

    public function test_admin_cannot_save_weights_if_total_is_not_100(): void
    {
        $admin = User::factory()->p2kkn()->create();

        Livewire::actingAs($admin)
            ->test(GradeWeights::class)
            ->set('weights.aktivitas_partisipatif', 40) // total becomes 110%
            ->call('save')
            ->assertHasErrors(['total'])
            ->assertDispatched('toast');
    }

    public function test_prompt_save_validates_total_before_showing_modal(): void
    {
        $admin = User::factory()->p2kkn()->create();

        // Invalid total (110%)
        Livewire::actingAs($admin)
            ->test(GradeWeights::class)
            ->set('weights.aktivitas_partisipatif', 40)
            ->call('promptSave')
            ->assertHasErrors(['total']);

        // Valid total (100%)
        Livewire::actingAs($admin)
            ->test(GradeWeights::class)
            ->set('weights.aktivitas_partisipatif', 35)
            ->set('weights.hasil_proyek', 20)
            ->call('promptSave')
            ->assertHasNoErrors();
    }

    public function test_grade_calculation_persists_6_siap_components_and_adapts_to_weights(): void
    {
        // Update aktivitas_partisipatif to 50% and hasil_proyek to 5%
        GradeWeight::where('code', 'aktivitas_partisipatif')->update(['weight' => 0.50]);
        GradeWeight::where('code', 'hasil_proyek')->update(['weight' => 0.05]);

        $student = User::factory()->mahasiswa()->create();
        $dpl = User::factory()->dpl()->create();

        $grade = new Grade([
            'student_id' => $student->id,
            'dpl_id' => $dpl->id,
            'pembekalan' => 80,
            'gelar_karya' => 90,
            'kehadiran' => 85,
            'lrk' => 90,
            'integritas' => 95,
            'sosial_kemasyarakatan' => 100,
            'lpk' => 88,
            'ujian_akhir' => 92,
        ]);

        $grade->calculateGrade();
        $grade->save();

        // Check persisted SIAP components in database
        $this->assertDatabaseHas('grades', [
            'student_id' => $student->id,
            'aktivitas_partisipatif' => 100.00, // sosial_kemasyarakatan
            'hasil_proyek' => 90.00,           // lrk
            'tugas' => 90.00,                  // (85 + 95) / 2
            'quiz' => 85.00,                   // (80 + 90) / 2
            'uts' => 88.00,                    // lpk
            'uas' => 92.00,                    // ujian_akhir
        ]);

        // Calculation check with weights:
        // 100 * 0.50 + 90 * 0.05 + 90 * 0.20 + 85 * 0.05 + 88 * 0.10 + 92 * 0.10
        // = 50 + 4.5 + 18 + 4.25 + 8.8 + 9.2 = 94.75
        $this->assertEquals(94.75, (float) $grade->final_grade);
        $this->assertEquals('A', $grade->grade_letter);
    }

    public function test_dpl_saving_grade_persists_both_kkn_and_siap_components(): void
    {
        $dpl = User::factory()->dpl()->create();
        $student = User::factory()->mahasiswa()->create();

        Livewire::actingAs($dpl)
            ->test(StudentGradeForm::class, ['student' => $student->id])
            ->set('pembekalan', 70)
            ->set('gelar_karya', 80)
            ->set('kehadiran', 90)
            ->set('lrk', 85)
            ->set('integritas', 90)
            ->set('sosial_kemasyarakatan', 95)
            ->set('lpk', 80)
            ->set('ujian_akhir', 85)
            ->call('saveGrade')
            ->assertHasNoErrors()
            ->assertRedirect(route('dpl.grades.index'));

        // Verify stored in DB
        $savedGrade = Grade::where('student_id', $student->id)->first();
        $this->assertNotNull($savedGrade);

        // KKN aspects
        $this->assertEquals(70.0, (float) $savedGrade->pembekalan);
        $this->assertEquals(80.0, (float) $savedGrade->gelar_karya);

        // 6 SIAP components
        $this->assertEquals(95.0, (float) $savedGrade->aktivitas_partisipatif);
        $this->assertEquals(85.0, (float) $savedGrade->hasil_proyek);
        $this->assertEquals(90.0, (float) $savedGrade->tugas);
        $this->assertEquals(75.0, (float) $savedGrade->quiz);
        $this->assertEquals(80.0, (float) $savedGrade->uts);
        $this->assertEquals(85.0, (float) $savedGrade->uas);

        // Default weights: 30%, 25%, 20%, 5%, 10%, 10%
        // (95 * 0.3) + (85 * 0.25) + (90 * 0.2) + (75 * 0.05) + (80 * 0.1) + (85 * 0.1)
        // = 28.5 + 21.25 + 18 + 3.75 + 8 + 8.5 = 88.00
        $this->assertEquals(88.00, (float) $savedGrade->final_grade);
        $this->assertEquals('A', $savedGrade->grade_letter);
    }
}
