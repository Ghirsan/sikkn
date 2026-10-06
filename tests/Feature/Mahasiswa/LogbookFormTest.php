<?php

namespace Tests\Feature\Mahasiswa;

use App\Enums\UserRole;
use App\Livewire\Mahasiswa\LogbookForm;
use App\Models\User;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Models\DailyLog;

class LogbookFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_save_logbook_with_external_image_url()
    {
        $period = \App\Models\Period::create([
            'name' => 'Period 1',
            'semester' => 'Ganjil',
            'year' => '2023/2024',
            'start_date' => Carbon::today()->subDays(5),
            'end_date' => Carbon::today()->addDays(5),
            'is_active' => true,
        ]);
        $group = Group::create([
            'name' => 'Group 1',
            'period_id' => $period->id,
            'start_date' => Carbon::today()->subDays(5),
            'end_date' => Carbon::today()->addDays(5),
        ]);
        
        $student = User::factory()->create([
            'role' => UserRole::Mahasiswa,
            'group_id' => $group->id,
        ]);

        $this->actingAs($student);

        Livewire::test(LogbookForm::class)
            ->set('date', Carbon::today()->format('Y-m-d'))
            ->set('importantNotes', 'Notes with image')
            ->set('imageUrl', 'https://drive.google.com/file/d/1234567890abcdef/view')
            ->set('activities', [
                [
                    'start_time' => '08:00',
                    'end_time' => '10:00',
                    'activity_description' => 'Test',
                ]
            ])
            ->call('saveDraft')
            ->assertHasNoErrors()
            ->assertRedirect(route('logbook.index'));

        $this->assertDatabaseHas('daily_logs', [
            'student_id' => $student->id,
            'image_path' => 'https://drive.google.com/file/d/1234567890abcdef/view',
        ]);
        
        $log = DailyLog::where('student_id', $student->id)->first();
        $this->assertEquals('https://drive.google.com/thumbnail?id=1234567890abcdef', $log->image_url);
    }

    public function test_fails_validation_for_invalid_external_image_url()
    {
        $period = \App\Models\Period::create([
            'name' => 'Period 1',
            'semester' => 'Ganjil',
            'year' => '2023/2024',
            'start_date' => Carbon::today()->subDays(5),
            'end_date' => Carbon::today()->addDays(5),
            'is_active' => true,
        ]);
        $group = Group::create([
            'name' => 'Group 1',
            'period_id' => $period->id,
            'start_date' => Carbon::today()->subDays(5),
            'end_date' => Carbon::today()->addDays(5),
        ]);

        
        $student = User::factory()->create([
            'role' => UserRole::Mahasiswa,
            'group_id' => $group->id,
        ]);

        $this->actingAs($student);

        Livewire::test(LogbookForm::class)
            ->set('imageUrl', 'https://invalid-domain.com/image.jpg')
            ->call('verifyImageUrl')
            ->assertHasErrors(['imageUrl']);
    }
}
