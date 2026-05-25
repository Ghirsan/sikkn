<?php

namespace Database\Seeders;

use App\Enums\LogStatus;
use App\Enums\PeriodStatus;
use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Models\DailyLog;
use App\Models\Group;
use App\Models\MentoringLog;
use App\Models\Period;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;

class KKNSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Period ─────────────────────────────────────────────
        $period = Period::create([
            'semester' => \App\Enums\Semester::Genap,
            'year' => 2026,
            'start_date' => '2026-07-01',
            'end_date' => '2026-08-15',
        ]);

        // ── 2. DPL Users ──────────────────────────────────────────
        $dpl1 = User::where('email', 'dpl@sikkn.test')->first();
        $dpl1->update(['nip' => '197805152005011002', 'prodi' => 'Teknik Informatika', 'fakultas' => 'Fakultas Teknik']);

        $dpl2 = User::factory()->dpl()->create([
            'name' => 'Dr. Siti Aminah, M.Pd.',
            'email' => 'dpl2@sikkn.test',
            'nip' => '198201102008012001',
            'prodi' => 'Pendidikan Matematika',
            'fakultas' => 'Fakultas Keguruan dan Ilmu Pendidikan',
        ]);

        $dpl3 = User::factory()->dpl()->create([
            'name' => 'Ir. Hendra Pratama, M.T.',
            'email' => 'dpl3@sikkn.test',
            'nip' => '196909201998021003',
            'prodi' => 'Teknik Sipil',
            'fakultas' => 'Fakultas Teknik',
        ]);

        // ── 3. Groups ─────────────────────────────────────────────
        $group1 = Group::create([
            'period_id' => $period->id,
            'dpl_id' => $dpl1->id,
            'name' => 'Kelompok 01',
            'type' => \App\Enums\GroupType::Reguler,
            'village' => 'Desa Sukamakmur',
            'district' => 'Kec. Karanganyar',
            'regency' => 'Kab. Karanganyar',
            'province' => 'Jawa Tengah',
        ]);

        $group2 = Group::create([
            'period_id' => $period->id,
            'dpl_id' => $dpl2->id,
            'name' => 'Kelompok 02',
            'type' => \App\Enums\GroupType::Tematik,
            'village' => 'Desa Sidoharjo',
            'district' => 'Kec. Polanharjo',
            'regency' => 'Kab. Klaten',
            'province' => 'Jawa Tengah',
        ]);

        $group3 = Group::create([
            'period_id' => $period->id,
            'dpl_id' => $dpl3->id,
            'name' => 'Kelompok 03',
            'type' => \App\Enums\GroupType::Reguler,
            'village' => 'Desa Sendangtirto',
            'district' => 'Kec. Berbah',
            'regency' => 'Kab. Sleman',
            'province' => 'DI Yogyakarta',
        ]);

        // ── 4. Students ───────────────────────────────────────────
        $studentsData = [
            // Group 1 students (DPL: Dr. Budi Santoso)
            ['name' => 'Andi Mahasiswa', 'email' => 'mahasiswa@sikkn.test', 'nim' => '20260001', 'prodi' => 'Teknik Informatika', 'fakultas' => 'Fakultas Teknik', 'group' => $group1],
            ['name' => 'Rina Permata', 'email' => 'rina@sikkn.test', 'nim' => '20260002', 'prodi' => 'Teknik Informatika', 'fakultas' => 'Fakultas Teknik', 'group' => $group1],
            ['name' => 'Budi Hartono', 'email' => 'budi.h@sikkn.test', 'nim' => '20260003', 'prodi' => 'Sistem Informasi', 'fakultas' => 'Fakultas Teknik', 'group' => $group1],
            ['name' => 'Dewi Sartika', 'email' => 'dewi@sikkn.test', 'nim' => '20260004', 'prodi' => 'Teknik Sipil', 'fakultas' => 'Fakultas Teknik', 'group' => $group1],

            // Group 2 students (DPL: Dr. Siti Aminah)
            ['name' => 'Fajar Nugroho', 'email' => 'fajar@sikkn.test', 'nim' => '20260005', 'prodi' => 'Pendidikan Matematika', 'fakultas' => 'Fakultas Keguruan dan Ilmu Pendidikan', 'group' => $group2],
            ['name' => 'Lestari Wulandari', 'email' => 'lestari@sikkn.test', 'nim' => '20260006', 'prodi' => 'Pendidikan Bahasa Inggris', 'fakultas' => 'Fakultas Keguruan dan Ilmu Pendidikan', 'group' => $group2],
            ['name' => 'Agus Riyanto', 'email' => 'agus@sikkn.test', 'nim' => '20260007', 'prodi' => 'Pendidikan Matematika', 'fakultas' => 'Fakultas Keguruan dan Ilmu Pendidikan', 'group' => $group2],

            // Group 3 students (DPL: Ir. Hendra Pratama)
            ['name' => 'Putri Handayani', 'email' => 'putri@sikkn.test', 'nim' => '20260008', 'prodi' => 'Teknik Sipil', 'fakultas' => 'Fakultas Teknik', 'group' => $group3],
            ['name' => 'Rizky Pratama', 'email' => 'rizky@sikkn.test', 'nim' => '20260009', 'prodi' => 'Teknik Informatika', 'fakultas' => 'Fakultas Teknik', 'group' => $group3],
            ['name' => 'Sinta Dewi', 'email' => 'sinta@sikkn.test', 'nim' => '20260010', 'prodi' => 'Sistem Informasi', 'fakultas' => 'Fakultas Teknik', 'group' => $group3],
        ];

        $students = [];
        foreach ($studentsData as $data) {
            $group = $data['group'];
            unset($data['group']);

            $existing = User::where('email', $data['email'])->first();
            if ($existing) {
                $existing->update([
                    'nim' => $data['nim'],
                    'prodi' => $data['prodi'],
                    'fakultas' => $data['fakultas'],
                    'group_id' => $group->id,
                ]);
                $students[] = $existing;
            } else {
                $students[] = User::factory()->mahasiswa()->create(array_merge($data, [
                    'group_id' => $group->id,
                ]));
            }
        }

        // ── 5. Update Prodi & Fakultas users ──────────────────────
        User::where('email', 'prodi@sikkn.test')->update([
            'prodi' => 'Teknik Informatika',
            'fakultas' => 'Fakultas Teknik',
        ]);

        User::where('email', 'fakultas@sikkn.test')->update([
            'fakultas' => 'Fakultas Teknik',
        ]);

        // ── 6. Programs (varied statuses) ─────────────────────────
        // Group 1 — Andi: approved, Rina: submitted, Budi: needs_revision, Dewi: draft
        Program::create([
            'group_id' => $group1->id,
            'student_id' => $students[0]->id,
            'title' => 'Pelatihan Literasi Digital untuk Warga Desa',
            'type' => ProgramType::PengabdianMasyarakat,
            'target' => 'Meningkatkan kemampuan literasi digital warga desa',
            'target_audience' => 'Warga Desa Sukamakmur usia produktif',
            'budget' => 500000,
            'source_of_fund' => 'Dana Mandiri',
            'method' => 'Workshop dan pendampingan langsung',
            'output_target' => '30 warga mampu menggunakan aplikasi digital dasar',
            'timeline' => 'Minggu ke-1 s/d ke-3',
            'status' => ProgramStatus::Approved,
        ]);

        Program::create([
            'group_id' => $group1->id,
            'student_id' => $students[1]->id,
            'title' => 'Pembuatan Website Profil Desa',
            'type' => ProgramType::Multidisiplin,
            'target' => 'Menyediakan platform informasi desa berbasis web',
            'target_audience' => 'Perangkat Desa dan masyarakat umum',
            'budget' => 300000,
            'source_of_fund' => 'Dana Mandiri',
            'method' => 'Pengembangan website dan pelatihan admin',
            'output_target' => 'Website profil desa aktif dan terkelola',
            'timeline' => 'Minggu ke-1 s/d ke-4',
            'status' => ProgramStatus::Submitted,
        ]);

        Program::create([
            'group_id' => $group1->id,
            'student_id' => $students[2]->id,
            'title' => 'Pemetaan Potensi UMKM Desa',
            'type' => ProgramType::PengabdianMasyarakat,
            'target' => 'Mengidentifikasi dan mendokumentasikan potensi UMKM desa',
            'target_audience' => 'Pelaku UMKM Desa Sukamakmur',
            'budget' => 200000,
            'source_of_fund' => 'Dana Mandiri',
            'method' => 'Survei lapangan dan wawancara',
            'output_target' => 'Dokumen pemetaan UMKM desa',
            'timeline' => 'Minggu ke-2 s/d ke-4',
            'status' => ProgramStatus::NeedsRevision,
            'revision_note' => 'Tambahkan detail metodologi survei dan indikator keberhasilan yang lebih terukur.',
        ]);

        Program::create([
            'group_id' => $group1->id,
            'student_id' => $students[3]->id,
            'title' => 'Perbaikan Saluran Irigasi Persawahan',
            'type' => ProgramType::Multidisiplin,
            'target' => 'Memperbaiki saluran irigasi yang rusak',
            'status' => ProgramStatus::Draft,
        ]);

        // Group 2 — all approved (ready for PDF)
        Program::create([
            'group_id' => $group2->id,
            'student_id' => $students[4]->id,
            'title' => 'Bimbingan Belajar Matematika Anak SD',
            'type' => ProgramType::PengabdianMasyarakat,
            'target' => 'Meningkatkan kemampuan berhitung anak SD',
            'target_audience' => 'Siswa SD kelas 4-6 Desa Sidoharjo',
            'budget' => 250000,
            'source_of_fund' => 'Dana Mandiri',
            'method' => 'Bimbingan belajar kelompok',
            'output_target' => '20 siswa meningkat nilai matematikanya',
            'timeline' => 'Minggu ke-1 s/d ke-6',
            'status' => ProgramStatus::Approved,
        ]);

        Program::create([
            'group_id' => $group2->id,
            'student_id' => $students[5]->id,
            'title' => 'English Fun Day untuk Anak-Anak',
            'type' => ProgramType::PengabdianMasyarakat,
            'target' => 'Mengenalkan bahasa Inggris melalui permainan',
            'target_audience' => 'Anak-anak usia 7-12 tahun',
            'budget' => 150000,
            'source_of_fund' => 'Dana Mandiri',
            'method' => 'Permainan edukatif dan lagu',
            'output_target' => '15 anak menguasai 50 kosakata dasar',
            'timeline' => 'Minggu ke-2 s/d ke-5',
            'status' => ProgramStatus::Approved,
        ]);

        Program::create([
            'group_id' => $group2->id,
            'student_id' => $students[6]->id,
            'title' => 'Pelatihan Komputer Dasar untuk Perangkat Desa',
            'type' => ProgramType::Multidisiplin,
            'target' => 'Meningkatkan kemampuan IT perangkat desa',
            'target_audience' => 'Perangkat Desa Sidoharjo',
            'budget' => 400000,
            'source_of_fund' => 'Dana Mandiri',
            'method' => 'Workshop hands-on',
            'output_target' => '10 perangkat desa mampu mengoperasikan aplikasi perkantoran',
            'timeline' => 'Minggu ke-3 s/d ke-5',
            'status' => ProgramStatus::Approved,
        ]);

        // ── 7. Daily Logs (sample for Andi — student[0]) ──────────
        $logDates = ['2026-07-01', '2026-07-02', '2026-07-03', '2026-07-04', '2026-07-05'];
        $activities = [
            'Survei lokasi dan perkenalan dengan perangkat desa. Koordinasi jadwal kegiatan KKN.',
            'Sosialisasi program kerja kepada masyarakat RT 01 dan RT 02. Diskusi kebutuhan desa.',
            'Persiapan materi pelatihan literasi digital. Penyusunan modul pembelajaran.',
            'Pelaksanaan pelatihan literasi digital sesi pertama. 12 warga hadir.',
            'Evaluasi hasil pelatihan sesi pertama. Revisi modul berdasarkan feedback peserta.',
        ];

        foreach ($logDates as $i => $date) {
            DailyLog::create([
                'student_id' => $students[0]->id,
                'date' => $date,
                'duration_minutes' => rand(120, 360),
                'activity_description' => $activities[$i],
                'status' => $i < 3 ? LogStatus::Approved : LogStatus::Pending,
            ]);
        }

        // ── 8. Mentoring Logs (sample for Group 1) ────────────────
        MentoringLog::create([
            'group_id' => $group1->id,
            'student_id' => $students[0]->id,
            'date' => '2026-07-03',
            'topic' => 'Finalisasi Desain Program Literasi Digital',
            'discussion_summary' => 'Membahas target peserta, metode pelatihan, dan penyesuaian modul pembelajaran sesuai kemampuan warga.',
            'dpl_feedback' => 'Program sudah baik. Pastikan materi pelatihan dibuat sederhana dan gunakan contoh-contoh yang relevan dengan kehidupan sehari-hari warga.',
            'status' => LogStatus::Approved,
        ]);

        MentoringLog::create([
            'group_id' => $group1->id,
            'student_id' => $students[1]->id,
            'date' => '2026-07-05',
            'topic' => 'Konsultasi Arsitektur Website Desa',
            'discussion_summary' => 'Diskusi mengenai struktur halaman, fitur utama, dan rencana pelatihan admin website kepada perangkat desa.',
            'status' => LogStatus::Pending,
        ]);
    }
}
