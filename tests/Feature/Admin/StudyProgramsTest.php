<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Livewire\Admin\StudyPrograms;
use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class StudyProgramsTest extends TestCase
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

    public function test_study_programs_page_shows_account_status(): void
    {
        // When no account exists
        Livewire::actingAs($this->admin)
            ->test(StudyPrograms::class)
            ->assertSee('Belum Ada Akun')
            ->assertSee('Buat Akun');

        // When account exists
        User::factory()->create([
            'role' => UserRole::Prodi,
            'study_program_id' => $this->studyProgram->id,
            'faculty_id' => $this->faculty->id,
            'name' => 'Kaprodi IF',
            'email' => 'prodi.if@sikkn.test',
        ]);

        Livewire::actingAs($this->admin)
            ->test(StudyPrograms::class)
            ->assertSee('Kaprodi IF')
            ->assertSee('prodi.if@sikkn.test')
            ->assertSee('Kelola Akun');
    }

    public function test_admin_can_create_account_for_study_program_with_default_password(): void
    {
        $hukum = StudyProgram::create([
            'name' => 'Hukum S1',
            'code' => '110001',
            'faculty_id' => $this->faculty->id,
        ]);

        $prefixed = StudyProgram::create([
            'name' => 'Prodi Informatika S1',
            'code' => '240602',
            'faculty_id' => $this->faculty->id,
        ]);

        $component = Livewire::actingAs($this->admin)
            ->test(StudyPrograms::class)
            ->call('manageAccount', $prefixed->id)
            ->assertSet('accountName', 'Prodi Informatika')
            ->assertSee('Kosongkan untuk password sementara acak')
            ->call('manageAccount', $hukum->id)
            ->assertSet('accountName', 'Prodi Hukum')
            ->set('accountEmail', 'prodi.hukum@univ.ac.id')
            ->call('saveAccount')
            ->assertHasNoErrors()
            ->assertDispatched('toast')
            ->assertSet('showIssuedCredentialModal', true);

        $tempPassword = $component->get('issuedCredential.temporary_password');
        $this->assertNotEmpty($tempPassword);

        $created = User::where('email', 'prodi.hukum@univ.ac.id')->firstOrFail();
        $this->assertSame('Prodi Hukum', $created->name);
        $this->assertSame(UserRole::Prodi, $created->role);
        $this->assertSame($hukum->id, $created->study_program_id);
        $this->assertSame($this->faculty->id, $created->faculty_id);
        $this->assertTrue($created->must_change_password);
        $this->assertTrue(Hash::check($tempPassword, $created->password));
    }

    public function test_study_program_account_validates_minimum_password_length_when_provided(): void
    {
        Livewire::actingAs($this->admin)
            ->test(StudyPrograms::class)
            ->call('manageAccount', $this->studyProgram->id)
            ->set('accountEmail', 'prodi.if@univ.ac.id')
            ->set('accountPassword', '12345')
            ->call('saveAccount')
            ->assertHasErrors(['accountPassword']);
    }

    public function test_admin_can_update_study_program_account_and_password(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Prodi,
            'study_program_id' => $this->studyProgram->id,
            'faculty_id' => $this->faculty->id,
            'name' => 'Prodi Old Kaprodi',
            'email' => 'old@if.test',
            'password' => Hash::make('oldpassword'),
        ]);

        Livewire::actingAs($this->admin)
            ->test(StudyPrograms::class)
            ->call('manageAccount', $this->studyProgram->id)
            ->assertSet('accountName', 'Prodi Old Kaprodi')
            ->assertSet('accountEmail', 'old@if.test')
            ->assertSee('Kosongkan jika tidak ingin mengubah')
            ->set('accountName', 'Prodi Updated Name')
            ->call('saveAccount')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertEquals('Prodi Updated Name', $user->name);
        $this->assertTrue(Hash::check('oldpassword', $user->password));

        Livewire::actingAs($this->admin)
            ->test(StudyPrograms::class)
            ->call('manageAccount', $this->studyProgram->id)
            ->set('accountName', 'New Kaprodi')
            ->set('accountPassword', 'newpassword123')
            ->call('saveAccount')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertEquals('New Kaprodi', $user->name);
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_admin_can_delete_study_program_account(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Prodi,
            'study_program_id' => $this->studyProgram->id,
            'faculty_id' => $this->faculty->id,
            'name' => 'Kaprodi IF',
            'email' => 'kaprodi@if.test',
        ]);

        Livewire::actingAs($this->admin)
            ->test(StudyPrograms::class)
            ->call('manageAccount', $this->studyProgram->id)
            ->call('deleteAccount')
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_admin_can_resolve_duplicate_study_program_accounts(): void
    {
        $user1 = User::factory()->create([
            'role' => UserRole::Prodi,
            'study_program_id' => $this->studyProgram->id,
            'faculty_id' => $this->faculty->id,
            'email' => 'demo@if.test',
        ]);

        $user2 = User::factory()->create([
            'role' => UserRole::Prodi,
            'study_program_id' => $this->studyProgram->id,
            'faculty_id' => $this->faculty->id,
            'email' => 'official@if.test',
        ]);

        Livewire::actingAs($this->admin)
            ->test(StudyPrograms::class)
            ->call('manageAccount', $this->studyProgram->id)
            ->assertCount('duplicateAccounts', 2)
            ->call('resolveDuplicateAccount', $user2->id);

        $this->assertDatabaseMissing('users', ['id' => $user1->id]);
        $this->assertDatabaseHas('users', ['id' => $user2->id]);
    }
}
