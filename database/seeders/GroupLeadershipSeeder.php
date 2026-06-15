<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Group;
use Illuminate\Database\Seeder;

class GroupLeadershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = Group::with(['dpls', 'students'])->get();

        foreach ($groups as $group) {
            // Assign the first DPL as the Lead DPL if there is any
            $leadDpl = $group->dpls->first();
            
            // Assign the first student as the Student Leader if there is any
            $studentLeader = $group->students->first();

            if ($leadDpl || $studentLeader) {
                $group->update([
                    'lead_dpl_id' => $leadDpl?->id,
                    'student_leader_id' => $studentLeader?->id,
                ]);
            }
        }
    }
}
