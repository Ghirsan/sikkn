<?php

namespace Tests\Feature\Admin;

use App\Enums\Semester;
use App\Enums\UserRole;
use App\Livewire\Admin\Students;
use App\Models\DailyLog;
use App\Models\Faculty;
use App\Models\Grade;
use App\Models\Group;
use App\Models\MentoringLog;
use App\Models\Period;
use App\Models\Program;
use App\Models\ProgramParticipant;
use App\Models\ProgramType;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class StudentsTest extends TestCase
{
    use RefreshDatabase;

    protected Faculty $faculty;

    protected StudyProgram $studyProgram;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->faculty = Faculty::create([
            'code' => '21',
            'name' => 'TEKNIK',
            'short_name' => 'FT',
        ]);

        $this->studyProgram = StudyProgram::create([
            'code' => '211201',
            'faculty_id' => $this->faculty->id,
            'name' => 'Teknik Komputer S1',
        ]);
    }

    public function test_admin_can_access_students_page(): void
    {
        $admin = User::factory()->p2kkn()->create();

        $response = $this->actingAs($admin)->get(route('admin.students.index'));

        $response->assertStatus(200);
        $response->assertSeeLivewire(Students::class);
    }

    public function test_non_admin_cannot_access_students_page(): void
    {
        $student = User::factory()->mahasiswa()->create();

        $this->actingAs($student)->get(route('admin.students.index'))->assertStatus(403);
    }

    public function test_admin_can_paginate_students(): void
    {
        $admin = User::factory()->p2kkn()->create();

        User::factory()->count(25)->mahasiswa()->create();

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->assertSet('paginators.page', 1)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->assertStatus(200);
    }

    public function test_searching_resets_pagination_page(): void
    {
        $admin = User::factory()->p2kkn()->create();

        User::factory()->count(25)->mahasiswa()->create();

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('search', 'Test')
            ->assertSet('paginators.page', 1);
    }

    public function test_changing_period_resets_pagination_page(): void
    {
        $admin = User::factory()->p2kkn()->create();

        User::factory()->count(25)->mahasiswa()->create();

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('gotoPage', 2)
            ->assertSet('paginators.page', 2)
            ->set('periodId', 999)
            ->assertSet('paginators.page', 1);
    }

    public function test_admin_can_open_create_student_modal(): void
    {
        $admin = User::factory()->p2kkn()->create();

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('openCreateModal')
            ->assertSet('showCreateModal', true);
    }

    public function test_admin_can_create_student_successfully(): void
    {
        $admin = User::factory()->p2kkn()->create();

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('openCreateModal')
            ->set('nim', '21120120120001')
            ->set('name', 'Budi Santoso')
            ->set('phone', '08123456789')
            ->call('createStudent')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);

        $this->assertDatabaseHas('users', [
            'nim' => '21120120120001',
            'name' => 'Budi Santoso',
            'role' => UserRole::Mahasiswa->value,
            'email' => '21120120120001@student.sikkn.ac.id',
            'study_program_id' => $this->studyProgram->id,
            'faculty_id' => $this->faculty->id,
        ]);

        $createdStudent = User::where('nim', '21120120120001')->first();
        $this->assertNotNull($createdStudent->ulid);
        $this->assertEquals(26, strlen($createdStudent->ulid));
    }

    public function test_nim_input_auto_fills_readonly_prodi_and_fakultas_and_rejects_invalid_nim(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $fsm = Faculty::create([
            'code' => '24',
            'name' => 'SAINS DAN MATEMATIKA',
            'short_name' => 'FSM',
        ]);
        $informatika = StudyProgram::create([
            'code' => '240601',
            'faculty_id' => $fsm->id,
            'name' => 'Informatika S1',
        ]);

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('openCreateModal')
            ->assertSeeHtml('wire:model="resolvedProdiName"')
            ->assertSeeHtml('wire:model="resolvedFacultyName"')
            ->assertDontSeeHtml('wire:model="study_program_id"')
            ->set('nim', '24060120120001')
            ->assertSet('study_program_id', $informatika->id)
            ->assertSet('faculty_id', $fsm->id)
            ->assertSet('resolvedProdiName', 'Informatika S1')
            ->assertSet('resolvedFacultyName', 'SAINS DAN MATEMATIKA')
            ->set('nim', '99999920120001')
            ->assertSet('study_program_id', null)
            ->assertSet('faculty_id', null)
            ->assertSet('resolvedProdiName', '')
            ->assertSet('resolvedFacultyName', '')
            ->assertHasErrors(['nim']);

        // Rejects NIM shorter or longer than 14 digits
        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('openCreateModal')
            ->set('nim', '211201201200')
            ->set('name', 'NIM Pendek')
            ->call('createStudent')
            ->assertHasErrors(['nim']);

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('openCreateModal')
            ->set('nim', '211201201200019')
            ->set('name', 'NIM Panjang')
            ->call('createStudent')
            ->assertHasErrors(['nim']);

        // Rejects 14-digit NIM whose 6-digit prefix does not match any Prodi/Fakultas
        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('openCreateModal')
            ->set('nim', '99999920120001')
            ->set('name', 'NIM Tidak Terdaftar')
            ->call('createStudent')
            ->assertHasErrors(['nim']);

        // Even if study_program_id is tampered with, save derives Prodi & Fakultas strictly from NIM
        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('openCreateModal')
            ->set('nim', '24060120120055')
            ->set('study_program_id', $this->studyProgram->id)
            ->set('name', 'Siti Aminah')
            ->call('createStudent')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'nim' => '24060120120055',
            'study_program_id' => $informatika->id,
            'faculty_id' => $fsm->id,
        ]);
    }

    public function test_create_student_validates_required_fields(): void
    {
        $admin = User::factory()->p2kkn()->create();

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('openCreateModal')
            ->set('nim', '')
            ->set('name', '')
            ->call('createStudent')
            ->assertHasErrors(['nim', 'name']);
    }

    public function test_create_student_rejects_duplicate_nim(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $student = User::factory()->mahasiswa()->create([
            'nim' => '21120120120002',
            'name' => 'Existing Student',
        ]);

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('openCreateModal')
            ->set('nim', '21120120120002')
            ->set('name', 'Duplicate Attempt')
            ->call('createStudent')
            ->assertHasErrors(['nim']);

        $this->assertSame(1, User::where('nim', '21120120120002')->count());
        $this->assertSame('Existing Student', $student->fresh()->name);
    }

    public function test_create_and_edit_student_validates_numeric_nim_and_preserves_leading_zeros(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $zeroFaculty = Faculty::create([
            'code' => '00',
            'name' => 'FAKULTAS UJI',
            'short_name' => 'FU',
        ]);
        $zeroProdi = StudyProgram::create([
            'code' => '001201',
            'faculty_id' => $zeroFaculty->id,
            'name' => 'Prodi Uji S1',
        ]);

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('openCreateModal')
            ->set('nim', 'ABC211201')
            ->set('name', 'Invalid NIM')
            ->call('createStudent')
            ->assertHasErrors(['nim']);

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('openCreateModal')
            ->set('nim', '2112-001')
            ->set('name', 'Invalid Dash NIM')
            ->call('createStudent')
            ->assertHasErrors(['nim']);

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('openCreateModal')
            ->set('nim', '00120120120001')
            ->set('name', 'Leading Zero Student')
            ->call('createStudent')
            ->assertHasNoErrors();

        $student = User::where('nim', '00120120120001')->firstOrFail();
        $this->assertSame('00120120120001', $student->nim);
        $this->assertSame($zeroProdi->id, $student->study_program_id);
        $this->assertSame($zeroFaculty->id, $student->faculty_id);

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('openEditModal', $student->id)
            ->set('nim', '0012012012000X')
            ->call('saveStudent')
            ->assertHasErrors(['nim']);
    }

    public function test_unassigned_students_remain_visible_under_every_period_filter(): void
    {
        $admin = User::factory()->p2kkn()->create();

        $period1 = Period::create([
            'name' => 'Periode 1',
            'semester' => Semester::Ganjil->value,
            'year' => 2025,
            'start_date' => '2025-07-01',
            'end_date' => '2025-08-15',
        ]);

        $period2 = Period::create([
            'name' => 'Periode 2',
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-07-01',
            'end_date' => '2026-08-15',
        ]);

        $group1 = Group::create([
            'period_id' => $period1->id,
            'name' => 'Kelompok P1',
            'village' => 'Desa 1',
        ]);

        $group2 = Group::create([
            'period_id' => $period2->id,
            'name' => 'Kelompok P2',
            'village' => 'Desa 2',
        ]);

        User::factory()->mahasiswa()->create([
            'nim' => '24060120120001',
            'name' => 'Mahasiswa Periode Satu',
            'group_id' => $group1->id,
        ]);

        User::factory()->mahasiswa()->create([
            'nim' => '24060120120002',
            'name' => 'Mahasiswa Periode Dua',
            'group_id' => $group2->id,
        ]);

        User::factory()->mahasiswa()->create([
            'nim' => '24060120120003',
            'name' => 'Mahasiswa Tanpa Kelompok',
            'group_id' => null,
        ]);

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->set('periodId', $period2->id)
            ->assertSee('Mahasiswa Periode Dua')
            ->assertSee('Mahasiswa Tanpa Kelompok')
            ->assertDontSee('Mahasiswa Periode Satu')
            ->assertViewHas('stats', [
                'total' => 2,
                'assigned' => 1,
                'unassigned' => 1,
            ]);
    }

    public function test_database_enforces_unique_nim_while_allowing_null_or_blank(): void
    {
        $userA = User::factory()->p2kkn()->create(['nim' => '']);
        $userB = User::factory()->p2kkn()->create(['nim' => null]);

        $this->assertNull($userA->fresh()->nim);
        $this->assertNull($userB->fresh()->nim);

        User::factory()->mahasiswa()->create(['nim' => '24060120129999']);

        $this->expectException(QueryException::class);
        User::factory()->mahasiswa()->create(['nim' => '24060120129999']);
    }

    public function test_admin_can_edit_student_via_modal(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $student = User::factory()->mahasiswa()->create([
            'nim' => '21120120120003',
            'name' => 'Original Name',
            'phone' => '0811111111',
        ]);

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('openEditModal', $student->id)
            ->assertSet('isEditing', true)
            ->assertSet('editingStudentId', $student->id)
            ->assertSet('nim', '21120120120003')
            ->assertSet('name', 'Original Name')
            ->assertSet('phone', '0811111111')
            ->set('name', 'New Student Name')
            ->set('phone', '0822222222')
            ->call('saveStudent')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false)
            ->assertSet('isEditing', false)
            ->assertSet('editingStudentId', null);

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'name' => 'New Student Name',
            'phone' => '0822222222',
        ]);
    }

    public function test_edit_student_validates_duplicate_nim(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $studentA = User::factory()->mahasiswa()->create(['nim' => '21120120120011']);
        $studentB = User::factory()->mahasiswa()->create(['nim' => '21120120120022']);

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('openEditModal', $studentA->id)
            ->set('nim', '21120120120022')
            ->call('saveStudent')
            ->assertHasErrors(['nim']);
    }

    public function test_unassigning_or_moving_student_clears_previous_group_leader(): void
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
            'name' => 'Kelompok A',
            'village' => 'Desa A',
        ]);

        $student = User::factory()->mahasiswa()->create([
            'nim' => '21120120120099',
            'group_id' => $groupA->id,
        ]);

        $groupA->update(['student_leader_id' => $student->id]);

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('openEditModal', $student->id)
            ->set('group_id', null)
            ->call('saveStudent')
            ->assertHasNoErrors();

        $this->assertNull($student->fresh()->group_id);
        $this->assertNull($groupA->fresh()->student_leader_id);
    }

    public function test_admin_can_delete_student_without_activity_records_and_clears_group_leader(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
        ]);

        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok Uji',
            'village' => 'Desa Uji',
        ]);

        $student = User::factory()->mahasiswa()->create([
            'group_id' => $group->id,
        ]);

        $group->update(['student_leader_id' => $student->id]);

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->assertSeeHtml('aria-disabled="false"')
            ->call('confirmDelete', $student->id)
            ->assertSet('deletingStudentId', $student->id)
            ->assertDispatched('modal-show', name: 'delete-student-modal')
            ->assertNotDispatched('toast-show')
            ->call('delete')
            ->assertDispatched('toast-show')
            ->assertDispatched('modal-close', name: 'delete-student-modal');

        $this->assertDatabaseMissing('users', ['id' => $student->id]);
        $this->assertNull($group->fresh()->student_leader_id);
    }

    public function test_admin_cannot_delete_student_with_activity_records(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $dpl = User::factory()->dpl()->create();
        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
        ]);
        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok Aktif',
            'village' => 'Desa Aktif',
        ]);

        $studentWithLog = User::factory()->mahasiswa()->create(['group_id' => $group->id]);
        DailyLog::create([
            'student_id' => $studentWithLog->id,
            'date' => '2026-01-10',
        ]);

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->assertSeeHtml('aria-disabled="true"')
            ->assertSeeHtml('opacity-50')
            ->call('confirmDelete', $studentWithLog->id)
            ->assertDispatched('toast-show')
            ->assertNotDispatched('modal-show')
            ->assertSet('deletingStudentId', null);

        $this->assertDatabaseHas('users', ['id' => $studentWithLog->id]);

        $studentWithProgram = User::factory()->mahasiswa()->create(['group_id' => $group->id]);
        $programType = ProgramType::firstOrCreate(
            ['code' => 'multidisiplin'],
            ['name' => 'Multidisiplin']
        );
        $program = Program::create([
            'group_id' => $group->id,
            'program_type_id' => $programType->id,
            'title' => 'Program Multidisiplin',
        ]);
        ProgramParticipant::create([
            'program_id' => $program->id,
            'student_id' => $studentWithProgram->id,
        ]);

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('confirmDelete', $studentWithProgram->id)
            ->assertDispatched('toast-show')
            ->assertNotDispatched('modal-show');

        $this->assertDatabaseHas('users', ['id' => $studentWithProgram->id]);

        $studentWithMentoringAndGrade = User::factory()->mahasiswa()->create(['group_id' => $group->id]);
        MentoringLog::create([
            'group_id' => $group->id,
            'student_id' => $studentWithMentoringAndGrade->id,
            'date' => '2026-01-11',
            'topic' => 'Konsultasi',
            'discussion_summary' => 'Progres awal',
        ]);
        Grade::create([
            'student_id' => $studentWithMentoringAndGrade->id,
            'dpl_id' => $dpl->id,
            'group_id' => $group->id,
        ]);

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('confirmDelete', $studentWithMentoringAndGrade->id)
            ->assertDispatched('toast-show')
            ->assertNotDispatched('modal-show');

        $this->assertDatabaseHas('users', ['id' => $studentWithMentoringAndGrade->id]);
    }

    public function test_creating_student_without_password_issues_temporary_password_and_allows_reset(): void
    {
        $admin = User::factory()->p2kkn()->create();

        $component = Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('openCreateModal')
            ->assertSeeHtml('wire:model="password"')
            ->set('nim', '21120120120088')
            ->set('name', 'Mahasiswa Baru')
            ->call('createStudent')
            ->assertHasNoErrors()
            ->assertSet('showIssuedCredentialModal', true);

        $tempPassword = $component->get('issuedCredential.temporary_password');
        $this->assertNotEmpty($tempPassword);

        $student = User::where('nim', '21120120120088')->firstOrFail();
        $this->assertTrue($student->must_change_password);
        $this->assertTrue(Hash::check($tempPassword, $student->password));

        $student->update(['must_change_password' => false]);

        $resetComponent = Livewire::actingAs($admin)
            ->test(Students::class)
            ->assertDontSeeHtml('resetPassword('.$student->id.')')
            ->call('openEditModal', $student->id)
            ->assertDontSeeHtml('wire:model="password"')
            ->assertSee('Reset Password Sementara')
            ->call('resetPassword')
            ->assertSet('showCreateModal', false)
            ->assertSet('showIssuedCredentialModal', true)
            ->assertDontSee('@js(')
            ->assertSeeHtml('x-ref="tempPassword"')
            ->assertSeeHtml('copyPassword()');

        $resetPassword = $resetComponent->get('issuedCredential.temporary_password');
        $this->assertNotEmpty($resetPassword);

        $student->refresh();
        $this->assertTrue($student->must_change_password);
        $this->assertTrue(Hash::check($resetPassword, $student->password));
    }
}
