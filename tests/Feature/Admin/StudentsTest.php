<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Students;
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
}
