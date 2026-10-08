<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_mahasiswa_can_access_dashboard(): void
    {
        $user = User::factory()->mahasiswa()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Mahasiswa');
    }

    public function test_dpl_can_access_dashboard(): void
    {
        $user = User::factory()->dpl()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dosen KKN');
    }

    public function test_p2kkn_can_access_dashboard(): void
    {
        $user = User::factory()->p2kkn()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Admin P2KKN');
    }

    public function test_prodi_can_access_dashboard(): void
    {
        $user = User::factory()->prodi()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Program Studi');
    }

    public function test_fakultas_can_access_dashboard(): void
    {
        $user = User::factory()->fakultas()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Fakultas');
    }

    public function test_user_role_enum_has_correct_values(): void
    {
        $this->assertEquals('mahasiswa', UserRole::Mahasiswa->value);
        $this->assertEquals('dpl', UserRole::Dpl->value);
        $this->assertEquals('p2kkn', UserRole::P2kkn->value);
        $this->assertEquals('prodi', UserRole::Prodi->value);
        $this->assertEquals('fakultas', UserRole::Fakultas->value);
    }

    public function test_user_has_role_method(): void
    {
        $user = User::factory()->dpl()->create();

        $this->assertTrue($user->hasRole(UserRole::Dpl));
        $this->assertFalse($user->hasRole(UserRole::Mahasiswa));
    }

    public function test_user_has_any_role_method(): void
    {
        $user = User::factory()->p2kkn()->create();

        $this->assertTrue($user->hasAnyRole(UserRole::P2kkn, UserRole::Fakultas));
        $this->assertFalse($user->hasAnyRole(UserRole::Mahasiswa, UserRole::Dpl));
    }

    public function test_user_is_admin_method(): void
    {
        $p2kkn = User::factory()->p2kkn()->create();
        $prodi = User::factory()->prodi()->create();
        $fakultas = User::factory()->fakultas()->create();
        $mahasiswa = User::factory()->mahasiswa()->create();
        $dpl = User::factory()->dpl()->create();

        $this->assertTrue($p2kkn->isAdmin());
        $this->assertTrue($prodi->isAdmin());
        $this->assertTrue($fakultas->isAdmin());
        $this->assertFalse($mahasiswa->isAdmin());
        $this->assertFalse($dpl->isAdmin());
    }

    public function test_role_middleware_blocks_unauthorized_access(): void
    {
        $mahasiswa = User::factory()->mahasiswa()->create();

        // Register a test route with role middleware
        Route::middleware(['auth', 'role:p2kkn'])->get('/test-admin', function () {
            return 'admin only';
        });

        $response = $this->actingAs($mahasiswa)->get('/test-admin');

        $response->assertStatus(403);
    }

    public function test_role_middleware_allows_authorized_access(): void
    {
        $admin = User::factory()->p2kkn()->create();

        Route::middleware(['auth', 'role:p2kkn'])->get('/test-admin-allowed', function () {
            return 'admin only';
        });

        $response = $this->actingAs($admin)->get('/test-admin-allowed');

        $response->assertStatus(200);
    }

    public function test_role_middleware_accepts_multiple_roles(): void
    {
        $prodi = User::factory()->prodi()->create();

        Route::middleware(['auth', 'role:p2kkn,prodi,fakultas'])->get('/test-multi-role', function () {
            return 'multi role';
        });

        $response = $this->actingAs($prodi)->get('/test-multi-role');

        $response->assertStatus(200);
    }

    public function test_user_role_cast_to_enum(): void
    {
        $user = User::factory()->dpl()->create();

        $this->assertInstanceOf(UserRole::class, $user->role);
        $this->assertEquals(UserRole::Dpl, $user->role);
    }
}
