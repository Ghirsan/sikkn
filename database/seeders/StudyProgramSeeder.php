<?php

namespace Database\Seeders;

use App\Models\Faculty;
use App\Models\StudyProgram;
use Illuminate\Database\Seeder;

class StudyProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            ['code' => '110001', 'faculty' => 'HUKUM', 'name' => 'Hukum S1', 'short_name' => 'FH'],
            ['code' => '120101', 'faculty' => 'EKONOMIKA DAN BISNIS', 'name' => 'Manajemen S1', 'short_name' => 'FEB'],
            ['code' => '120102', 'faculty' => 'EKONOMIKA DAN BISNIS', 'name' => 'Bisnis Digital S1', 'short_name' => 'FEB'],
            ['code' => '120201', 'faculty' => 'EKONOMIKA DAN BISNIS', 'name' => 'Ekonomi S1', 'short_name' => 'FEB'],
            ['code' => '120202', 'faculty' => 'EKONOMIKA DAN BISNIS', 'name' => 'Ekonomi Islam S1', 'short_name' => 'FEB'],
            ['code' => '120301', 'faculty' => 'EKONOMIKA DAN BISNIS', 'name' => 'Akuntansi S1', 'short_name' => 'FEB'],
            ['code' => '130101', 'faculty' => 'ILMU BUDAYA', 'name' => 'Sastra Indonesia S1', 'short_name' => 'FIB'],
            ['code' => '130201', 'faculty' => 'ILMU BUDAYA', 'name' => 'Sastra Inggris S1', 'short_name' => 'FIB'],
            ['code' => '130202', 'faculty' => 'ILMU BUDAYA', 'name' => 'Bahasa dan Kebudayaan Jepang S1', 'short_name' => 'FIB'],
            ['code' => '130301', 'faculty' => 'ILMU BUDAYA', 'name' => 'Sejarah S1', 'short_name' => 'FIB'],
            ['code' => '130401', 'faculty' => 'ILMU BUDAYA', 'name' => 'Ilmu Perpustakaan S1', 'short_name' => 'FIB'],
            ['code' => '130402', 'faculty' => 'ILMU BUDAYA', 'name' => 'Antropologi Sosial S1', 'short_name' => 'FIB'],
            ['code' => '140101', 'faculty' => 'ILMU SOSIAL DAN ILMU POLITIK', 'name' => 'Ilmu Pemerintahan S1', 'short_name' => 'FISIP'],
            ['code' => '140201', 'faculty' => 'ILMU SOSIAL DAN ILMU POLITIK', 'name' => 'Administrasi Publik S1', 'short_name' => 'FISIP'],
            ['code' => '140202', 'faculty' => 'ILMU SOSIAL DAN ILMU POLITIK', 'name' => 'Administrasi Publik K. Rembang S1', 'short_name' => 'FISIP'],
            ['code' => '140301', 'faculty' => 'ILMU SOSIAL DAN ILMU POLITIK', 'name' => 'Administrasi Bisnis S1', 'short_name' => 'FISIP'],
            ['code' => '140401', 'faculty' => 'ILMU SOSIAL DAN ILMU POLITIK', 'name' => 'Ilmu Komunikasi S1', 'short_name' => 'FISIP'],
            ['code' => '140501', 'faculty' => 'ILMU SOSIAL DAN ILMU POLITIK', 'name' => 'Hubungan Internasional S1', 'short_name' => 'FISIP'],
            ['code' => '150001', 'faculty' => 'PSIKOLOGI', 'name' => 'Psikologi S1', 'short_name' => 'FPSI'],
            ['code' => '210101', 'faculty' => 'TEKNIK', 'name' => 'Teknik Sipil S1', 'short_name' => 'FT'],
            ['code' => '210201', 'faculty' => 'TEKNIK', 'name' => 'Arsitektur S1', 'short_name' => 'FT'],
            ['code' => '210301', 'faculty' => 'TEKNIK', 'name' => 'Teknik Kimia S1', 'short_name' => 'FT'],
            ['code' => '210401', 'faculty' => 'TEKNIK', 'name' => 'Perencanaan Wilayah Dan Kota S1', 'short_name' => 'FT'],
            ['code' => '210501', 'faculty' => 'TEKNIK', 'name' => 'Teknik Mesin S1', 'short_name' => 'FT'],
            ['code' => '210601', 'faculty' => 'TEKNIK', 'name' => 'Teknik Elektro S1', 'short_name' => 'FT'],
            ['code' => '210701', 'faculty' => 'TEKNIK', 'name' => 'Teknik Industri S1', 'short_name' => 'FT'],
            ['code' => '210801', 'faculty' => 'TEKNIK', 'name' => 'Teknik Lingkungan S1', 'short_name' => 'FT'],
            ['code' => '210901', 'faculty' => 'TEKNIK', 'name' => 'Teknik Perkapalan S1', 'short_name' => 'FT'],
            ['code' => '211001', 'faculty' => 'TEKNIK', 'name' => 'Teknik Geologi S1', 'short_name' => 'FT'],
            ['code' => '211101', 'faculty' => 'TEKNIK', 'name' => 'Teknik Geodesi S1', 'short_name' => 'FT'],
            ['code' => '211201', 'faculty' => 'TEKNIK', 'name' => 'Teknik Komputer S1', 'short_name' => 'FT'],
            ['code' => '220101', 'faculty' => 'KEDOKTERAN', 'name' => 'Kedokteran S1', 'short_name' => 'FK'],
            ['code' => '220102', 'faculty' => 'KEDOKTERAN', 'name' => 'Kedokteran Gigi S1', 'short_name' => 'FK'],
            ['code' => '220103', 'faculty' => 'KEDOKTERAN', 'name' => 'Farmasi S1', 'short_name' => 'FK'],
            ['code' => '220201', 'faculty' => 'KEDOKTERAN', 'name' => 'Keperawatan S1', 'short_name' => 'FK'],
            ['code' => '220301', 'faculty' => 'KEDOKTERAN', 'name' => 'Gizi S1', 'short_name' => 'FK'],
            ['code' => '230101', 'faculty' => 'PETERNAKAN DAN PERTANIAN', 'name' => 'Peternakan S1', 'short_name' => 'FPP'],
            ['code' => '230201', 'faculty' => 'PETERNAKAN DAN PERTANIAN', 'name' => 'Teknologi Pangan S1', 'short_name' => 'FPP'],
            ['code' => '230202', 'faculty' => 'PETERNAKAN DAN PERTANIAN', 'name' => 'Agroekoteknologi S1', 'short_name' => 'FPP'],
            ['code' => '230203', 'faculty' => 'PETERNAKAN DAN PERTANIAN', 'name' => 'Agribisnis S1', 'short_name' => 'FPP'],
            ['code' => '240101', 'faculty' => 'SAINS DAN MATEMATIKA', 'name' => 'Matematika S1', 'short_name' => 'FSM'],
            ['code' => '240201', 'faculty' => 'SAINS DAN MATEMATIKA', 'name' => 'Biologi S1', 'short_name' => 'FSM'],
            ['code' => '240202', 'faculty' => 'SAINS DAN MATEMATIKA', 'name' => 'Bioteknologi S1', 'short_name' => 'FSM'],
            ['code' => '240301', 'faculty' => 'SAINS DAN MATEMATIKA', 'name' => 'Kimia S1', 'short_name' => 'FSM'],
            ['code' => '240401', 'faculty' => 'SAINS DAN MATEMATIKA', 'name' => 'Fisika S1', 'short_name' => 'FSM'],
            ['code' => '240501', 'faculty' => 'SAINS DAN MATEMATIKA', 'name' => 'Statistika S1', 'short_name' => 'FSM'],
            ['code' => '240601', 'faculty' => 'SAINS DAN MATEMATIKA', 'name' => 'Informatika S1', 'short_name' => 'FSM'],
            ['code' => '250001', 'faculty' => 'KESEHATAN MASYARAKAT', 'name' => 'Kesehatan Masyarakat S1', 'short_name' => 'FKM'],
            ['code' => '250002', 'faculty' => 'KESEHATAN MASYARAKAT', 'name' => 'Keselamatan dan Kesehatan Kerja S1', 'short_name' => 'FKM'],
            ['code' => '260101', 'faculty' => 'PERIKANAN DAN ILMU KELAUTAN', 'name' => 'Manajemen Sumber Daya Perairan S1', 'short_name' => 'FPIK'],
            ['code' => '260201', 'faculty' => 'PERIKANAN DAN ILMU KELAUTAN', 'name' => 'Akuakultur S1', 'short_name' => 'FPIK'],
            ['code' => '260301', 'faculty' => 'PERIKANAN DAN ILMU KELAUTAN', 'name' => 'Perikanan Tangkap S1', 'short_name' => 'FPIK'],
            ['code' => '260401', 'faculty' => 'PERIKANAN DAN ILMU KELAUTAN', 'name' => 'Ilmu Kelautan S1', 'short_name' => 'FPIK'],
            ['code' => '260501', 'faculty' => 'PERIKANAN DAN ILMU KELAUTAN', 'name' => 'Oseanografi S1', 'short_name' => 'FPIK'],
            ['code' => '260601', 'faculty' => 'PERIKANAN DAN ILMU KELAUTAN', 'name' => 'Teknologi Hasil Perikanan S1', 'short_name' => 'FPIK'],
            ['code' => '400113', 'faculty' => 'SEKOLAH VOKASI', 'name' => 'Manajemen dan Administrasi Logistik D4', 'short_name' => 'SV'],
            ['code' => '400114', 'faculty' => 'SEKOLAH VOKASI', 'name' => 'Akuntansi Perpajakan D4', 'short_name' => 'SV'],
            ['code' => '400205', 'faculty' => 'SEKOLAH VOKASI', 'name' => 'Bahasa Asing Terapan D4', 'short_name' => 'SV'],
            ['code' => '400206', 'faculty' => 'SEKOLAH VOKASI', 'name' => 'Informasi dan Humas D4', 'short_name' => 'SV'],
            ['code' => '400305', 'faculty' => 'SEKOLAH VOKASI', 'name' => 'Teknik Infrastruktur Sipil dan Perancangan Arsitektur D4', 'short_name' => 'SV'],
            ['code' => '400306', 'faculty' => 'SEKOLAH VOKASI', 'name' => 'Perencanaan Tata Ruang dan Pertanahan D4', 'short_name' => 'SV'],
            ['code' => '400401', 'faculty' => 'SEKOLAH VOKASI', 'name' => 'Teknologi Rekayasa Kimia Industri D4', 'short_name' => 'SV'],
            ['code' => '400402', 'faculty' => 'SEKOLAH VOKASI', 'name' => 'Rekayasa Perancangan Mekanik D4', 'short_name' => 'SV'],
            ['code' => '400403', 'faculty' => 'SEKOLAH VOKASI', 'name' => 'Teknologi Rekayasa Otomasi D4', 'short_name' => 'SV'],
            ['code' => '400404', 'faculty' => 'SEKOLAH VOKASI', 'name' => 'Teknologi Rekayasa Konstruksi Perkapalan D4', 'short_name' => 'SV'],
            ['code' => '400406', 'faculty' => 'SEKOLAH VOKASI', 'name' => 'Teknik Listrik Industri D4', 'short_name' => 'SV'],
        ];

        $facultiesData = [];
        foreach ($programs as $program) {
            $facultyCode = substr($program['code'], 0, 2);
            $facultiesData[$program['faculty']] = [
                'code' => $facultyCode,
                'short_name' => $program['short_name'],
            ];
        }

        $faculties = [];
        foreach ($facultiesData as $name => $data) {
            $faculties[$name] = Faculty::updateOrCreate(
                ['code' => $data['code']],
                ['name' => $name, 'short_name' => $data['short_name']]
            );
        }

        foreach ($programs as $program) {
            StudyProgram::updateOrCreate(
                ['code' => $program['code']],
                [
                    'faculty_id' => $faculties[$program['faculty']]->id,
                    'name' => $program['name'],
                ]
            );
        }
    }
}
