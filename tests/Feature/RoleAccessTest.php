<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function testGuestCannotAccessDashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function testMahasiswaCanAccessDashboard(): void
    {
        $user = User::factory()->mahasiswa()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Mahasiswa');
    }

    public function testDplCanAccessDashboard(): void
    {
        $user = User::factory()->dpl()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dosen Pembimbing Lapangan');
    }

    public function testP2kknCanAccessDashboard(): void
    {
        $user = User::factory()->p2kkn()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Admin P2KKN');
    }

    public function testProdiCanAccessDashboard(): void
    {
        $user = User::factory()->prodi()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Program Studi');
    }

    public function testFakultasCanAccessDashboard(): void
    {
        $user = User::factory()->fakultas()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Fakultas');
    }

    public function testUserRoleEnumHasCorrectValues(): void
    {
        $this->assertEquals('mahasiswa', UserRole::Mahasiswa->value);
        $this->assertEquals('dpl', UserRole::Dpl->value);
        $this->assertEquals('p2kkn', UserRole::P2kkn->value);
        $this->assertEquals('prodi', UserRole::Prodi->value);
        $this->assertEquals('fakultas', UserRole::Fakultas->value);
    }

    public function testUserHasRoleMethod(): void
    {
        $user = User::factory()->dpl()->create();

        $this->assertTrue($user->hasRole(UserRole::Dpl));
        $this->assertFalse($user->hasRole(UserRole::Mahasiswa));
    }

    public function testUserHasAnyRoleMethod(): void
    {
        $user = User::factory()->p2kkn()->create();

        $this->assertTrue($user->hasAnyRole(UserRole::P2kkn, UserRole::Fakultas));
        $this->assertFalse($user->hasAnyRole(UserRole::Mahasiswa, UserRole::Dpl));
    }

    public function testUserIsAdminMethod(): void
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

    public function testRoleMiddlewareBlocksUnauthorizedAccess(): void
    {
        $mahasiswa = User::factory()->mahasiswa()->create();

        // Register a test route with role middleware
        \Illuminate\Support\Facades\Route::middleware(['auth', 'role:p2kkn'])->get('/test-admin', function () {
            return 'admin only';
        });

        $response = $this->actingAs($mahasiswa)->get('/test-admin');

        $response->assertStatus(403);
    }

    public function testRoleMiddlewareAllowsAuthorizedAccess(): void
    {
        $admin = User::factory()->p2kkn()->create();

        \Illuminate\Support\Facades\Route::middleware(['auth', 'role:p2kkn'])->get('/test-admin-allowed', function () {
            return 'admin only';
        });

        $response = $this->actingAs($admin)->get('/test-admin-allowed');

        $response->assertStatus(200);
    }

    public function testRoleMiddlewareAcceptsMultipleRoles(): void
    {
        $prodi = User::factory()->prodi()->create();

        \Illuminate\Support\Facades\Route::middleware(['auth', 'role:p2kkn,prodi,fakultas'])->get('/test-multi-role', function () {
            return 'multi role';
        });

        $response = $this->actingAs($prodi)->get('/test-multi-role');

        $response->assertStatus(200);
    }

    public function testUserRoleCastToEnum(): void
    {
        $user = User::factory()->dpl()->create();

        $this->assertInstanceOf(UserRole::class, $user->role);
        $this->assertEquals(UserRole::Dpl, $user->role);
    }
}
