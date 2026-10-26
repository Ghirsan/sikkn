<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_mahasiswa_can_visit_the_dashboard(): void
    {
        $student = User::factory()->mahasiswa()->create();
        $this->actingAs($student);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Aksi Cepat Mahasiswa');
    }

    public function test_dpl_can_visit_the_dashboard(): void
    {
        $dpl = User::factory()->dpl()->create();
        $this->actingAs($dpl);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Belum Ada Kelompok Bimbingan Ditugaskan');
    }

    public function test_p2kkn_can_visit_the_dashboard(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $this->actingAs($admin);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Aksi Cepat Admin P2KKN');
    }

    public function test_prodi_can_visit_the_dashboard(): void
    {
        $prodi = User::factory()->prodi()->create();
        $this->actingAs($prodi);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Aksi Cepat Program Studi');
    }

    public function test_fakultas_can_visit_the_dashboard(): void
    {
        $fakultas = User::factory()->fakultas()->create();
        $this->actingAs($fakultas);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Aksi Cepat Fakultas');
    }
}
