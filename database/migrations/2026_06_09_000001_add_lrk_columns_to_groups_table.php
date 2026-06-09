<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->string('partner_name')->nullable()->after('province');
            $table->string('village_head')->nullable()->after('partner_name');
            $table->text('background')->nullable()->after('village_head');
            $table->string('location_map_path')->nullable()->after('background');
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['partner_name', 'village_head', 'background', 'location_map_path']);
        });
    }
};
