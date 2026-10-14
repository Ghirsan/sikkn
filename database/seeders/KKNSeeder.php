<?php

namespace Database\Seeders;

use App\Enums\LogStatus;
use App\Enums\ProgramStatus;
use App\Enums\Semester;
use App\Enums\UserRole;
use App\Models\DailyLog;
use App\Models\DailyLogActivity;
use App\Models\Faculty;
use App\Models\Grade;
use App\Models\Group;
use App\Models\GroupType;
use App\Models\MentoringLog;
use App\Models\ParticipantOutput;
use App\Models\Period;
use App\Models\Program;
use App\Models\ProgramType;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

class KKNSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $now = now();
            $defaultPassword = Hash::make('password');

            // ── 1. Period (Starts Oct 14, 2026 - Nov 14, 2026) ──────
            $period = Period::updateOrCreate(
                ['semester' => Semester::Ganjil, 'year' => 2026],
                [
                    'name' => 'KKN Tim Gasal 2026/2027',
                    'start_date' => '2026-10-14',
                    'end_date' => '2026-11-14',
                ]
            );

            // ── 2. In-Memory Lookup Caches ────────────────────────────
            $groupTypeMap = GroupType::pluck('id', 'code')->toArray();
            $programTypeMap = ProgramType::pluck('id', 'code')->toArray();
            $facultyMap = Faculty::pluck('id', 'short_name')->toArray();
            $studyProgramMap = StudyProgram::pluck('id', 'code')->toArray();
            $studyProgramFacultyMap = StudyProgram::pluck('faculty_id', 'id')->toArray();

            // ── 3. Groups from docs-source/tim.xlsx ───────────────────
            $timFile = base_path('docs-source/tim.xlsx');
            $groupsData = [];

            if (file_exists($timFile)) {
                $sheetTim = IOFactory::load($timFile)->getActiveSheet()->toArray(null, true, true, false);
                array_shift($sheetTim); // header

                foreach ($sheetTim as $idx => $r) {
                    if (empty($r[0])) {
                        continue;
                    }
                    $name = trim($r[0]);
                    $typeCode = trim($r[1] ?? 'Reguler');
                    $groupTypeId = $groupTypeMap[$typeCode] ?? $groupTypeMap['Reguler'] ?? null;
                    $regency = ! empty($r[2]) ? trim($r[2]) : 'Kab. Karanganyar';
                    $district = ! empty($r[3]) ? trim($r[3]) : 'Kec. Sukamakmur';
                    $village = ! empty($r[4]) ? trim($r[4]) : 'Desa Mandiri '.($idx + 1);

                    $groupsData[] = [
                        'period_id' => $period->id,
                        'name' => $name,
                        'group_type_id' => $groupTypeId,
                        'regency' => $regency,
                        'district' => $district,
                        'village' => $village,
                        'province' => 'Jawa Tengah',
                        'partner_name' => ($idx % 3 === 0) ? 'Yayasan Pembangunan Desa' : null,
                        'village_head' => 'Kepala Desa '.$village,
                        'background' => "Program KKN untuk akselerasi dan pemberdayaan masyarakat di $village, $district, $regency.",
                        'is_lrk_locked' => ($idx === 0 || $idx === 1),
                        'is_lpk_locked' => ($idx === 0),
                        'start_date' => '2026-10-14',
                        'end_date' => '2026-11-14',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (! empty($groupsData)) {
                DB::table('groups')->insert($groupsData);
            }

            $allGroups = Group::where('period_id', $period->id)->get();
            $groupCount = $allGroups->count();

            // ── 4. User Accounts (Standard Template) ──────────────────
            $userTemplate = [
                'name' => null,
                'email' => null,
                'password' => $defaultPassword,
                'role' => null,
                'nim' => null,
                'nip' => null,
                'faculty_id' => null,
                'study_program_id' => null,
                'group_id' => null,
                'phone' => null,
                'emergency_phone' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $usersToInsert = [];

            // 4A. Admin P2KKN
            $usersToInsert[] = array_merge($userTemplate, [
                'name' => 'Admin P2KKN',
                'email' => 'admin@sikkn.test',
                'role' => UserRole::P2kkn->value,
            ]);

            // 4B. Every Faculty has its own account
            $allFaculties = Faculty::all();
            foreach ($allFaculties as $fac) {
                $slug = strtolower($fac->short_name ?: $fac->code);
                $usersToInsert[] = array_merge($userTemplate, [
                    'name' => 'Dekan '.$fac->name,
                    'email' => "fakultas.{$slug}@sikkn.test",
                    'role' => UserRole::Fakultas->value,
                    'faculty_id' => $fac->id,
                ]);
            }

            // Main demo fakultas account (mapped to FT or first faculty)
            $ftId = $facultyMap['FT'] ?? $allFaculties->first()?->id;
            $usersToInsert[] = array_merge($userTemplate, [
                'name' => 'Dekan Fakultas Teknik',
                'email' => 'fakultas@sikkn.test',
                'role' => UserRole::Fakultas->value,
                'faculty_id' => $ftId,
            ]);

            // 4C. Every Study Program has its own account
            $allStudyPrograms = StudyProgram::all();
            foreach ($allStudyPrograms as $sp) {
                $usersToInsert[] = array_merge($userTemplate, [
                    'name' => 'Kaprodi '.$sp->name,
                    'email' => "prodi.{$sp->code}@sikkn.test",
                    'role' => UserRole::Prodi->value,
                    'study_program_id' => $sp->id,
                    'faculty_id' => $sp->faculty_id,
                ]);
            }

            // Main demo prodi account (Informatika 240601)
            $informatikaId = $studyProgramMap['240601'] ?? $allStudyPrograms->first()?->id;
            $usersToInsert[] = array_merge($userTemplate, [
                'name' => 'Kaprodi Informatika',
                'email' => 'prodi@sikkn.test',
                'role' => UserRole::Prodi->value,
                'study_program_id' => $informatikaId,
                'faculty_id' => $studyProgramFacultyMap[$informatikaId] ?? null,
            ]);

            // 4D. DPLs from docs-source/dosen.xlsx
            $dosenFile = base_path('docs-source/dosen.xlsx');
            if (file_exists($dosenFile)) {
                $sheetDosen = IOFactory::load($dosenFile)->getActiveSheet()->toArray(null, true, true, false);
                array_shift($sheetDosen); // header

                foreach ($sheetDosen as $idx => $r) {
                    if (empty($r[1])) {
                        continue;
                    }
                    $name = trim($r[1]);
                    $nip = ltrim(trim((string) $r[2]), "'");
                    $facShort = trim($r[3] ?? '');
                    $facultyId = $facultyMap[$facShort] ?? null;

                    // First study program of that faculty
                    $firstSpId = null;
                    if ($facultyId) {
                        foreach ($studyProgramFacultyMap as $spId => $fId) {
                            if ($fId === $facultyId) {
                                $firstSpId = $spId;
                                break;
                            }
                        }
                    }

                    $email = ($idx === 0) ? 'dpl@sikkn.test' : 'dpl'.($idx + 1).'@sikkn.test';

                    $usersToInsert[] = array_merge($userTemplate, [
                        'name' => $name,
                        'nip' => $nip,
                        'email' => $email,
                        'role' => UserRole::Dpl->value,
                        'faculty_id' => $facultyId,
                        'study_program_id' => $firstSpId,
                    ]);
                }
            }

            // 4E. Students from docs-source/mahasiswa.xlsx
            $mhsFile = base_path('docs-source/mahasiswa.xlsx');
            if (file_exists($mhsFile)) {
                $sheetMhs = IOFactory::load($mhsFile)->getActiveSheet()->toArray(null, true, true, false);
                array_shift($sheetMhs); // header

                foreach ($sheetMhs as $idx => $r) {
                    if (empty($r[0])) {
                        continue;
                    }
                    $nim = trim((string) $r[0]);
                    $name = trim($r[1]);
                    $code = substr($nim, 0, 6);
                    $spId = $studyProgramMap[$code] ?? null;
                    $facultyId = $spId ? ($studyProgramFacultyMap[$spId] ?? null) : null;

                    // Distribute across groups
                    $assignedGroup = $groupCount > 0 ? $allGroups[$idx % $groupCount] : null;

                    $email = ($idx === 0) ? 'mahasiswa@sikkn.test' : 'mhs_'.$nim.'@sikkn.test';

                    $usersToInsert[] = array_merge($userTemplate, [
                        'name' => $name,
                        'nim' => $nim,
                        'email' => $email,
                        'role' => UserRole::Mahasiswa->value,
                        'faculty_id' => $facultyId,
                        'study_program_id' => $spId,
                        'group_id' => $assignedGroup?->id,
                    ]);
                }
            }

            // Bulk insert users in chunks of 500
            foreach (array_chunk($usersToInsert, 500) as $chunk) {
                DB::table('users')->insert($chunk);
            }

            // ── 5. Assign DPLs to Groups & Assign Leaders ─────────────
            $dplModels = User::where('role', UserRole::Dpl->value)->get();
            $dplGroupRows = [];

            if ($groupCount > 0 && $dplModels->isNotEmpty()) {
                foreach ($allGroups as $gIdx => $grp) {
                    $primaryDpl = $dplModels[$gIdx % $dplModels->count()];
                    $dplGroupRows[] = [
                        'dpl_id' => $primaryDpl->id,
                        'group_id' => $grp->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    // Co-DPLs for first 3 groups if extra DPLs exist
                    if ($gIdx < 3 && $dplModels->count() > 147) {
                        $coDpl = $dplModels[147 + $gIdx] ?? null;
                        if ($coDpl) {
                            $dplGroupRows[] = [
                                'dpl_id' => $coDpl->id,
                                'group_id' => $grp->id,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }

                    // First student assigned to this group becomes the Student Leader
                    $firstStudent = User::where('group_id', $grp->id)->first();

                    $grp->update([
                        'lead_dpl_id' => $primaryDpl->id,
                        'student_leader_id' => $firstStudent?->id,
                    ]);
                }

                DB::table('dpl_group')->insert($dplGroupRows);
            }

            // ── 6. Real-Like Workflow Scenarios for Demo Groups ────────
            if ($groupCount >= 3) {
                $group1 = $allGroups[0];
                $group2 = $allGroups[1];
                $group3 = $allGroups[2];

                $multidisiplinTypeId = $programTypeMap['multidisiplin'] ?? 1;

                // Group 1: Completed Cycle (Approved LRK & LPK)
                $md1_g1 = Program::create(['group_id' => $group1->id, 'student_id' => null, 'title' => 'Digitalisasi dan Pemetaan Potensi UMKM Desa', 'program_type_id' => $multidisiplinTypeId, 'sequence' => 1]);
                $md2_g1 = Program::create(['group_id' => $group1->id, 'student_id' => null, 'title' => 'Edukasi Pola Hidup Sehat & Pembuatan Apotek Hidup', 'program_type_id' => $multidisiplinTypeId, 'sequence' => 2]);
                $md3_g1 = Program::create(['group_id' => $group1->id, 'student_id' => null, 'title' => 'Video Profil Promosi Potensi Desa', 'program_type_id' => $multidisiplinTypeId, 'sequence' => 3]);

                $g1Students = User::where('group_id', $group1->id)->take(4)->get();
                foreach ($g1Students as $sIdx => $stu) {
                    $part = $md1_g1->participants()->create([
                        'student_id' => $stu->id,
                        'status' => ProgramStatus::Approved,
                        'lpk_status' => ProgramStatus::Approved,
                        'execution_date' => '2026-10-20',
                        'participant_title' => 'Sub-program Digitalisasi '.$stu->name,
                        'role_in_program' => $sIdx === 0 ? 'Ketua Pelaksana' : 'Koordinator Teknis',
                        'responsibility' => 'Perencanaan materi pelatihan dan pendampingan warga.',
                        'achievement' => 'Terlaksana 100% dengan antusiasme tinggi dari 25 pelaku UMKM.',
                        'obstacle' => 'Koneksi internet seluler di balai desa sempat kurang stabil.',
                        'solution' => 'Menggunakan modem portabel dan materi offline cadangan.',
                        'problem_potential' => 'Pelaku UMKM belum memiliki katalog digital dan toko online.',
                        'method' => 'Pelatihan tatap muka dan pendampingan personal',
                        'target_audience' => 'Pelaku UMKM Dusun Krajan',
                        'output_target' => 'Buku Panduan Digitalisasi dan Akun Katalog Digital',
                        'documentation_image_path' => 'https://images.unsplash.com/photo-1531482615713-2afd69097998?w=800',
                        'documentation_caption' => 'Pelaksanaan pendampingan digital marketing UMKM',
                    ]);

                    ParticipantOutput::create([
                        'program_participant_id' => $part->id,
                        'output_code' => 'OUT-'.$part->id,
                        'name' => 'Katalog Digital UMKM Desa',
                        'type' => 'pdf',
                        'url' => 'https://drive.google.com/file/d/1B7sL9kQYgX1rZ',
                    ]);
                }

                // Daily Logs for demo student in Group 1
                $demoStudent = $g1Students->first();
                if ($demoStudent) {
                    $log1 = DailyLog::create([
                        'student_id' => $demoStudent->id,
                        'date' => '2026-10-14',
                        'important_notes' => 'Hari pertama penerjunan KKN. Rapat koordinasi bersama kepala desa dan perangkat.',
                        'status' => LogStatus::Approved,
                    ]);
                    DailyLogActivity::create([
                        'daily_log_id' => $log1->id,
                        'start_time' => '08:00',
                        'end_time' => '11:30',
                        'activity_description' => 'Upacara penerjunan dan temu wicara awal di balai desa.',
                    ]);
                    DailyLogActivity::create([
                        'daily_log_id' => $log1->id,
                        'start_time' => '13:00',
                        'end_time' => '16:30',
                        'activity_description' => 'Observasi lingkungan dusun dan silaturahmi ke tokoh masyarakat.',
                    ]);

                    $log2 = DailyLog::create([
                        'student_id' => $demoStudent->id,
                        'date' => '2026-10-15',
                        'important_notes' => 'Survei awal lokasi UMKM pangan dan identifikasi kebutuhan pelatihan.',
                        'status' => LogStatus::Approved,
                    ]);
                    DailyLogActivity::create([
                        'daily_log_id' => $log2->id,
                        'start_time' => '08:30',
                        'end_time' => '12:00',
                        'activity_description' => 'Wawancara pemilik usaha olahan singkong dan keripik.',
                    ]);

                    // Mentoring Log
                    MentoringLog::create([
                        'group_id' => $group1->id,
                        'student_id' => $demoStudent->id,
                        'program_id' => $md1_g1->id,
                        'date' => '2026-10-15',
                        'topic' => 'Finalisasi Desain Program Kerja Multidisiplin 1',
                        'discussion_summary' => 'Menyepakati materi modul pelatihan, instrumen pre-test, dan pembagian tugas anggota.',
                        'dpl_feedback' => 'Rencana sudah terstruktur. Pastikan materi menggunakan bahasa yang mudah dipahami warga.',
                        'status' => LogStatus::Approved,
                    ]);

                    // Evaluation Grade
                    $leadDpl1 = User::find($group1->lead_dpl_id);
                    if ($leadDpl1) {
                        Grade::create([
                            'student_id' => $demoStudent->id,
                            'dpl_id' => $leadDpl1->id,
                            'aktivitas_partisipatif' => 90.0,
                            'hasil_proyek' => 92.5,
                            'tugas' => 88.0,
                            'quiz' => 85.0,
                            'uts' => 89.0,
                            'uas' => 91.0,
                            'final_grade' => 90.25,
                            'grade_letter' => 'A',
                            'keterangan' => 'Sangat aktif memimpin tim dan menyelesaikan program dengan luaran yang bermanfaat nyata.',
                        ]);
                    }
                }

                // Group 2: LRK Approved, LPK In Progress
                $md1_g2 = Program::create(['group_id' => $group2->id, 'student_id' => null, 'title' => 'Peningkatan Kualitas Pendidikan Dasar dan Literasi', 'program_type_id' => $multidisiplinTypeId, 'sequence' => 1]);
                $g2Students = User::where('group_id', $group2->id)->take(3)->get();
                foreach ($g2Students as $sIdx => $stu) {
                    $md1_g2->participants()->create([
                        'student_id' => $stu->id,
                        'status' => ProgramStatus::Approved,
                        'lpk_status' => ProgramStatus::Draft,
                        'execution_date' => '2026-10-25',
                        'participant_title' => 'Bimbingan Literasi Siswa SD',
                        'role_in_program' => 'Fasilitator',
                        'responsibility' => 'Menyusun silabus baca tulis dan modul cerita anak.',
                        'problem_potential' => 'Minat baca siswa SD masih perlu ditingkatkan.',
                        'method' => 'Belajar interaktif dan pojok baca',
                        'target_audience' => 'Siswa SD kelas 3-5',
                        'output_target' => 'Pojok Baca Mini Balai Desa',
                    ]);
                }

                // Group 3: LRK In Review / Revision
                $md1_g3 = Program::create(['group_id' => $group3->id, 'student_id' => null, 'title' => 'Pengembangan Ekowisata Berbasis Komunitas', 'program_type_id' => $multidisiplinTypeId, 'sequence' => 1]);
                $g3Students = User::where('group_id', $group3->id)->take(3)->get();
                foreach ($g3Students as $sIdx => $stu) {
                    $md1_g3->participants()->create([
                        'student_id' => $stu->id,
                        'status' => $sIdx === 0 ? ProgramStatus::Submitted : ProgramStatus::NeedsRevision,
                        'revision_note' => $sIdx === 0 ? null : 'Mohon perjelas rincian target peserta dan timeline observasi.',
                        'participant_title' => 'Pariwisata Berkelanjutan '.$stu->name,
                        'role_in_program' => 'Konseptor',
                        'responsibility' => 'Mendesain konsep promosi wisata desa.',
                        'problem_potential' => 'Potensi sumber mata air dan pemandangan belum terpromosikan.',
                        'method' => 'Diskusi kelompok terfokus (FGD)',
                        'target_audience' => 'Kelompok Sadar Wisata Desa',
                    ]);
                }
            }
        });
    }
}
