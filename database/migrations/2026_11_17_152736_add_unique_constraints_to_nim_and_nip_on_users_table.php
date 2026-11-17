<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')->where('nim', '')->update(['nim' => null]);
        DB::table('users')->where('nip', '')->update(['nip' => null]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['nim']);
            $table->dropIndex(['nip']);
            $table->unique('nim');
            $table->unique('nip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nim']);
            $table->dropUnique(['nip']);
            $table->index('nim');
            $table->index('nip');
        });
    }
};
