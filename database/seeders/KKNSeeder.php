<?php

namespace Database\Seeders;

use App\Enums\LogStatus;
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
        if ($dpl1) {
            $dpl1->update(['nip' => '197805152005011002', 'prodi' => 'Teknik Informatika', 'fakultas' => 'Fakultas Teknik', 'name' => 'Dr. Budi Santoso, M.Kom.']);
        }

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
        // KELOMPOK 1: Sudah bisa cetak LPK & LRK
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
            'is_lrk_locked' => true,
            'is_lpk_locked' => true,
        ]);

        // KELOMPOK 2: Sudah bisa cetak LRK, Belum bisa cetak LPK
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
            'is_lrk_locked' => true,
            'is_lpk_locked' => false,
        ]);

        // KELOMPOK 3: Belum bisa cetak LRK maupun LPK
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
            'is_lrk_locked' => false,
            'is_lpk_locked' => false,
        ]);

        // Assign DPLs to groups via group_id
        if ($dpl1) $dpl1->update(['group_id' => $group1->id]);
        $dpl2->update(['group_id' => $group2->id]);
        $dpl3->update(['group_id' => $group3->id]);


        // ── 4. Students ───────────────────────────────────────────
        $studentsData = [
            // Group 1 students
            ['name' => 'Andi Mahasiswa', 'email' => 'mahasiswa@sikkn.test', 'nim' => '21100122100001', 'prodi' => 'Teknik Informatika', 'fakultas' => 'Fakultas Teknik', 'group' => $group1], // 2 = Saintek
            ['name' => 'Rina Permata', 'email' => 'rina@sikkn.test', 'nim' => '14000122100002', 'prodi' => 'Ilmu Komunikasi', 'fakultas' => 'Fakultas Ilmu Sosial dan Politik', 'group' => $group1], // 1 = Soshum
            ['name' => 'Budi Hartono', 'email' => 'budi.h@sikkn.test', 'nim' => '23000122100003', 'prodi' => 'Agroteknologi', 'fakultas' => 'Fakultas Pertanian', 'group' => $group1], // 2 = Saintek
            ['name' => 'Dewi Sartika', 'email' => 'dewi@sikkn.test', 'nim' => '25000122100004', 'prodi' => 'Kesehatan Masyarakat', 'fakultas' => 'Fakultas Kedokteran', 'group' => $group1], // 2 = Saintek

            // Group 2 students
            ['name' => 'Fajar Nugroho', 'email' => 'fajar@sikkn.test', 'nim' => '40030022100005', 'prodi' => 'Teknologi Rekayasa', 'fakultas' => 'Sekolah Vokasi', 'group' => $group2], // 4003 = Vokasi Saintek
            ['name' => 'Lestari Wulandari', 'email' => 'lestari@sikkn.test', 'nim' => '16000122100006', 'prodi' => 'Pendidikan Bahasa Inggris', 'fakultas' => 'Fakultas Keguruan dan Ilmu Pendidikan', 'group' => $group2], // 1 = Soshum
            ['name' => 'Agus Riyanto', 'email' => 'agus@sikkn.test', 'nim' => '40010022100007', 'prodi' => 'Manajemen Perusahaan', 'fakultas' => 'Sekolah Vokasi', 'group' => $group2], // 4001 = Vokasi Soshum

            // Group 3 students
            ['name' => 'Putri Handayani', 'email' => 'putri@sikkn.test', 'nim' => '12000122100008', 'prodi' => 'Manajemen', 'fakultas' => 'Fakultas Ekonomika dan Bisnis', 'group' => $group3], // 1 = Soshum
            ['name' => 'Rizky Pratama', 'email' => 'rizky@sikkn.test', 'nim' => '11000122100009', 'prodi' => 'Pariwisata', 'fakultas' => 'Fakultas Ilmu Budaya', 'group' => $group3], // 1 = Soshum
            ['name' => 'Sinta Dewi', 'email' => 'sinta@sikkn.test', 'nim' => '40040022100010', 'prodi' => 'Teknik Infrastruktur', 'fakultas' => 'Sekolah Vokasi', 'group' => $group3], // 4004 = Vokasi Saintek
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

        // ── 5. Programs & Participants ─────────────────────────
        
        // --- GROUP 1: Siap Cetak LRK & LPK (Semua LPK Approved) ---
        $md1_g1 = Program::create(['group_id' => $group1->id, 'student_id' => null, 'title' => 'Digitalisasi dan Pemetaan Potensi UMKM Desa', 'type' => ProgramType::Multidisiplin, 'sequence' => 1]);
        $md2_g1 = Program::create(['group_id' => $group1->id, 'student_id' => null, 'title' => 'Edukasi Pola Hidup Sehat & Pembuatan Apotek Hidup', 'type' => ProgramType::Multidisiplin, 'sequence' => 2]);
        $md3_g1 = Program::create(['group_id' => $group1->id, 'student_id' => null, 'title' => 'Video Profil Desa Sukamakmur', 'type' => ProgramType::Multidisiplin, 'sequence' => 3]);
        
        $g1_students = [$students[0], $students[1], $students[2], $students[3]];
        
        // Custom programs for Group 1
        $sosmas_titles_g1 = [
            'Pendampingan Pembelajaran Jarak Jauh Anak Sekolah Dasar',
            'Penyuluhan Pemasaran Online Melalui Media Sosial',
            'Sosialisasi Pengolahan Sampah Organik Menjadi Kompos',
            'Pemeriksaan Kesehatan Gratis untuk Lansia'
        ];
        $lainnya_titles_g1 = [
            'Pembuatan Plang Penunjuk Jalan Utama Desa',
            'Lomba Mewarnai Tingkat TK/PAUD Desa Sukamakmur',
            'Kerja Bakti Membersihkan Saluran Irigasi Warga',
            'Pelatihan Cuci Tangan Pakai Sabun untuk Anak Usia Dini'
        ];

        foreach ($g1_students as $idx => $student) {
            // Multidisiplin
            foreach ([$md1_g1, $md2_g1, $md3_g1] as $md) {
                $md->participants()->create([
                    'student_id' => $student->id,
                    'status' => ProgramStatus::Approved,
                    'lpk_status' => ProgramStatus::Approved,
                    'execution_date' => '2026-07-' . (10 + $idx),
                    'participant_title' => $md->sequence != 3 ? 'Usulan Spesifik ' . $student->name : null,
                    'role_in_program' => $md->sequence == 3 ? ($idx == 0 ? 'Sutradara/Editor' : 'Kameramen/Talent') : 'Ketua Sub-Program',
                    'responsibility' => 'Mengkoordinasikan dan melaksanakan program sesuai bidang ilmu.',
                    'achievement' => 'Terlaksana 100% dan mendapat antusiasme tinggi dari warga.',
                    'obstacle' => 'Tidak ada kendala yang berarti selama pelaksanaan.',
                    'solution' => 'Koordinasi aktif dengan perangkat desa untuk memastikan kelancaran.',
                    'problem_potential' => 'Kurangnya pemahaman warga terkait digitalisasi UMKM.',
                    'method' => 'Sosialisasi dan Praktik Langsung',
                    'target_audience' => 'Pelaku UMKM Desa',
                    'output_target' => 'Buku Panduan Digitalisasi',
                ]);
            }
            // Sosmas
            $sos = Program::create(['group_id' => $group1->id, 'student_id' => $student->id, 'title' => $sosmas_titles_g1[$idx], 'type' => ProgramType::SosialKemasyarakatan]);
            $sos->participants()->create([
                'student_id' => $student->id, 'status' => ProgramStatus::Approved, 'lpk_status' => ProgramStatus::Approved, 
                'execution_date' => '2026-07-15', 'role_in_program' => 'Ketua Pelaksana', 
                'responsibility' => 'Bertanggung jawab penuh atas pelaksanaan acara sosmas.',
                'achievement' => 'Dihadiri 30 orang warga desa, target tercapai.', 'obstacle' => 'Cuaca hujan di awal acara.', 'solution' => 'Memindahkan lokasi ke dalam balai desa.'
            ]);
            // Lainnya
            $lain = Program::create(['group_id' => $group1->id, 'student_id' => $student->id, 'title' => $lainnya_titles_g1[$idx], 'type' => ProgramType::Lainnya]);
            $lain->participants()->create([
                'student_id' => $student->id, 'status' => ProgramStatus::Approved, 'lpk_status' => ProgramStatus::Approved, 
                'execution_date' => '2026-07-20', 'role_in_program' => 'Koordinator', 
                'responsibility' => 'Memimpin persiapan teknis dan alat.',
                'achievement' => 'Acara berjalan lancar.', 'obstacle' => '-', 'solution' => '-'
            ]);
        }

        // --- GROUP 2: Siap Cetak LRK Saja (LRK Approved, LPK belum) ---
        $md1_g2 = Program::create(['group_id' => $group2->id, 'student_id' => null, 'title' => 'Peningkatan Kualitas Pendidikan Dasar', 'type' => ProgramType::Multidisiplin, 'sequence' => 1]);
        $md2_g2 = Program::create(['group_id' => $group2->id, 'student_id' => null, 'title' => 'Pembuatan Media Pembelajaran Interaktif', 'type' => ProgramType::Multidisiplin, 'sequence' => 2]);
        $md3_g2 = Program::create(['group_id' => $group2->id, 'student_id' => null, 'title' => 'Video Profil Potensi Edukasi Desa Sidoharjo', 'type' => ProgramType::Multidisiplin, 'sequence' => 3]);
        
        $g2_students = [$students[4], $students[5], $students[6]];
        
        $sosmas_titles_g2 = [
            'Bimbingan Belajar Matematika SD',
            'Pelatihan Bahasa Inggris Dasar untuk Anak-anak',
            'Pelatihan Senam Pagi untuk Siswa SD'
        ];
        $lainnya_titles_g2 = [
            'Pembuatan Pojok Baca di Balai Desa',
            'Storytelling Anak-anak Desa',
            'Kerja Bakti Membersihkan Lingkungan Sekolah'
        ];

        foreach ($g2_students as $idx => $student) {
            foreach ([$md1_g2, $md2_g2, $md3_g2] as $md) {
                $md->participants()->create([
                    'student_id' => $student->id,
                    'status' => ProgramStatus::Approved,
                    'lpk_status' => ProgramStatus::Draft, // <--- LPK MASIH DRAFT
                    'execution_date' => '2026-07-12',
                    'participant_title' => $md->sequence != 3 ? 'Integrasi ' . $student->prodi . ' pada Pendidikan' : null,
                    'role_in_program' => 'Fasilitator',
                    'responsibility' => 'Menyusun materi dan menyampaikan di depan kelas.',
                    'problem_potential' => 'Anak-anak kurang mendapatkan pendampingan belajar di rumah.',
                    'method' => 'Bimbingan Belajar Kelompok',
                    'target_audience' => 'Siswa SD Desa Sidoharjo',
                    'output_target' => 'Peningkatan Minat Belajar Anak',
                ]);
            }
            $sos = Program::create(['group_id' => $group2->id, 'student_id' => $student->id, 'title' => $sosmas_titles_g2[$idx], 'type' => ProgramType::SosialKemasyarakatan]);
            $sos->participants()->create([
                'student_id' => $student->id, 'status' => ProgramStatus::Approved, 'lpk_status' => ProgramStatus::Draft, 
                'execution_date' => '2026-07-18', 'role_in_program' => 'Instruktur', 
                'responsibility' => 'Menyiapkan modul dan lembar kerja.',
            ]);
            $lain = Program::create(['group_id' => $group2->id, 'student_id' => $student->id, 'title' => $lainnya_titles_g2[$idx], 'type' => ProgramType::Lainnya]);
            $lain->participants()->create([
                'student_id' => $student->id, 'status' => ProgramStatus::Approved, 'lpk_status' => ProgramStatus::Draft, 
                'execution_date' => '2026-07-22', 'role_in_program' => 'Penanggung Jawab', 
                'responsibility' => 'Mengawasi berjalannya program tambahan.',
            ]);
        }

        // --- GROUP 3: Belum Siap Cetak Keduanya (LRK Masih Draft/Revisi) ---
        $md1_g3 = Program::create(['group_id' => $group3->id, 'student_id' => null, 'title' => 'Pengembangan Pariwisata Berbasis Masyarakat (CBT)', 'type' => ProgramType::Multidisiplin, 'sequence' => 1]);
        $md2_g3 = Program::create(['group_id' => $group3->id, 'student_id' => null, 'title' => 'Pemetaan Potensi Alam dan Konservasi', 'type' => ProgramType::Multidisiplin, 'sequence' => 2]);
        $md3_g3 = Program::create(['group_id' => $group3->id, 'student_id' => null, 'title' => 'Video Promosi Wisata Desa Sendangtirto', 'type' => ProgramType::Multidisiplin, 'sequence' => 3]);
        
        $g3_students = [$students[7], $students[8], $students[9]];
        
        $sosmas_titles_g3 = [
            'Pelatihan Manajemen Keuangan untuk Pokdarwis',
            'Pemasaran Digital Objek Wisata Desa',
            'Sosialisasi Pengelolaan Sampah Kawasan Wisata'
        ];
        $lainnya_titles_g3 = [
            'Pembuatan Spot Foto Menarik di Area Wisata',
            'Pembuatan Brosur Wisata Desa',
            'Penanaman Pohon di Area Kritis'
        ];

        foreach ($g3_students as $idx => $student) {
            foreach ([$md1_g3, $md2_g3, $md3_g3] as $md) {
                $md->participants()->create([
                    'student_id' => $student->id,
                    'status' => ProgramStatus::Draft, // <--- LRK MASIH DRAFT
                    'participant_title' => $md->sequence != 3 ? 'Peran ' . $student->prodi . ' dalam Pariwisata' : null,
                    'role_in_program' => 'Konseptor',
                    'responsibility' => 'Mendesain tata kelola sesuai prodi.',
                    'problem_potential' => 'Potensi wisata ada namun belum dikelola dengan baik.',
                    'method' => 'Diskusi Terfokus (FGD)',
                    'target_audience' => 'Kelompok Sadar Wisata',
                ]);
            }
            $sos = Program::create(['group_id' => $group3->id, 'student_id' => $student->id, 'title' => $sosmas_titles_g3[$idx], 'type' => ProgramType::SosialKemasyarakatan]);
            $sos->participants()->create([
                'student_id' => $student->id, 'status' => ProgramStatus::NeedsRevision, // <--- LRK REVISI
                'revision_note' => 'Mohon jelaskan secara lebih spesifik target pesertanya.',
                'execution_date' => '2026-07-25', 'role_in_program' => 'Pemateri',
            ]);
            $lain = Program::create(['group_id' => $group3->id, 'student_id' => $student->id, 'title' => $lainnya_titles_g3[$idx], 'type' => ProgramType::Lainnya]);
            $lain->participants()->create([
                'student_id' => $student->id, 'status' => ProgramStatus::Submitted, // <--- LRK SUBMITTED (menunggu acc)
                'execution_date' => '2026-07-28', 'role_in_program' => 'Eksekutor',
            ]);
        }

        // ── 6. Daily Logs & Mentoring ──────────
        $weekOneLogs = [
            [
                'date' => '2026-07-01',
                'important_notes' => 'Hari pertama KKN berjalan lancar. Disambut dengan hangat oleh perangkat desa dan masyarakat sekitar. Perlu segera dilakukan pemetaan potensi desa secara menyeluruh.',
                'image_path' => null,
                'status' => LogStatus::Approved,
                'activities' => [
                    ['start_time' => '07:00', 'end_time' => '08:30', 'activity_description' => 'Upacara penerjunan mahasiswa KKN di Balai Desa Sukamakmur.'],
                    ['start_time' => '09:00', 'end_time' => '11:30', 'activity_description' => 'Perkenalan dengan perangkat desa, ketua RT/RW, dan tokoh masyarakat.'],
                    ['start_time' => '13:00', 'end_time' => '15:00', 'activity_description' => 'Survei lokasi dan observasi lingkungan desa bersama kepala dusun.'],
                ],
            ],
            [
                'date' => '2026-07-02',
                'important_notes' => 'Survei UMKM pertama. Menemukan bahwa sebagian besar UMKM belum menggunakan digital marketing.',
                'image_path' => null,
                'status' => LogStatus::Approved,
                'activities' => [
                    ['start_time' => '08:00', 'end_time' => '12:00', 'activity_description' => 'Mengunjungi 5 UMKM makanan ringan di Dusun Krajan.'],
                    ['start_time' => '13:30', 'end_time' => '16:00', 'activity_description' => 'Wawancara dengan pemilik UMKM terkait kendala pemasaran.'],
                ],
            ],
            [
                'date' => '2026-07-03',
                'important_notes' => 'Menyusun materi edukasi digitalisasi.',
                'image_path' => null,
                'status' => LogStatus::Approved,
                'activities' => [
                    ['start_time' => '09:00', 'end_time' => '12:00', 'activity_description' => 'Pencarian referensi materi digital marketing.'],
                    ['start_time' => '13:00', 'end_time' => '17:00', 'activity_description' => 'Penyusunan modul pelatihan untuk warga.'],
                ],
            ],
            [
                'date' => '2026-07-04',
                'important_notes' => '',
                'image_path' => null,
                'status' => LogStatus::Pending,
                'activities' => [
                    ['start_time' => '07:00', 'end_time' => '10:00', 'activity_description' => 'Kerja bakti membersihkan balai desa bersama warga.'],
                    ['start_time' => '10:00', 'end_time' => '12:00', 'activity_description' => 'Rapat koordinasi kelompok membahas progress program mingguan.'],
                ],
            ],
            [
                'date' => '2026-07-08',
                'important_notes' => 'Masuk minggu kedua. Fokus ke persiapan sosialisasi program kerja.',
                'image_path' => null,
                'status' => LogStatus::Pending,
                'activities' => [
                    ['start_time' => '08:00', 'end_time' => '11:00', 'activity_description' => 'Menyiapkan undangan sosialisasi program kerja ke tokoh desa.'],
                    ['start_time' => '13:00', 'end_time' => '15:30', 'activity_description' => 'Mendistribusikan undangan ke masing-masing RT.'],
                ],
            ],
        ];

        foreach ($weekOneLogs as $logData) {
            $log = DailyLog::create([
                'student_id' => $students[0]->id,
                'date' => $logData['date'],
                'important_notes' => $logData['important_notes'],
                'image_path' => $logData['image_path'] ?? null,
                'status' => $logData['status'],
            ]);

            foreach ($logData['activities'] as $act) {
                \App\Models\DailyLogActivity::create([
                    'daily_log_id' => $log->id,
                    'start_time' => $act['start_time'],
                    'end_time' => $act['end_time'],
                    'activity_description' => $act['activity_description'],
                ]);
            }
        }

        MentoringLog::create([
            'group_id' => $group1->id,
            'student_id' => $students[0]->id,
            'date' => '2026-07-03',
            'topic' => 'Finalisasi Desain Program Literasi Digital',
            'discussion_summary' => 'Membahas target peserta, metode pelatihan, dan penyesuaian modul pembelajaran sesuai kemampuan warga.',
            'dpl_feedback' => 'Program sudah baik. Pastikan materi pelatihan dibuat sederhana dan gunakan contoh-contoh yang relevan dengan kehidupan sehari-hari warga.',
            'status' => LogStatus::Approved,
        ]);
    }
}
