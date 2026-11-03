<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Faculty;
use App\Models\Group;
use App\Models\GroupType;
use App\Models\Period;
use App\Models\StudyProgram;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GroupAllocationExcelService
{
    /**
     * Common styling for Excel header rows.
     */
    protected function styleHeaderRow($sheet, int $columnCount, int $row = 1): void
    {
        $lastColumnLetter = Coordinate::stringFromColumnIndex($columnCount);
        $headerRange = "A{$row}:{$lastColumnLetter}{$row}";

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'], // Slate 800
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(28);
    }

    /**
     * Common styling for table data rows.
     */
    protected function styleDataRows($sheet, int $columnCount, int $startRow, int $endRow): void
    {
        if ($endRow < $startRow) {
            return;
        }

        $lastColumnLetter = Coordinate::stringFromColumnIndex($columnCount);

        for ($r = $startRow; $r <= $endRow; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(22);
            $bgColor = ($r % 2 === 0) ? 'F8FAFC' : 'FFFFFF'; // Slate 50 zebra

            $sheet->getStyle("A{$r}:{$lastColumnLetter}{$r}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $bgColor],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CBD5E1'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        for ($col = 1; $col <= $columnCount; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }
    }

    /**
     * Clean and normalize a string for header matching.
     */
    protected function normalize(string $value): string
    {
        return strtolower((string) preg_replace('/[^a-zA-Z0-9]/', '', $value));
    }

    /**
     * Helper to test for truthy values (Ya, Yes, 1, True, etc.).
     */
    protected function isTrueValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $str = strtolower(trim((string) $value));

        return in_array($str, ['1', 'ya', 'yes', 'true', 'y', 'ketua', 'utama'], true);
    }

    /**
     * Parse date from string or Excel numeric timestamp.
     */
    protected function parseDate(mixed $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value));
            } catch (\Throwable) {
                // fallback
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    // =========================================================================
    // 1. GROUPS EXPORT & IMPORT
    // =========================================================================

    /**
     * Export Groups template or existing groups for a Period.
     */
    public function exportGroups(Period $period): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Kelompok');

        $headers = [
            'No',
            'Nama Kelompok',
            'Jenis Kelompok',
            'Desa',
            'Kecamatan',
            'Kabupaten',
            'Provinsi',
            'Mitra',
            'Kepala Desa',
            'Tanggal Mulai',
            'Tanggal Selesai',
        ];

        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaderRow($sheet, count($headers));

        $groups = Group::where('period_id', $period->id)
            ->with(['groupType'])
            ->orderBy('name')
            ->get();

        $rowIdx = 2;

        if ($groups->isNotEmpty()) {
            foreach ($groups as $idx => $group) {
                $sheet->setCellValue([1, $rowIdx], $idx + 1);
                $sheet->getCell([2, $rowIdx])->setValueExplicit($group->name, DataType::TYPE_STRING);
                $sheet->setCellValue([3, $rowIdx], $group->groupType?->name ?? 'Reguler');
                $sheet->setCellValue([4, $rowIdx], $group->village ?? '');
                $sheet->setCellValue([5, $rowIdx], $group->district ?? '');
                $sheet->setCellValue([6, $rowIdx], $group->regency ?? '');
                $sheet->setCellValue([7, $rowIdx], $group->province ?? '');
                $sheet->setCellValue([8, $rowIdx], $group->partner_name ?? '');
                $sheet->setCellValue([9, $rowIdx], $group->village_head ?? '');
                $sheet->setCellValue([10, $rowIdx], $group->start_date ? $group->start_date->format('Y-m-d') : ($period->start_date ? $period->start_date->format('Y-m-d') : ''));
                $sheet->setCellValue([11, $rowIdx], $group->end_date ? $group->end_date->format('Y-m-d') : ($period->end_date ? $period->end_date->format('Y-m-d') : ''));
                $rowIdx++;
            }
        } else {
            // Provide a sample row to guide the admin
            $startDate = $period->start_date ? $period->start_date->format('Y-m-d') : date('Y-m-d');
            $endDate = $period->end_date ? $period->end_date->format('Y-m-d') : date('Y-m-d', strtotime('+45 days'));

            $sampleRow = [
                1,
                'Kelompok 1',
                'Reguler',
                'Desa Sukamaju',
                'Kecamatan Karanganyar',
                'Kabupaten Semarang',
                'Jawa Tengah',
                'Bumdes Maju Bersama',
                'Bpk. Suparno',
                $startDate,
                $endDate,
            ];

            foreach ($sampleRow as $colIdx => $val) {
                if ($colIdx === 1) {
                    $sheet->getCell([$colIdx + 1, $rowIdx])->setValueExplicit($val, DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue([$colIdx + 1, $rowIdx], $val);
                }
            }
            $rowIdx++;
        }

        $this->styleDataRows($sheet, count($headers), 2, $rowIdx - 1);

        $sanitizedPeriod = str_replace([' ', '/', '\\'], '_', $period->name ?: 'Periode_'.$period->year);
        $filename = 'Format_Kelompok_'.$sanitizedPeriod.'_'.now()->format('Ymd_His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Import Groups from Excel for a Period.
     *
     * @return array{success: bool, errors: array<string>, imported_count: int, updated_count: int}
     */
    public function importGroups(UploadedFile $file, Period $period): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'errors' => ['File tidak dapat dibaca sebagai format Excel yang valid: '.$e->getMessage()],
                'imported_count' => 0,
                'updated_count' => 0,
            ];
        }

        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        // Find header row (must contain 'namakelompok')
        $headerRow = null;
        for ($r = 1; $r <= min(5, $highestRow); $r++) {
            for ($c = 1; $c <= $highestColumnIndex; $c++) {
                $val = $this->normalize((string) $sheet->getCell([$c, $r])->getValue());
                if ($val === 'namakelompok' || $val === 'kelompok') {
                    $headerRow = $r;
                    break 2;
                }
            }
        }

        if ($headerRow === null) {
            return [
                'success' => false,
                'errors' => ['Kolom header "Nama Kelompok" tidak ditemukan pada file Excel. Pastikan menggunakan format template yang disediakan.'],
                'imported_count' => 0,
                'updated_count' => 0,
            ];
        }

        $columnMap = [];
        for ($c = 1; $c <= $highestColumnIndex; $c++) {
            $val = $this->normalize((string) $sheet->getCell([$c, $headerRow])->getValue());
            if ($val !== '') {
                $columnMap[$val] = $c;
            }
        }

        $nameCol = $columnMap['namakelompok'] ?? $columnMap['kelompok'] ?? null;
        if (! $nameCol) {
            return [
                'success' => false,
                'errors' => ['Kolom "Nama Kelompok" wajib ada pada file Excel.'],
                'imported_count' => 0,
                'updated_count' => 0,
            ];
        }

        $typeCol = $columnMap['jeniskelompok'] ?? $columnMap['jenis'] ?? null;
        $villageCol = $columnMap['desa'] ?? $columnMap['kelurahan'] ?? null;
        $districtCol = $columnMap['kecamatan'] ?? null;
        $regencyCol = $columnMap['kabupaten'] ?? $columnMap['kota'] ?? null;
        $provinceCol = $columnMap['provinsi'] ?? null;
        $partnerCol = $columnMap['mitra'] ?? null;
        $headCol = $columnMap['kepaladesa'] ?? $columnMap['lurah'] ?? null;
        $startCol = $columnMap['tanggalmulai'] ?? $columnMap['mulai'] ?? null;
        $endCol = $columnMap['tanggalselesai'] ?? $columnMap['selesai'] ?? null;

        // Preload group types
        $groupTypes = GroupType::all()->keyBy(fn ($gt) => $this->normalize($gt->name));
        $defaultGroupType = GroupType::first();

        $errors = [];
        $importedCount = 0;
        $updatedCount = 0;

        DB::beginTransaction();
        try {
            for ($r = $headerRow + 1; $r <= $highestRow; $r++) {
                $name = trim((string) $sheet->getCell([$nameCol, $r])->getValue());
                if ($name === '') {
                    continue; // Skip empty rows
                }

                $typeName = $typeCol ? trim((string) $sheet->getCell([$typeCol, $r])->getValue()) : '';
                $groupType = $groupTypes->get($this->normalize($typeName)) ?? $defaultGroupType;

                $village = $villageCol ? trim((string) $sheet->getCell([$villageCol, $r])->getValue()) : null;
                $district = $districtCol ? trim((string) $sheet->getCell([$districtCol, $r])->getValue()) : null;
                $regency = $regencyCol ? trim((string) $sheet->getCell([$regencyCol, $r])->getValue()) : null;
                $province = $provinceCol ? trim((string) $sheet->getCell([$provinceCol, $r])->getValue()) : null;
                $partner = $partnerCol ? trim((string) $sheet->getCell([$partnerCol, $r])->getValue()) : null;
                $head = $headCol ? trim((string) $sheet->getCell([$headCol, $r])->getValue()) : null;

                $startDate = $startCol ? $this->parseDate($sheet->getCell([$startCol, $r])->getValue()) : null;
                $endDate = $endCol ? $this->parseDate($sheet->getCell([$endCol, $r])->getValue()) : null;

                $startDate = $startDate ?? $period->start_date;
                $endDate = $endDate ?? $period->end_date;

                $group = Group::where('period_id', $period->id)
                    ->where('name', $name)
                    ->first();

                if ($group) {
                    $group->update([
                        'group_type_id' => $groupType?->id,
                        'village' => $village ?: $group->village,
                        'district' => $district ?: $group->district,
                        'regency' => $regency ?: $group->regency,
                        'province' => $province ?: $group->province,
                        'partner_name' => $partner ?: $group->partner_name,
                        'village_head' => $head ?: $group->village_head,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ]);
                    $updatedCount++;
                } else {
                    Group::create([
                        'period_id' => $period->id,
                        'name' => $name,
                        'group_type_id' => $groupType?->id,
                        'village' => $village ?? '',
                        'district' => $district ?? '',
                        'regency' => $regency ?? '',
                        'province' => $province ?? '',
                        'partner_name' => $partner,
                        'village_head' => $head,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ]);
                    $importedCount++;
                }
            }

            DB::commit();

            return [
                'success' => true,
                'errors' => $errors,
                'imported_count' => $importedCount,
                'updated_count' => $updatedCount,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            return [
                'success' => false,
                'errors' => ['Terjadi kesalahan saat menyimpan data kelompok: '.$e->getMessage()],
                'imported_count' => 0,
                'updated_count' => 0,
            ];
        }
    }

    // =========================================================================
    // 2. MAHASISWA EXPORT & IMPORT
    // =========================================================================

    /**
     * Export Mahasiswa template or existing allocations for a Period.
     */
    public function exportStudents(Period $period): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Mahasiswa');

        $headers = [
            'No',
            'NIM',
            'Nama Mahasiswa',
            'Email',
            'Fakultas',
            'Program Studi',
            'No HP',
            'Nama Kelompok',
            'Ketua Kelompok',
        ];

        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaderRow($sheet, count($headers));

        $students = User::where('role', UserRole::Mahasiswa)
            ->whereHas('group', fn ($q) => $q->where('period_id', $period->id))
            ->with(['group', 'faculty', 'studyProgram'])
            ->orderBy('name')
            ->get();

        $rowIdx = 2;

        if ($students->isNotEmpty()) {
            foreach ($students as $idx => $student) {
                $isLeader = $student->group && $student->group->student_leader_id === $student->id;

                $sheet->setCellValue([1, $rowIdx], $idx + 1);
                $sheet->getCell([2, $rowIdx])->setValueExplicit($student->nim, DataType::TYPE_STRING);
                $sheet->setCellValue([3, $rowIdx], $student->name);
                $sheet->setCellValue([4, $rowIdx], $student->email);
                $sheet->setCellValue([5, $rowIdx], $student->faculty?->short_name ?? $student->faculty?->name ?? '');
                $sheet->setCellValue([6, $rowIdx], $student->studyProgram?->name ?? '');
                $sheet->getCell([7, $rowIdx])->setValueExplicit($student->phone ?? '', DataType::TYPE_STRING);
                $sheet->getCell([8, $rowIdx])->setValueExplicit($student->group?->name ?? '', DataType::TYPE_STRING);
                $sheet->setCellValue([9, $rowIdx], $isLeader ? 'Ya' : 'Tidak');
                $rowIdx++;
            }
        } else {
            // Sample row with existing group name from period if available
            $sampleGroup = Group::where('period_id', $period->id)->first()?->name ?? 'Kelompok 1';

            $sampleRow = [
                1,
                '24060120120001',
                'Ahmad Fauzi',
                'ahmad.fauzi@students.ac.id',
                'FSM',
                'Informatika S1',
                '081234567890',
                $sampleGroup,
                'Ya',
            ];

            foreach ($sampleRow as $colIdx => $val) {
                if (in_array($colIdx, [1, 6, 7], true)) {
                    $sheet->getCell([$colIdx + 1, $rowIdx])->setValueExplicit((string) $val, DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue([$colIdx + 1, $rowIdx], $val);
                }
            }
            $rowIdx++;
        }

        $this->styleDataRows($sheet, count($headers), 2, $rowIdx - 1);

        $sanitizedPeriod = str_replace([' ', '/', '\\'], '_', $period->name ?: 'Periode_'.$period->year);
        $filename = 'Format_Mahasiswa_'.$sanitizedPeriod.'_'.now()->format('Ymd_His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Import Mahasiswa allocations from Excel for a Period.
     *
     * @return array{success: bool, errors: array<string>, imported_count: int, updated_count: int}
     */
    public function importStudents(UploadedFile $file, Period $period): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'errors' => ['File tidak dapat dibaca sebagai format Excel yang valid: '.$e->getMessage()],
                'imported_count' => 0,
                'updated_count' => 0,
            ];
        }

        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        // Find header row (must contain 'nim')
        $headerRow = null;
        for ($r = 1; $r <= min(5, $highestRow); $r++) {
            for ($c = 1; $c <= $highestColumnIndex; $c++) {
                $val = $this->normalize((string) $sheet->getCell([$c, $r])->getValue());
                if ($val === 'nim') {
                    $headerRow = $r;
                    break 2;
                }
            }
        }

        if ($headerRow === null) {
            return [
                'success' => false,
                'errors' => ['Kolom header "NIM" tidak ditemukan pada file Excel. Pastikan format tabel sesuai template.'],
                'imported_count' => 0,
                'updated_count' => 0,
            ];
        }

        $columnMap = [];
        for ($c = 1; $c <= $highestColumnIndex; $c++) {
            $val = $this->normalize((string) $sheet->getCell([$c, $headerRow])->getValue());
            if ($val !== '') {
                $columnMap[$val] = $c;
            }
        }

        $nimCol = $columnMap['nim'] ?? null;
        $nameCol = $columnMap['namamahasiswa'] ?? $columnMap['nama'] ?? null;
        $emailCol = $columnMap['email'] ?? null;
        $groupCol = $columnMap['namakelompok'] ?? $columnMap['kelompok'] ?? null;
        $facultyCol = $columnMap['fakultas'] ?? null;
        $prodiCol = $columnMap['programstudi'] ?? $columnMap['prodi'] ?? null;
        $phoneCol = $columnMap['nohp'] ?? $columnMap['telepon'] ?? $columnMap['hp'] ?? null;
        $leaderCol = $columnMap['ketuakelompok'] ?? $columnMap['ketua'] ?? null;

        if (! $nimCol || ! $nameCol || ! $groupCol) {
            return [
                'success' => false,
                'errors' => ['Kolom "NIM", "Nama Mahasiswa", dan "Nama Kelompok" wajib ada pada file Excel.'],
                'imported_count' => 0,
                'updated_count' => 0,
            ];
        }

        // Preload groups for this period
        $groups = Group::where('period_id', $period->id)->get()->keyBy(fn ($g) => $this->normalize($g->name));

        // Preload faculties & study programs
        $faculties = Faculty::all();
        $studyPrograms = StudyProgram::all();

        $errors = [];
        $importedCount = 0;
        $updatedCount = 0;
        $defaultPassword = Hash::make('password');

        DB::beginTransaction();
        try {
            for ($r = $headerRow + 1; $r <= $highestRow; $r++) {
                $nim = trim((string) $sheet->getCell([$nimCol, $r])->getValue());
                $name = trim((string) $sheet->getCell([$nameCol, $r])->getValue());
                $groupName = trim((string) $sheet->getCell([$groupCol, $r])->getValue());

                if ($nim === '' && $name === '' && $groupName === '') {
                    continue; // Skip empty row
                }

                if ($nim === '' || $name === '') {
                    $errors[] = "Baris {$r}: NIM dan Nama Mahasiswa wajib diisi.";

                    continue;
                }

                if ($groupName === '') {
                    $errors[] = "Baris {$r} ({$name}): Nama Kelompok wajib diisi.";

                    continue;
                }

                $group = $groups->get($this->normalize($groupName));
                if (! $group) {
                    $errors[] = "Baris {$r} ({$name}): Kelompok \"{$groupName}\" tidak ditemukan pada periode KKN ini. Buat kelompok terlebih dahulu di menu Kelompok.";

                    continue;
                }

                // Resolve Study Program & Faculty
                $prodiName = $prodiCol ? trim((string) $sheet->getCell([$prodiCol, $r])->getValue()) : '';
                $facultyName = $facultyCol ? trim((string) $sheet->getCell([$facultyCol, $r])->getValue()) : '';

                $studyProgram = null;
                if ($prodiName !== '') {
                    $normProdi = $this->normalize($prodiName);
                    $studyProgram = $studyPrograms->first(function ($sp) use ($normProdi) {
                        return $this->normalize($sp->name) === $normProdi
                            || $this->normalize($sp->code) === $normProdi
                            || str_contains($this->normalize($sp->name), $normProdi);
                    });
                }

                $faculty = null;
                if ($studyProgram && $studyProgram->faculty_id) {
                    $faculty = $studyProgram->faculty;
                } elseif ($facultyName !== '') {
                    $normFac = $this->normalize($facultyName);
                    $faculty = $faculties->first(function ($f) use ($normFac) {
                        return $this->normalize($f->name) === $normFac
                            || $this->normalize($f->short_name ?? '') === $normFac
                            || $this->normalize($f->code) === $normFac;
                    });
                }

                $phone = $phoneCol ? trim((string) $sheet->getCell([$phoneCol, $r])->getValue()) : null;
                $email = $emailCol ? trim((string) $sheet->getCell([$emailCol, $r])->getValue()) : '';
                if ($email === '') {
                    $email = strtolower($nim).'@student.sikkn.ac.id';
                }

                $isLeader = $leaderCol ? $this->isTrueValue($sheet->getCell([$leaderCol, $r])->getValue()) : false;

                // Find existing user by NIM or Email
                $user = User::where('nim', $nim)->orWhere('email', $email)->first();

                if ($user) {
                    $user->update([
                        'name' => $name,
                        'email' => $user->email === $email ? $user->email : $email,
                        'role' => UserRole::Mahasiswa,
                        'nim' => $nim,
                        'group_id' => $group->id,
                        'faculty_id' => $faculty?->id ?? $user->faculty_id,
                        'study_program_id' => $studyProgram?->id ?? $user->study_program_id,
                        'phone' => $phone ?: $user->phone,
                    ]);
                    $updatedCount++;
                } else {
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => $defaultPassword,
                        'role' => UserRole::Mahasiswa,
                        'nim' => $nim,
                        'group_id' => $group->id,
                        'faculty_id' => $faculty?->id,
                        'study_program_id' => $studyProgram?->id,
                        'phone' => $phone,
                    ]);
                    $importedCount++;
                }

                if ($isLeader) {
                    $group->update(['student_leader_id' => $user->id]);
                }
            }

            if (! empty($errors) && $importedCount === 0 && $updatedCount === 0) {
                DB::rollBack();

                return [
                    'success' => false,
                    'errors' => $errors,
                    'imported_count' => 0,
                    'updated_count' => 0,
                ];
            }

            DB::commit();

            return [
                'success' => true,
                'errors' => $errors,
                'imported_count' => $importedCount,
                'updated_count' => $updatedCount,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            return [
                'success' => false,
                'errors' => ['Terjadi kesalahan saat menyimpan data mahasiswa: '.$e->getMessage()],
                'imported_count' => 0,
                'updated_count' => 0,
            ];
        }
    }

    // =========================================================================
    // 3. DPL EXPORT & IMPORT
    // =========================================================================

    /**
     * Export DPL template or existing group assignments for a Period.
     * Supports DPL with multiple groups by listing them in separate rows.
     */
    public function exportDpls(Period $period): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar DPL');

        $headers = [
            'No',
            'NIP',
            'Nama DPL',
            'Email',
            'Program Studi',
            'No HP',
            'Nama Kelompok',
            'DPL Utama',
        ];

        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaderRow($sheet, count($headers));

        $groups = Group::where('period_id', $period->id)
            ->with(['dpls.studyProgram', 'leadDpl'])
            ->orderBy('name')
            ->get();

        $rowIdx = 2;
        $counter = 1;

        if ($groups->isNotEmpty() && $groups->some(fn ($g) => $g->dpls->isNotEmpty())) {
            foreach ($groups as $group) {
                foreach ($group->dpls as $dpl) {
                    $isLead = $group->lead_dpl_id === $dpl->id;

                    $sheet->setCellValue([1, $rowIdx], $counter++);
                    $sheet->getCell([2, $rowIdx])->setValueExplicit($dpl->nip ?? '', DataType::TYPE_STRING);
                    $sheet->setCellValue([3, $rowIdx], $dpl->name);
                    $sheet->setCellValue([4, $rowIdx], $dpl->email);
                    $sheet->setCellValue([5, $rowIdx], $dpl->studyProgram?->name ?? '');
                    $sheet->getCell([6, $rowIdx])->setValueExplicit($dpl->phone ?? '', DataType::TYPE_STRING);
                    $sheet->getCell([7, $rowIdx])->setValueExplicit($group->name, DataType::TYPE_STRING);
                    $sheet->setCellValue([8, $rowIdx], $isLead ? 'Ya' : 'Tidak');
                    $rowIdx++;
                }
            }
        } else {
            // Provide sample rows demonstrating multi-group supervision
            $sampleGroup1 = $groups->first()?->name ?? 'Kelompok 1';
            $sampleGroup2 = $groups->skip(1)->first()?->name ?? 'Kelompok 2';

            $sampleRows = [
                [1, '198001012005011001', 'Dr. Ir. Budi Santoso, M.T.', 'budi.santoso@undip.ac.id', 'Teknik Mesin S1', '081234567891', $sampleGroup1, 'Ya'],
                [2, '198001012005011001', 'Dr. Ir. Budi Santoso, M.T.', 'budi.santoso@undip.ac.id', 'Teknik Mesin S1', '081234567891', $sampleGroup2, 'Ya'],
            ];

            foreach ($sampleRows as $row) {
                foreach ($row as $colIdx => $val) {
                    if (in_array($colIdx, [1, 5, 6], true)) {
                        $sheet->getCell([$colIdx + 1, $rowIdx])->setValueExplicit((string) $val, DataType::TYPE_STRING);
                    } else {
                        $sheet->setCellValue([$colIdx + 1, $rowIdx], $val);
                    }
                }
                $rowIdx++;
            }
        }

        $this->styleDataRows($sheet, count($headers), 2, $rowIdx - 1);

        $sanitizedPeriod = str_replace([' ', '/', '\\'], '_', $period->name ?: 'Periode_'.$period->year);
        $filename = 'Format_DPL_'.$sanitizedPeriod.'_'.now()->format('Ymd_His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Import DPL allocations from Excel for a Period.
     * Supports multi-group DPL rows.
     *
     * @return array{success: bool, errors: array<string>, imported_count: int, updated_count: int}
     */
    public function importDpls(UploadedFile $file, Period $period): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'errors' => ['File tidak dapat dibaca sebagai format Excel yang valid: '.$e->getMessage()],
                'imported_count' => 0,
                'updated_count' => 0,
            ];
        }

        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        // Find header row (must contain 'nip')
        $headerRow = null;
        for ($r = 1; $r <= min(5, $highestRow); $r++) {
            for ($c = 1; $c <= $highestColumnIndex; $c++) {
                $val = $this->normalize((string) $sheet->getCell([$c, $r])->getValue());
                if ($val === 'nip') {
                    $headerRow = $r;
                    break 2;
                }
            }
        }

        if ($headerRow === null) {
            return [
                'success' => false,
                'errors' => ['Kolom header "NIP" tidak ditemukan pada file Excel. Pastikan format tabel sesuai template.'],
                'imported_count' => 0,
                'updated_count' => 0,
            ];
        }

        $columnMap = [];
        for ($c = 1; $c <= $highestColumnIndex; $c++) {
            $val = $this->normalize((string) $sheet->getCell([$c, $headerRow])->getValue());
            if ($val !== '') {
                $columnMap[$val] = $c;
            }
        }

        $nipCol = $columnMap['nip'] ?? null;
        $nameCol = $columnMap['namadpl'] ?? $columnMap['nama'] ?? null;
        $emailCol = $columnMap['email'] ?? null;
        $groupCol = $columnMap['namakelompok'] ?? $columnMap['kelompok'] ?? null;
        $prodiCol = $columnMap['programstudi'] ?? $columnMap['prodi'] ?? null;
        $phoneCol = $columnMap['nohp'] ?? $columnMap['telepon'] ?? $columnMap['hp'] ?? null;
        $leadCol = $columnMap['dplutama'] ?? $columnMap['utama'] ?? $columnMap['ketua'] ?? null;

        if (! $nipCol || ! $nameCol || ! $groupCol) {
            return [
                'success' => false,
                'errors' => ['Kolom "NIP", "Nama DPL", dan "Nama Kelompok" wajib ada pada file Excel.'],
                'imported_count' => 0,
                'updated_count' => 0,
            ];
        }

        // Preload groups for this period
        $groups = Group::where('period_id', $period->id)->get()->keyBy(fn ($g) => $this->normalize($g->name));

        // Preload study programs
        $studyPrograms = StudyProgram::all();

        $errors = [];
        $importedCount = 0;
        $updatedCount = 0;
        $defaultPassword = Hash::make('password');

        DB::beginTransaction();
        try {
            for ($r = $headerRow + 1; $r <= $highestRow; $r++) {
                $nip = trim((string) $sheet->getCell([$nipCol, $r])->getValue());
                $name = trim((string) $sheet->getCell([$nameCol, $r])->getValue());
                $groupName = trim((string) $sheet->getCell([$groupCol, $r])->getValue());

                if ($nip === '' && $name === '' && $groupName === '') {
                    continue; // Skip empty row
                }

                if ($nip === '' || $name === '') {
                    $errors[] = "Baris {$r}: NIP dan Nama DPL wajib diisi.";

                    continue;
                }

                if ($groupName === '') {
                    $errors[] = "Baris {$r} ({$name}): Nama Kelompok wajib diisi.";

                    continue;
                }

                $group = $groups->get($this->normalize($groupName));
                if (! $group) {
                    $errors[] = "Baris {$r} ({$name}): Kelompok \"{$groupName}\" tidak ditemukan pada periode KKN ini. Buat kelompok terlebih dahulu di menu Kelompok.";

                    continue;
                }

                $prodiName = $prodiCol ? trim((string) $sheet->getCell([$prodiCol, $r])->getValue()) : '';
                $studyProgram = null;
                if ($prodiName !== '') {
                    $normProdi = $this->normalize($prodiName);
                    $studyProgram = $studyPrograms->first(function ($sp) use ($normProdi) {
                        return $this->normalize($sp->name) === $normProdi
                            || $this->normalize($sp->code) === $normProdi
                            || str_contains($this->normalize($sp->name), $normProdi);
                    });
                }

                $phone = $phoneCol ? trim((string) $sheet->getCell([$phoneCol, $r])->getValue()) : null;
                $email = $emailCol ? trim((string) $sheet->getCell([$emailCol, $r])->getValue()) : '';
                if ($email === '') {
                    $email = strtolower($nip).'@lecturer.sikkn.ac.id';
                }

                $isLead = $leadCol ? $this->isTrueValue($sheet->getCell([$leadCol, $r])->getValue()) : false;

                // Find existing lecturer by NIP or Email
                $user = User::where('nip', $nip)->orWhere('email', $email)->first();

                if ($user) {
                    $user->update([
                        'name' => $name,
                        'email' => $user->email === $email ? $user->email : $email,
                        'role' => UserRole::Dpl,
                        'nip' => $nip,
                        'study_program_id' => $studyProgram?->id ?? $user->study_program_id,
                        'faculty_id' => $studyProgram?->faculty_id ?? $user->faculty_id,
                        'phone' => $phone ?: $user->phone,
                    ]);
                    $updatedCount++;
                } else {
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => $defaultPassword,
                        'role' => UserRole::Dpl,
                        'nip' => $nip,
                        'study_program_id' => $studyProgram?->id,
                        'faculty_id' => $studyProgram?->faculty_id,
                        'phone' => $phone,
                    ]);
                    $importedCount++;
                }

                // Associate DPL with the group (many-to-many pivot)
                $group->dpls()->syncWithoutDetaching([$user->id]);

                // If marked as lead DPL, update lead_dpl_id
                if ($isLead) {
                    $group->update(['lead_dpl_id' => $user->id]);
                }
            }

            if (! empty($errors) && $importedCount === 0 && $updatedCount === 0) {
                DB::rollBack();

                return [
                    'success' => false,
                    'errors' => $errors,
                    'imported_count' => 0,
                    'updated_count' => 0,
                ];
            }

            DB::commit();

            return [
                'success' => true,
                'errors' => $errors,
                'imported_count' => $importedCount,
                'updated_count' => $updatedCount,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            return [
                'success' => false,
                'errors' => ['Terjadi kesalahan saat menyimpan data DPL: '.$e->getMessage()],
                'imported_count' => 0,
                'updated_count' => 0,
            ];
        }
    }
}
