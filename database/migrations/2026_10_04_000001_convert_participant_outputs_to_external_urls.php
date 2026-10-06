<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participant_outputs', function (Blueprint $table) {
            $table->string('type')->change();
            $table->dropColumn('file_path');
        });

        DB::table('participant_outputs')
            ->where('type', 'file')
            ->delete();

        DB::table('participant_outputs')
            ->where('type', 'link')
            ->get(['id', 'url'])
            ->each(function (object $output): void {
                $type = $this->inferType($output->url);

                DB::table('participant_outputs')
                    ->where('id', $output->id)
                    ->update(['type' => $type ?? 'lainnya']);
            });
    }

    public function down(): void
    {
        Schema::table('participant_outputs', function (Blueprint $table) {
            $table->string('file_path')->nullable();
        });
    }

    private function inferType(?string $value): ?string
    {
        $parts = parse_url(trim((string) $value));
        $host = strtolower($parts['host'] ?? '');
        $extension = strtolower(pathinfo($parts['path'] ?? '', PATHINFO_EXTENSION));

        if (in_array($host, ['youtu.be', 'youtube.com', 'www.youtube.com', 'm.youtube.com', 'vimeo.com', 'www.vimeo.com'], true)) {
            return 'video';
        }

        if ($extension === 'pdf') {
            return 'pdf';
        }

        if (in_array($extension, ['avif', 'gif', 'jpeg', 'jpg', 'png', 'webp'], true)) {
            return 'image';
        }

        if (in_array($extension, ['m4v', 'mov', 'mp4', 'ogv', 'webm'], true)) {
            return 'video';
        }

        return 'lainnya';
    }
};
