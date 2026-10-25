<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GradeExcelService
{
    /**
     * Aspect definitions for DPL bulk grading.
     *
     * @var array<string, string>
     */
    public const ASPECTS = [
        'pembekalan' => 'Pembekalan',
        'gelar_karya' => 'Gelar Karya',
        'kehadiran' => 'Kehadiran',
        'lrk' => 'LRK',
        'integritas' => 'Integritas',
        'sosial_kemasyarakatan' => 'Sosial Kemasyarakatan',
        'lpk' => 'LPK',
        'ujian_akhir' => 'Ujian Akhir',
    ];

    /**
     * SIAP component definitions.
     *
     * @var array<string, string>
     */
    public const SIAP_COMPONENTS = [
        'aktivitas_partisipatif' => 'Aktivitas Partisipatif',
        'hasil_proyek' => 'Hasil Proyek',
        'tugas' => 'Tugas',
        'quiz' => 'Quiz',
        'uts' => 'UTS',
        'uas' => 'UAS',
    ];

    /**
     * Export DPL grading template or filled grades as Excel (.xlsx).
     */
    public function exportDplGrades(Collection $students, ?string $groupName = null): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Penilaian KKN');

        $headers = [
            'No',
            'Nama',
            'NIM',
            'Prodi',
            'Fakultas',
            'Desa',
            'Pembekalan',
            'Gelar Karya',
            'Kehadiran',
            'LRK',
            'Integritas',
            'Sosial Kemasyarakatan',
            'LPK',
            'Ujian Akhir',
            'Keterangan',
        ];

        // Header Row
        $sheet->fromArray($headers, null, 'A1');
        $lastColumnLetter = Coordinate::stringFromColumnIndex(count($headers));

        // Style Header
        $headerRange = "A1:{$lastColumnLetter}1";
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
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Populate Data Rows
        $rowIndex = 2;
        $no = 1;

        foreach ($students as $student) {
            $grade = $student->grade;
            $village = $student->group ? $student->group->village : '-';

            $rowData = [
                $no++,
                $student->name,
                (string) $student->nim,
                $student->prodi ?? '-',
                $student->fakultas ?? '-',
                $village,
                $grade?->pembekalan !== null ? (float) $grade->pembekalan : '',
                $grade?->gelar_karya !== null ? (float) $grade->gelar_karya : '',
                $grade?->kehadiran !== null ? (float) $grade->kehadiran : '',
                $grade?->lrk !== null ? (float) $grade->lrk : '',
                $grade?->integritas !== null ? (float) $grade->integritas : '',
                $grade?->sosial_kemasyarakatan !== null ? (float) $grade->sosial_kemasyarakatan : '',
                $grade?->lpk !== null ? (float) $grade->lpk : '',
                $grade?->ujian_akhir !== null ? (float) $grade->ujian_akhir : '',
                $grade?->keterangan ?? '',
            ];

            $sheet->fromArray($rowData, null, "A{$rowIndex}");

            // Explicitly store NIM as string to prevent scientific notation (E+) in Excel
            $sheet->setCellValueExplicit("C{$rowIndex}", (string) $student->nim, DataType::TYPE_STRING);

            // Center align No, NIM
            $sheet->getStyle("A{$rowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$rowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$rowIndex}")->getNumberFormat()->setFormatCode('@');

            // Right/center align aspect score columns
            $sheet->getStyle("G{$rowIndex}:N{$rowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $rowIndex++;
        }

        // Apply borders to table
        if ($rowIndex > 2) {
            $dataRange = "A1:{$lastColumnLetter}".($rowIndex - 1);
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CBD5E1'],
                    ],
                ],
            ]);
        }

        // Auto-size columns
        for ($col = 1; $col <= count($headers); $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $sanitizedGroupName = $groupName ? str_replace([' ', '/', '\\'], '_', $groupName).'_' : '';
        $filename = 'Format_Nilai_KKN_'.$sanitizedGroupName.now()->format('Ymd_His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Import and validate DPL bulk grading Excel file.
     *
     * @return array{success: bool, errors: array<string>, imported_count: int, skipped_count: int}
     */
    public function importDplGrades(UploadedFile $file, User $dpl, Collection $allowedStudents): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'errors' => ['File tidak dapat dibaca sebagai format Excel yang valid: '.$e->getMessage()],
                'imported_count' => 0,
                'skipped_count' => 0,
            ];
        }

        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        // Find header row (search first 5 rows for 'nim')
        $headerRow = null;
        $columnMap = [];

        for ($r = 1; $r <= min(5, $highestRow); $r++) {
            for ($c = 1; $c <= $highestColumnIndex; $c++) {
                $val = trim((string) $sheet->getCell([$c, $r])->getValue());
                $normalized = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $val));
                if ($normalized === 'nim') {
                    $headerRow = $r;
                    break 2;
                }
            }
        }

        if ($headerRow === null) {
            return [
                'success' => false,
                'errors' => ['Kolom header "NIM" tidak ditemukan pada file Excel. Pastikan format tabel sesuai dengan template.'],
                'imported_count' => 0,
                'skipped_count' => 0,
            ];
        }

        // Map column names to index
        for ($c = 1; $c <= $highestColumnIndex; $c++) {
            $val = trim((string) $sheet->getCell([$c, $headerRow])->getValue());
            $normalized = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $val));
            if ($normalized !== '') {
                $columnMap[$normalized] = $c;
            }
        }

        // Verify required columns exist
        $requiredAspectKeys = array_keys(self::ASPECTS);
        $missingColumns = [];

        foreach ($requiredAspectKeys as $key) {
            $normKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $key));
            if (! isset($columnMap[$normKey])) {
                $missingColumns[] = self::ASPECTS[$key];
            }
        }

        if (! empty($missingColumns)) {
            return [
                'success' => false,
                'errors' => ['Kolom aspek berikut tidak ditemukan di file Excel: '.implode(', ', $missingColumns)],
                'imported_count' => 0,
                'skipped_count' => 0,
            ];
        }

        $studentsByNim = $allowedStudents->keyBy(function (User $s) {
            return trim((string) $s->nim);
        });

        $errors = [];
        $rowsToSave = [];
        $skippedCount = 0;

        // Iterate data rows
        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
            $nimCol = $columnMap['nim'];
            $cell = $sheet->getCell([$nimCol, $row]);
            $nimRaw = $cell->getValue();
            $nim = trim((string) $nimRaw);

            // Normalize scientific notation (e.g. 2.40601E+13) or floats back to plain string
            if (is_numeric($nim) && (stripos($nim, 'e+') !== false || stripos($nim, 'e-') !== false)) {
                $nim = sprintf('%.0f', (float) $nim);
            } elseif (is_float($nimRaw)) {
                $nim = sprintf('%.0f', $nimRaw);
            }

            // Check if entire row is empty
            $isRowEmpty = true;
            for ($c = 1; $c <= $highestColumnIndex; $c++) {
                $cellVal = $sheet->getCell([$c, $row])->getValue();
                if ($cellVal !== null && trim((string) $cellVal) !== '') {
                    $isRowEmpty = false;
                    break;
                }
            }

            if ($isRowEmpty) {
                continue;
            }

            if ($nim === '') {
                $errors[] = "Baris {$row}: Kolom NIM kosong padahal baris memiliki data.";

                continue;
            }

            if (! $studentsByNim->has($nim)) {
                $errors[] = "Baris {$row} (NIM {$nim}): Mahasiswa tidak ditemukan atau tidak berada dalam kelompok bimbingan Anda.";

                continue;
            }

            $student = $studentsByNim->get($nim);

            // Read the 8 aspect scores
            $aspectScores = [];
            $allEmpty = true;
            $anyEmpty = false;

            foreach (self::ASPECTS as $key => $label) {
                $normKey = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $key));
                $colIndex = $columnMap[$normKey];
                $cellVal = $sheet->getCell([$colIndex, $row])->getValue();

                if ($cellVal === null || trim((string) $cellVal) === '') {
                    $anyEmpty = true;
                    $aspectScores[$key] = null;
                } else {
                    $allEmpty = false;
                    // Normalize Indonesian comma decimals (e.g. 85,5 -> 85.5)
                    $cleanedVal = str_replace(',', '.', trim((string) $cellVal));
                    if (! is_numeric($cleanedVal)) {
                        $errors[] = "Baris {$row} (NIM {$nim}): Nilai {$label} harus berupa angka (ditemukan '{$cellVal}').";
                    } else {
                        $floatVal = (float) $cleanedVal;
                        if ($floatVal < 0 || $floatVal > 100) {
                            $errors[] = "Baris {$row} (NIM {$nim}): Nilai {$label} harus berada di antara 0 dan 100 (ditemukan {$floatVal}).";
                        } else {
                            $aspectScores[$key] = round($floatVal, 2);
                        }
                    }
                }
            }

            // Read optional keterangan
            $keterangan = null;
            if (isset($columnMap['keterangan'])) {
                $keteranganVal = $sheet->getCell([$columnMap['keterangan'], $row])->getValue();
                if ($keteranganVal !== null && trim((string) $keteranganVal) !== '') {
                    $keterangan = trim((string) $keteranganVal);
                }
            }

            // Partial completion rule:
            // 1. If all 8 aspect score cells are empty, skip row (leave student untouched)
            if ($allEmpty) {
                $skippedCount++;

                continue;
            }

            // 2. If at least one score is provided, all 8 aspects must be filled
            if ($anyEmpty) {
                foreach (self::ASPECTS as $key => $label) {
                    if ($aspectScores[$key] === null) {
                        $errors[] = "Baris {$row} (NIM {$nim}): Nilai {$label} belum diisi. Jika mahasiswa dinilai, seluruh 8 aspek harus diisi.";
                    }
                }

                continue;
            }

            $rowsToSave[] = [
                'student' => $student,
                'scores' => $aspectScores,
                'keterangan' => $keterangan,
            ];
        }

        // Strict validation: If ANY row had errors, do not proceed
        if (! empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors,
                'imported_count' => 0,
                'skipped_count' => $skippedCount,
            ];
        }

        if (empty($rowsToSave)) {
            return [
                'success' => false,
                'errors' => ['Tidak ada baris nilai mahasiswa yang dimasukkan atau diubah dalam file Excel.'],
                'imported_count' => 0,
                'skipped_count' => $skippedCount,
            ];
        }

        // Commit in atomic transaction
        DB::transaction(function () use ($rowsToSave, $dpl) {
            foreach ($rowsToSave as $item) {
                $student = $item['student'];
                $scores = $item['scores'];
                $keterangan = $item['keterangan'];

                $grade = Grade::firstOrNew([
                    'student_id' => $student->id,
                    'group_id' => $student->group_id,
                ], [
                    'dpl_id' => $dpl->id,
                ]);
                $grade->dpl_id = $dpl->id;

                $grade->pembekalan = $scores['pembekalan'];
                $grade->gelar_karya = $scores['gelar_karya'];
                $grade->kehadiran = $scores['kehadiran'];
                $grade->lrk = $scores['lrk'];
                $grade->integritas = $scores['integritas'];
                $grade->sosial_kemasyarakatan = $scores['sosial_kemasyarakatan'];
                $grade->lpk = $scores['lpk'];
                $grade->ujian_akhir = $scores['ujian_akhir'];
                $grade->keterangan = $keterangan;

                $grade->calculateGrade();
                $grade->save();
            }
        });

        return [
            'success' => true,
            'errors' => [],
            'imported_count' => count($rowsToSave),
            'skipped_count' => $skippedCount,
        ];
    }

    /**
     * Export Faculty SIAP grades as Excel (.xlsx).
     */
    public function exportFacultySiapGrades(Collection $students, ?string $facultyName = null): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Nilai SIAP');

        $headers = [
            'No',
            'Nama',
            'NIM',
            'Prodi',
            'Fakultas',
            'Desa',
            'Aktivitas Partisipatif',
            'Hasil Proyek',
            'Tugas',
            'Quiz',
            'UTS',
            'UAS',
            'Nilai Akhir',
            'Nilai Huruf',
            'Keterangan',
        ];

        // Header Row
        $sheet->fromArray($headers, null, 'A1');
        $lastColumnLetter = Coordinate::stringFromColumnIndex(count($headers));

        // Style Header
        $headerRange = "A1:{$lastColumnLetter}1";
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
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Populate Data Rows
        $rowIndex = 2;
        $no = 1;

        foreach ($students as $student) {
            $grade = $student->grade;
            $village = $student->group ? $student->group->village : '-';

            $rowData = [
                $no++,
                $student->name,
                (string) $student->nim,
                $student->prodi ?? '-',
                $student->fakultas ?? '-',
                $village,
                $grade?->aktivitas_partisipatif !== null ? (float) $grade->aktivitas_partisipatif : '-',
                $grade?->hasil_proyek !== null ? (float) $grade->hasil_proyek : '-',
                $grade?->tugas !== null ? (float) $grade->tugas : '-',
                $grade?->quiz !== null ? (float) $grade->quiz : '-',
                $grade?->uts !== null ? (float) $grade->uts : '-',
                $grade?->uas !== null ? (float) $grade->uas : '-',
                $grade?->final_grade !== null ? (float) $grade->final_grade : '-',
                $grade?->grade_letter ?? '-',
                $grade?->keterangan ?? '-',
            ];

            $sheet->fromArray($rowData, null, "A{$rowIndex}");

            // Explicitly store NIM as string to prevent scientific notation (E+) in Excel
            $sheet->setCellValueExplicit("C{$rowIndex}", (string) $student->nim, DataType::TYPE_STRING);

            // Alignments
            $sheet->getStyle("A{$rowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$rowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$rowIndex}")->getNumberFormat()->setFormatCode('@');
            $sheet->getStyle("G{$rowIndex}:M{$rowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("N{$rowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $rowIndex++;
        }

        // Apply borders
        if ($rowIndex > 2) {
            $dataRange = "A1:{$lastColumnLetter}".($rowIndex - 1);
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CBD5E1'],
                    ],
                ],
            ]);
        }

        // Auto-size columns
        for ($col = 1; $col <= count($headers); $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $sanitizedFacultyName = $facultyName ? str_replace([' ', '/', '\\'], '_', $facultyName).'_' : '';
        $filename = 'Export_Nilai_SIAP_'.$sanitizedFacultyName.now()->format('Ymd_His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
