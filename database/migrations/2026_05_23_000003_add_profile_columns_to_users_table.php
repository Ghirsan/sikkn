<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nim')->nullable()->after('name');
            $table->string('nip')->nullable()->after('nim');
            $table->string('prodi')->nullable()->after('nip');
            $table->string('fakultas')->nullable()->after('prodi');
            $table->foreignId('group_id')->nullable()->after('fakultas')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_id');
            $table->dropColumn(['nim', 'nip', 'prodi', 'fakultas']);
        });
    }
};
