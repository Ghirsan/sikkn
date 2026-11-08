<?php

use App\Enums\DocumentStatus;
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
        Schema::table('groups', function (Blueprint $table) {
            $table->string('lrk_status')->default(DocumentStatus::Draft->value)->after('background');
            $table->text('lrk_notes')->nullable()->after('lrk_status');
            $table->timestamp('lrk_submitted_at')->nullable()->after('lrk_notes');
            $table->timestamp('lrk_reviewed_at')->nullable()->after('lrk_submitted_at');
        });

        DB::table('groups')->where('is_lrk_locked', true)->update([
            'lrk_status' => DocumentStatus::Approved->value,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['lrk_status', 'lrk_notes', 'lrk_submitted_at', 'lrk_reviewed_at']);
        });
    }
};
