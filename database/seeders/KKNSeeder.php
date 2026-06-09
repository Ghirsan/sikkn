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
            'name' => 'Kelompok 01',
            'type' => \App\Enums\GroupType::Reguler,
            'village' => 'Desa Sukamakmur',
            'district' => 'Kec. Karanganyar',
            'regency' => 'Kab. Karanganyar',
            'province' => 'Jawa Tengah',
            'village_head' => 'H. Suparman, S.Sos.',
            'background' => 'Desa Sukamakmur merupakan desa yang terletak di Kecamatan Karanganyar dengan mayoritas penduduk bermata pencaharian sebagai petani. Permasalahan utama yang ditemukan meliputi rendahnya literasi digital di kalangan warga usia produktif, saluran irigasi yang rusak, serta potensi UMKM yang belum terkelola dengan baik.',
        ]);

        $group2 = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 02',
            'type' => \App\Enums\GroupType::Tematik,
            'village' => 'Desa Sidoharjo',
            'district' => 'Kec. Polanharjo',
            'regency' => 'Kab. Klaten',
            'province' => 'Jawa Tengah',
            'partner_name' => 'Yayasan Pendidikan Nusantara',
            'village_head' => 'Bambang Widodo',
            'background' => 'Desa Sidoharjo memiliki potensi di bidang pendidikan namun masih terkendala dengan kurangnya tenaga pengajar dan fasilitas belajar. Anak-anak usia sekolah membutuhkan bimbingan belajar terutama di bidang Matematika dan Bahasa Inggris.',
        ]);

        $group3 = Group::create([
            'period_id' => $period->id,
            'name' => 'Kelompok 03',
            'type' => \App\Enums\GroupType::Reguler,
            'village' => 'Desa Sendangtirto',
            'district' => 'Kec. Berbah',
            'regency' => 'Kab. Sleman',
            'province' => 'DI Yogyakarta',
            'village_head' => 'Dra. Sri Mulyani',
            'background' => 'Desa Sendangtirto memiliki potensi wisata dan pertanian organik yang belum dikelola secara optimal. Diperlukan pendampingan dalam hal pemasaran digital dan pengelolaan sumber daya alam.',
        ]);

        // Assign DPLs to groups via group_id
        $dpl1->update(['group_id' => $group1->id]);
        $dpl2->update(['group_id' => $group2->id]);
        $dpl3->update(['group_id' => $group3->id]);


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
        // Group 1 — All Approved (Fully structured for LRK test)
        $prog1 = Program::create([
            'group_id' => $group1->id,
            'student_id' => $students[0]->id,
            'title' => 'Pelatihan Literasi Digital untuk Warga Desa',
            'type' => ProgramType::SosialKemasyarakatan,
            'target' => 'Meningkatkan kemampuan literasi digital warga desa',
            'target_audience' => 'Warga Desa Sukamakmur',
            'budget' => 500000,
            'source_of_fund' => 'Dana Mandiri',
            'method' => 'Workshop dan pendampingan langsung',
            'output_target' => '30 warga mampu menggunakan aplikasi digital dasar',
            'role_in_program' => 'Ketua Pelaksana',
            'responsibility' => 'Menyusun materi dan mengkoordinasi peserta',
            'status' => ProgramStatus::Approved,
        ]);

        $prog2 = Program::create([
            'group_id' => $group1->id,
            'student_id' => $students[1]->id,
            'title' => 'Pembuatan Website Profil Desa',
            'type' => ProgramType::Multidisiplin,
            'theme' => 'Digitalisasi Layanan Desa',
            'multidisciplinary_number' => 1,
            'problem_potential' => 'Tidak adanya media informasi resmi desa',
            'target_audience' => 'Perangkat Desa dan masyarakat umum',
            'budget' => 300000,
            'source_of_fund' => 'Dana Mandiri',
            'method' => 'Pengembangan website dan pelatihan admin',
            'output_target' => 'Website profil desa aktif dan terkelola',
            'status' => ProgramStatus::Approved,
        ]);

        $prog3 = Program::create([
            'group_id' => $group1->id,
            'student_id' => $students[2]->id,
            'title' => 'Video Dokumenter Potensi UMKM Desa',
            'type' => ProgramType::Multidisiplin,
            'theme' => 'Video Profil Desa Sukamakmur',
            'multidisciplinary_number' => 3, // MD 3 = Video Format
            'role_in_program' => 'Sutradara & Editor',
            'responsibility' => 'Mengambil footage dan editing akhir',
            'storyboard' => "Scene 1: Wawancara Kepala Desa\nScene 2: Aktivitas UMKM Kripik Singkong\nScene 3: Pemandangan Sawah Desa",
            'video_script' => "Opening: Selamat datang di Desa Sukamakmur...\nVoice Over: Desa ini memiliki banyak potensi...",
            'status' => ProgramStatus::Approved,
        ]);

        $prog4 = Program::create([
            'group_id' => $group1->id,
            'student_id' => $students[3]->id,
            'title' => 'Perbaikan Saluran Irigasi Persawahan',
            'type' => ProgramType::Multidisiplin,
            'theme' => 'Ketahanan Pangan Desa',
            'multidisciplinary_number' => 2,
            'problem_potential' => 'Saluran irigasi banyak yang tersumbat',
            'target_audience' => 'Petani Desa Sukamakmur',
            'method' => 'Kerja bakti dan pembuatan saluran sekunder',
            'output_target' => 'Aliran air sawah lancar',
            'status' => ProgramStatus::Approved,
        ]);

        // Add Program Dates for Calendar (Table 8)
        $dates = ['2026-07-06', '2026-07-07'];
        foreach ($dates as $d) { \App\Models\ProgramDate::create(['program_id' => $prog1->id, 'date' => $d]); }
        $dates = ['2026-07-08', '2026-07-09', '2026-07-10'];
        foreach ($dates as $d) { \App\Models\ProgramDate::create(['program_id' => $prog2->id, 'date' => $d]); }
        $dates = ['2026-07-15', '2026-07-16'];
        foreach ($dates as $d) { \App\Models\ProgramDate::create(['program_id' => $prog3->id, 'date' => $d]); }

        // Add Schedule Events (Table 7)
        \App\Models\ScheduleEvent::create([
            'group_id' => $group1->id,
            'title' => 'Upacara Penerjunan Mahasiswa KKN',
            'date' => '2026-07-01',
            'week_number' => 1,
            'description' => 'Upacara pelepasan oleh Rektor Universitas',
        ]);
        \App\Models\ScheduleEvent::create([
            'group_id' => $group1->id,
            'title' => 'Observasi Lapangan dan Diskusi dengan Perangkat Desa',
            'date' => '2026-07-02',
            'week_number' => 1,
            'description' => 'Survey pendahuluan di balai desa',
        ]);

        // Group 2 — all approved (ready for PDF)
        Program::create([
            'group_id' => $group2->id,
            'student_id' => $students[4]->id,
            'title' => 'Bimbingan Belajar Matematika Anak SD',
            'type' => ProgramType::SosialKemasyarakatan,
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
            'type' => ProgramType::SosialKemasyarakatan,
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
