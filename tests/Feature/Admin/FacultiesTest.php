<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Livewire\Admin\Faculties;
use App\Models\Faculty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class FacultiesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Faculty $faculty;

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
    }

    public function test_faculties_page_shows_account_status(): void
    {
        // When no account exists
        Livewire::actingAs($this->admin)
            ->test(Faculties::class)
            ->assertSee('Belum Ada Akun')
            ->assertSee('Buat Akun');

        // When account exists
        User::factory()->create([
            'role' => UserRole::Fakultas,
            'faculty_id' => $this->faculty->id,
            'name' => 'Dekanat FT',
            'email' => 'fakultas.ft@sikkn.test',
        ]);

        Livewire::actingAs($this->admin)
            ->test(Faculties::class)
            ->assertSee('Dekanat FT')
            ->assertSee('fakultas.ft@sikkn.test')
            ->assertSee('Kelola Akun');
    }

    public function test_admin_can_create_account_for_faculty_with_default_password(): void
    {
        $teknik = Faculty::create([
            'name' => 'TEKNIK',
            'code' => '21',
            'short_name' => 'FT2',
        ]);

        $vokasi = Faculty::create([
            'name' => 'SEKOLAH VOKASI',
            'code' => '40',
            'short_name' => 'SV',
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test(Faculties::class)
            ->call('manageAccount', $vokasi->id)
            ->assertSet('accountName', 'SEKOLAH VOKASI')
            ->assertSee('Kosongkan untuk password sementara acak')
            ->call('manageAccount', $this->faculty->id)
            ->assertSet('accountName', 'Fakultas Teknik')
            ->call('manageAccount', $teknik->id)
            ->assertSet('accountName', 'FAKULTAS TEKNIK')
            ->set('accountEmail', 'fakultas.ft@univ.ac.id')
            ->call('saveAccount')
            ->assertHasNoErrors()
            ->assertDispatched('toast')
            ->assertSet('showIssuedCredentialModal', true);

        $tempPassword = $component->get('issuedCredential.temporary_password');
        $this->assertNotEmpty($tempPassword);

        $created = User::where('email', 'fakultas.ft@univ.ac.id')->firstOrFail();
        $this->assertSame('FAKULTAS TEKNIK', $created->name);
        $this->assertSame(UserRole::Fakultas, $created->role);
        $this->assertSame($teknik->id, $created->faculty_id);
        $this->assertTrue($created->must_change_password);
        $this->assertTrue(Hash::check($tempPassword, $created->password));
    }

    public function test_faculty_account_validates_minimum_password_length_when_provided(): void
    {
        Livewire::actingAs($this->admin)
            ->test(Faculties::class)
            ->call('manageAccount', $this->faculty->id)
            ->set('accountEmail', 'fakultas.ft@univ.ac.id')
            ->set('accountPassword', '12345')
            ->call('saveAccount')
            ->assertHasErrors(['accountPassword']);
    }

    public function test_admin_can_update_faculty_account_and_password(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Fakultas,
            'faculty_id' => $this->faculty->id,
            'name' => 'Old Name',
            'email' => 'old@ft.test',
            'password' => Hash::make('oldpassword'),
        ]);

        Livewire::actingAs($this->admin)
            ->test(Faculties::class)
            ->call('manageAccount', $this->faculty->id)
            ->assertSet('accountName', 'Old Name')
            ->assertSet('accountEmail', 'old@ft.test')
            ->assertSee('Kosongkan jika tidak ingin mengubah')
            ->set('accountName', 'Updated Name Only')
            ->call('saveAccount')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertEquals('Updated Name Only', $user->name);
        $this->assertTrue(Hash::check('oldpassword', $user->password));

        Livewire::actingAs($this->admin)
            ->test(Faculties::class)
            ->call('manageAccount', $this->faculty->id)
            ->set('accountName', 'New Name')
            ->set('accountPassword', 'newpassword123')
            ->call('saveAccount')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_admin_can_delete_faculty_account(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Fakultas,
            'faculty_id' => $this->faculty->id,
            'name' => 'Dekanat FT',
            'email' => 'dekanat@ft.test',
        ]);

        Livewire::actingAs($this->admin)
            ->test(Faculties::class)
            ->call('manageAccount', $this->faculty->id)
            ->call('deleteAccount')
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_admin_can_resolve_duplicate_faculty_accounts(): void
    {
        $user1 = User::factory()->create([
            'role' => UserRole::Fakultas,
            'faculty_id' => $this->faculty->id,
            'email' => 'demo@ft.test',
        ]);

        $user2 = User::factory()->create([
            'role' => UserRole::Fakultas,
            'faculty_id' => $this->faculty->id,
            'email' => 'official@ft.test',
        ]);

        Livewire::actingAs($this->admin)
            ->test(Faculties::class)
            ->call('manageAccount', $this->faculty->id)
            ->assertCount('duplicateAccounts', 2)
            ->call('resolveDuplicateAccount', $user2->id);

        $this->assertDatabaseMissing('users', ['id' => $user1->id]);
        $this->assertDatabaseHas('users', ['id' => $user2->id]);
    }
}
