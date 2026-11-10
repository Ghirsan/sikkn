<?php

namespace Tests\Feature\Admin;

use App\Enums\Semester;
use App\Enums\UserRole;
use App\Livewire\Admin\Students;
use App\Models\Group;
use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StudentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
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
        ]);

        $createdStudent = User::where('nim', '21120120120001')->first();
        $this->assertNotNull($createdStudent->ulid);
        $this->assertEquals(26, strlen($createdStudent->ulid));
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

    public function test_create_student_updates_existing_student_allocation(): void
    {
        $admin = User::factory()->p2kkn()->create();
        $student = User::factory()->mahasiswa()->create([
            'nim' => '21120120120002',
            'name' => 'Old Name',
        ]);

        $period = Period::create([
            'semester' => Semester::Ganjil->value,
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-02-01',
        ]);

        $group = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok Test',
            'village' => 'Desa Test',
        ]);

        Livewire::actingAs($admin)
            ->test(Students::class)
            ->call('openCreateModal')
            ->set('nim', '21120120120002')
            ->set('name', 'Updated Name')
            ->set('group_id', $group->id)
            ->call('createStudent')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'name' => 'Updated Name',
            'group_id' => $group->id,
        ]);
    }
}
