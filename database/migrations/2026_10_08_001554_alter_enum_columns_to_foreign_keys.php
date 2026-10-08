<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Seed data
        DB::table('group_types')->insertOrIgnore([
            ['name' => 'Reguler', 'code' => 'Reguler'],
            ['name' => 'Tematik', 'code' => 'Tematik'],
        ]);

        DB::table('program_types')->insertOrIgnore([
            ['name' => 'Multidisiplin', 'code' => 'multidisiplin'],
            ['name' => 'Sosial Kemasyarakatan', 'code' => 'sosial_kemasyarakatan'],
            ['name' => 'Lainnya', 'code' => 'lainnya'],
        ]);

        $sdgs = [
            1 => '1. No Poverty',
            2 => '2. Zero Hunger',
            3 => '3. Good Health and Well-being',
            4 => '4. Quality Education',
            5 => '5. Gender Equality',
            6 => '6. Clean Water and Sanitation',
            7 => '7. Affordable and Clean Energy',
            8 => '8. Decent Work and Economic Growth',
            9 => '9. Industry, Innovation and Infrastructure',
            10 => '10. Reduced Inequalities',
            11 => '11. Sustainable Cities and Communities',
            12 => '12. Responsible Consumption and Production',
            13 => '13. Climate Action',
            14 => '14. Life Below Water',
            15 => '15. Life on Land',
            16 => '16. Peace, Justice and Strong Institutions',
            17 => '17. Partnerships for the Goals',
        ];
        foreach ($sdgs as $code => $name) {
            DB::table('sdg_categories')->insertOrIgnore(['code' => $code, 'name' => $name]);
        }

        // 2. Add columns
        Schema::table('groups', function (Blueprint $table) {
            $table->foreignId('group_type_id')->nullable()->constrained('group_types')->nullOnDelete();
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->foreignId('program_type_id')->nullable()->constrained('program_types')->nullOnDelete();
        });

        Schema::table('program_participants', function (Blueprint $table) {
            $table->foreignId('sdg_category_id')->nullable()->constrained('sdg_categories')->nullOnDelete();
        });

        // 3. Migrate data
        $groups = DB::table('groups')->get();
        foreach ($groups as $group) {
            $type = DB::table('group_types')->where('code', $group->type)->first();
            if ($type) {
                DB::table('groups')->where('id', $group->id)->update(['group_type_id' => $type->id]);
            }
        }

        $programs = DB::table('programs')->get();
        foreach ($programs as $program) {
            $type = DB::table('program_types')->where('code', $program->type)->first();
            if ($type) {
                DB::table('programs')->where('id', $program->id)->update(['program_type_id' => $type->id]);
            }
        }

        $participants = DB::table('program_participants')->whereNotNull('sdg_category')->get();
        foreach ($participants as $participant) {
            $sdg = DB::table('sdg_categories')->where('code', $participant->sdg_category)->first();
            if ($sdg) {
                DB::table('program_participants')->where('id', $participant->id)->update(['sdg_category_id' => $sdg->id]);
            }
        }

        // 4. Drop old columns
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('program_participants', function (Blueprint $table) {
            $table->dropColumn('sdg_category');
        });
    }

    public function down(): void
    {
        // Revert columns
        Schema::table('groups', function (Blueprint $table) {
            $table->string('type')->default('Reguler');
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->string('type')->default('sosial_kemasyarakatan');
        });

        Schema::table('program_participants', function (Blueprint $table) {
            $table->unsignedTinyInteger('sdg_category')->nullable();
        });

        // Revert data
        $groups = DB::table('groups')->get();
        foreach ($groups as $group) {
            if ($group->group_type_id) {
                $type = DB::table('group_types')->where('id', $group->group_type_id)->first();
                if ($type) {
                    DB::table('groups')->where('id', $group->id)->update(['type' => $type->code]);
                }
            }
        }

        $programs = DB::table('programs')->get();
        foreach ($programs as $program) {
            if ($program->program_type_id) {
                $type = DB::table('program_types')->where('id', $program->program_type_id)->first();
                if ($type) {
                    DB::table('programs')->where('id', $program->id)->update(['type' => $type->code]);
                }
            }
        }

        $participants = DB::table('program_participants')->get();
        foreach ($participants as $participant) {
            if ($participant->sdg_category_id) {
                $sdg = DB::table('sdg_categories')->where('id', $participant->sdg_category_id)->first();
                if ($sdg) {
                    DB::table('program_participants')->where('id', $participant->id)->update(['sdg_category' => $sdg->code]);
                }
            }
        }

        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['group_type_id']);
            $table->dropColumn('group_type_id');
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->dropForeign(['program_type_id']);
            $table->dropColumn('program_type_id');
        });

        Schema::table('program_participants', function (Blueprint $table) {
            $table->dropForeign(['sdg_category_id']);
            $table->dropColumn('sdg_category_id');
        });
    }
};
