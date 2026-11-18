<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\DailyLog;
use App\Models\Faculty;
use App\Models\Grade;
use App\Models\GradeWeight;
use App\Models\Group;
use App\Models\MentoringLog;
use App\Models\Period;
use App\Models\Program;
use App\Models\StudyProgram;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_consolidated_migrations_include_current_schema_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('users', [
            'ulid',
            'must_change_password',
            'nim',
            'nip',
            'emergency_name',
            'emergency_relationship',
        ]));

        $this->assertTrue(Schema::hasColumns('groups', [
            'ulid',
            'lrk_status',
            'lrk_notes',
            'lrk_submitted_at',
            'lrk_reviewed_at',
            'storyboard_scenes',
            'video_script_scenes',
            'macro_schedules',
        ]));

        foreach (['programs', 'program_participants', 'daily_logs', 'mentoring_logs'] as $table) {
            $this->assertTrue(Schema::hasColumn($table, 'ulid'), "Missing ulid column on {$table}");
        }

        $this->assertTrue(Schema::hasIndex('users', ['nim'], 'unique'));
        $this->assertTrue(Schema::hasIndex('users', ['nip'], 'unique'));
        $this->assertTrue(Schema::hasIndex('program_participants', ['participant_code']));
        $this->assertFalse(Schema::hasIndex('program_participants', ['participant_code'], 'unique'));
    }

    public function test_database_seeder_seeds_only_master_data_and_admin_faculty_prodi_users(): void
    {
        $this->seed(DatabaseSeeder::class);

        $facultyCount = Faculty::count();
        $studyProgramCount = StudyProgram::count();

        $this->assertSame(12, $facultyCount);
        $this->assertSame(66, $studyProgramCount);
        $this->assertSame(6, GradeWeight::count());

        $this->assertSame(1, User::where('role', UserRole::P2kkn)->count());
        $this->assertSame($facultyCount, User::where('role', UserRole::Fakultas)->count());
        $this->assertSame($studyProgramCount, User::where('role', UserRole::Prodi)->count());
        $this->assertSame(0, User::where('role', UserRole::Dpl)->count());
        $this->assertSame(0, User::where('role', UserRole::Mahasiswa)->count());

        $this->assertSame(0, Period::count());
        $this->assertSame(0, Group::count());
        $this->assertSame(0, Program::count());
        $this->assertSame(0, DailyLog::count());
        $this->assertSame(0, MentoringLog::count());
        $this->assertSame(0, Grade::count());

        $admin = User::where('email', 'admin@sikkn.test')->firstOrFail();
        $this->assertSame(UserRole::P2kkn, $admin->role);
        $this->assertNotNull($admin->ulid);
        $this->assertFalse($admin->must_change_password);
        $this->assertTrue(Hash::check('password', $admin->password));

        Faculty::withCount('observerUsers')->get()->each(function (Faculty $faculty) {
            $this->assertSame(1, $faculty->observer_users_count);

            $slug = Str::slug($faculty->short_name ?: $faculty->code);
            $user = $faculty->observerUser;
            $this->assertNotNull($user);
            $this->assertSame("fakultas.{$slug}@sikkn.test", $user->email);
            $this->assertNotNull($user->ulid);
        });

        StudyProgram::withCount('observerUsers')->get()->each(function (StudyProgram $studyProgram) {
            $this->assertSame(1, $studyProgram->observer_users_count);

            $code = Str::lower(trim($studyProgram->code));
            $user = $studyProgram->observerUser;
            $this->assertNotNull($user);
            $this->assertSame("prodi.{$code}@sikkn.test", $user->email);
            $this->assertSame($studyProgram->faculty_id, $user->faculty_id);
            $this->assertNotNull($user->ulid);
        });
    }

    public function test_database_seeder_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1 + 12 + 66, User::count());
        $this->assertSame(12, Faculty::count());
        $this->assertSame(66, StudyProgram::count());
    }
}
