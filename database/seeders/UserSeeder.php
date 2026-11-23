<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Seed baseline admin, faculty, and study program accounts.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $now = now();
            $defaultPassword = Hash::make('password');

            User::updateOrCreate(
                ['email' => 'admin@sikkn.test'],
                [
                    'name' => 'Admin P2KKN',
                    'email_verified_at' => $now,
                    'password' => $defaultPassword,
                    'must_change_password' => false,
                    'role' => UserRole::P2kkn,
                    'faculty_id' => null,
                    'study_program_id' => null,
                    'group_id' => null,
                ]
            );

            Faculty::query()->orderBy('code')->each(function (Faculty $faculty) use ($defaultPassword, $now) {
                $slug = Str::slug($faculty->short_name ?: $faculty->code);

                User::updateOrCreate(
                    ['email' => "fakultas.{$slug}@sikkn.test"],
                    [
                        'name' => $faculty->defaultObserverAccountName(),
                        'email_verified_at' => $now,
                        'password' => $defaultPassword,
                        'must_change_password' => false,
                        'role' => UserRole::Fakultas,
                        'faculty_id' => $faculty->id,
                        'study_program_id' => null,
                        'group_id' => null,
                    ]
                );
            });

            StudyProgram::query()->orderBy('code')->each(function (StudyProgram $studyProgram) use ($defaultPassword, $now) {
                $code = Str::lower(trim($studyProgram->code));

                User::updateOrCreate(
                    ['email' => "prodi.{$code}@sikkn.test"],
                    [
                        'name' => $studyProgram->defaultObserverAccountName(),
                        'email_verified_at' => $now,
                        'password' => $defaultPassword,
                        'must_change_password' => false,
                        'role' => UserRole::Prodi,
                        'faculty_id' => $studyProgram->faculty_id,
                        'study_program_id' => $studyProgram->id,
                        'group_id' => null,
                    ]
                );
            });
        });
    }
}
