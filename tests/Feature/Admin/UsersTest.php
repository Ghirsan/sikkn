<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Livewire\Admin\Users;
use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Faculty $faculty;

    private StudyProgram $studyProgram;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => UserRole::P2kkn,
            'email' => 'admin@sikkn.test',
        ]);

        $this->faculty = Faculty::create([
            'name' => 'Fakultas Teknik',
            'code' => 'FT',
            'short_name' => 'FT',
        ]);

        $this->studyProgram = StudyProgram::create([
            'name' => 'Teknik Informatika',
            'code' => '240601',
            'faculty_id' => $this->faculty->id,
        ]);
    }

    public function test_only_staff_and_observer_roles_are_displayed_in_pengguna_sistem(): void
    {
        $fakultasUser = User::factory()->create([
            'role' => UserRole::Fakultas,
            'faculty_id' => $this->faculty->id,
            'name' => 'Dekanat FT',
            'email' => 'dekanat@ft.test',
        ]);

        $prodiUser = User::factory()->create([
            'role' => UserRole::Prodi,
            'study_program_id' => $this->studyProgram->id,
            'faculty_id' => $this->faculty->id,
            'name' => 'Kaprodi IF',
            'email' => 'kaprodi@if.test',
        ]);

        $student = User::factory()->create([
            'role' => UserRole::Mahasiswa,
            'name' => 'Mahasiswa Test',
            'email' => 'mhs@test.com',
        ]);

        $dpl = User::factory()->create([
            'role' => UserRole::Dpl,
            'name' => 'Dosen Test',
            'email' => 'dpl@test.com',
        ]);

        Livewire::actingAs($this->admin)
            ->test(Users::class)
            ->assertSee('Dekanat FT')
            ->assertSee('Kaprodi IF')
            ->assertSee($this->admin->name)
            ->assertDontSee($student->name)
            ->assertDontSee($dpl->name);
    }

    public function test_admin_can_create_p2kkn_user(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Users::class)
            ->call('create')
            ->assertSee('Default: password')
            ->set('name', 'Admin Baru')
            ->set('email', 'adminbaru@sikkn.test')
            ->set('role', UserRole::P2kkn->value)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $created = User::where('email', 'adminbaru@sikkn.test')->firstOrFail();
        $this->assertSame('Admin Baru', $created->name);
        $this->assertSame(UserRole::P2kkn, $created->role);
        $this->assertNull($created->faculty_id);
        $this->assertNull($created->study_program_id);
        $this->assertTrue(Hash::check('password', $created->password));
    }

    public function test_admin_can_create_user_with_custom_password_and_update_without_changing_password(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Users::class)
            ->set('name', 'Admin Kustom')
            ->set('email', 'adminkustom@sikkn.test')
            ->set('role', UserRole::P2kkn->value)
            ->set('password', '12345')
            ->call('save')
            ->assertHasErrors(['password']);

        Livewire::actingAs($this->admin)
            ->test(Users::class)
            ->set('name', 'Admin Kustom')
            ->set('email', 'adminkustom@sikkn.test')
            ->set('role', UserRole::P2kkn->value)
            ->set('password', 'custompass123')
            ->call('save')
            ->assertHasNoErrors();

        $user = User::where('email', 'adminkustom@sikkn.test')->firstOrFail();
        $this->assertTrue(Hash::check('custompass123', $user->password));

        Livewire::actingAs($this->admin)
            ->test(Users::class)
            ->call('edit', $user->id)
            ->assertSee('Kosongkan jika tidak ingin mengubah')
            ->set('name', 'Admin Kustom Updated')
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertSame('Admin Kustom Updated', $user->name);
        $this->assertTrue(Hash::check('custompass123', $user->password));
    }

    public function test_creating_fakultas_user_requires_faculty_id_and_prevents_duplicate(): void
    {
        // First without faculty_id should fail validation
        Livewire::actingAs($this->admin)
            ->test(Users::class)
            ->set('name', 'PIC FT')
            ->set('email', 'pic@ft.test')
            ->set('role', UserRole::Fakultas->value)
            ->set('password', 'password123')
            ->set('faculty_id', '')
            ->call('save')
            ->assertHasErrors(['faculty_id']);

        // With faculty_id and blank password should succeed using default password
        Livewire::actingAs($this->admin)
            ->test(Users::class)
            ->set('name', 'PIC FT')
            ->set('email', 'pic@ft.test')
            ->set('role', UserRole::Fakultas->value)
            ->set('faculty_id', (string) $this->faculty->id)
            ->call('save')
            ->assertHasNoErrors();

        $createdFakultas = User::where('email', 'pic@ft.test')->firstOrFail();
        $this->assertSame(UserRole::Fakultas, $createdFakultas->role);
        $this->assertSame($this->faculty->id, $createdFakultas->faculty_id);
        $this->assertTrue(Hash::check('password', $createdFakultas->password));

        // Second attempt with same faculty should fail duplicate check
        Livewire::actingAs($this->admin)
            ->test(Users::class)
            ->set('name', 'PIC FT Duplicate')
            ->set('email', 'pic2@ft.test')
            ->set('role', UserRole::Fakultas->value)
            ->set('password', 'password123')
            ->set('faculty_id', (string) $this->faculty->id)
            ->call('save')
            ->assertHasErrors(['faculty_id']);
    }

    public function test_creating_prodi_user_requires_study_program_id_and_prevents_duplicate(): void
    {
        // First without study_program_id should fail
        Livewire::actingAs($this->admin)
            ->test(Users::class)
            ->set('name', 'PIC IF')
            ->set('email', 'pic@if.test')
            ->set('role', UserRole::Prodi->value)
            ->set('password', 'password123')
            ->set('study_program_id', '')
            ->call('save')
            ->assertHasErrors(['study_program_id']);

        // With study_program_id and blank password should succeed and set faculty_id + default password
        Livewire::actingAs($this->admin)
            ->test(Users::class)
            ->set('name', 'PIC IF')
            ->set('email', 'pic@if.test')
            ->set('role', UserRole::Prodi->value)
            ->set('study_program_id', (string) $this->studyProgram->id)
            ->call('save')
            ->assertHasNoErrors();

        $createdProdi = User::where('email', 'pic@if.test')->firstOrFail();
        $this->assertSame(UserRole::Prodi, $createdProdi->role);
        $this->assertSame($this->studyProgram->id, $createdProdi->study_program_id);
        $this->assertSame($this->faculty->id, $createdProdi->faculty_id);
        $this->assertTrue(Hash::check('password', $createdProdi->password));

        // Duplicate prodi user should fail
        Livewire::actingAs($this->admin)
            ->test(Users::class)
            ->set('name', 'PIC IF 2')
            ->set('email', 'pic2@if.test')
            ->set('role', UserRole::Prodi->value)
            ->set('password', 'password123')
            ->set('study_program_id', (string) $this->studyProgram->id)
            ->call('save')
            ->assertHasErrors(['study_program_id']);
    }

    public function test_selecting_faculty_or_study_program_auto_sets_name(): void
    {
        $teknik = Faculty::create([
            'name' => 'TEKNIK',
            'code' => '21',
            'short_name' => 'FT2',
        ]);

        $hukum = StudyProgram::create([
            'name' => 'Hukum S1',
            'code' => '110001',
            'faculty_id' => $this->faculty->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(Users::class)
            ->set('role', UserRole::Fakultas->value)
            ->set('faculty_id', (string) $this->faculty->id)
            ->assertSet('name', 'Fakultas Teknik')
            ->set('faculty_id', (string) $teknik->id)
            ->assertSet('name', 'FAKULTAS TEKNIK')
            ->set('role', UserRole::Prodi->value)
            ->set('study_program_id', (string) $hukum->id)
            ->assertSet('name', 'Prodi Hukum');
    }
}
