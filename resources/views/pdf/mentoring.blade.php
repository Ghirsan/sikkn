<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Buku Pembimbingan KKN - {{ $student->name }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 15mm 20mm 15mm 20mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .page-break {
            page-break-after: always;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Times New Roman', Times, serif;
        }

        table {
            font-family: 'Times New Roman', Times, serif;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .doc-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 12px;
        }

        .program-meta {
            font-size: 11pt;
            margin-bottom: 6px;
        }

        /* ── Info Box Table (No Borders) ── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 8px;
        }

        .info-table td {
            border: none;
            padding: 1px 0;
            vertical-align: top;
            font-size: 11pt;
            line-height: 1.4;
        }

        /* ── Mentoring Logs Table ── */
        .mentoring-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 14px;
        }

        .mentoring-table th, .mentoring-table td {
            border: 1px solid #000;
            padding: 4px 5px;
            vertical-align: top;
            font-family: 'Times New Roman', Times, serif;
        }

        .mentoring-table th {
            background-color: transparent;
            font-weight: bold;
            text-align: center;
            font-size: 9pt;
            line-height: 1.25;
        }

        .mentoring-table td {
            font-size: 9pt;
            line-height: 1.35;
        }

        .mentoring-table tr {
            page-break-inside: avoid;
        }

        /* ── Signatures ── */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .signature-table td {
            width: 50%;
            text-align: left;
            vertical-align: top;
            font-size: 11pt;
            border: none;
            padding-left: 10px;
            line-height: 1.35;
        }
    </style>
</head>
<body>
    @php
        $rawRegency = trim($group->regency ?? '');
        $cleanRegency = preg_replace('/^(kabupaten|kota|kab\.|kodya)\s+/i', '', $rawRegency);
        $kotaName = $cleanRegency ? ucwords(strtolower($cleanRegency)) : 'Semarang';
        $printDate = \Carbon\Carbon::now()->translatedFormat('d F Y');
    @endphp

    @foreach($groupedLogs as $programId => $logs)
        @php
            $firstLog = $logs->first();
            $program = $firstLog?->program;
            $programType = $program?->programType?->name ?? 'Multidisiplin / Sosial Kemasyarakatan / Lainnya';
            $programTitle = $program?->title ?? ($logs->isNotEmpty() ? 'Kegiatan Umum / Mandiri' : '____________________________________________________');
        @endphp

        <div>
            <div class="doc-title">Buku Pembimbingan Kuliah Kerja Nyata Universitas Diponegoro</div>

            <div class="program-meta">
                <strong>Jenis Program</strong> : {{ $programType }}
            </div>

            <table class="info-table">
                <tr>
                    <td style="width: 50%; padding-right: 25px;">
                        <table style="width: 100%; border-collapse: collapse; border: none;">
                            <tr>
                                <td style="border: none; padding: 2px 0; width: 70px;">Nama</td>
                                <td style="border: none; padding: 2px 0; width: 15px; text-align: center;">:</td>
                                <td style="border: none; padding: 2px 0;">{{ $student->name }}</td>
                            </tr>
                            <tr>
                                <td style="border: none; padding: 2px 0;">NIM</td>
                                <td style="border: none; padding: 2px 0; text-align: center;">:</td>
                                <td style="border: none; padding: 2px 0;">{{ $student->nim }}</td>
                            </tr>
                            <tr>
                                <td style="border: none; padding: 2px 0;">Prodi</td>
                                <td style="border: none; padding: 2px 0; text-align: center;">:</td>
                                <td style="border: none; padding: 2px 0;">{{ $student->prodi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td style="border: none; padding: 2px 0;">Fakultas</td>
                                <td style="border: none; padding: 2px 0; text-align: center;">:</td>
                                <td style="border: none; padding: 2px 0;">{{ $student->fakultas ?? '-' }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 50%; padding-left: 25px;">
                        <table style="width: 100%; border-collapse: collapse; border: none;">
                            <tr>
                                <td style="border: none; padding: 2px 0; width: 80px;">Desa</td>
                                <td style="border: none; padding: 2px 0; width: 15px; text-align: center;">:</td>
                                <td style="border: none; padding: 2px 0;">{{ $group->village ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td style="border: none; padding: 2px 0;">Kecamatan</td>
                                <td style="border: none; padding: 2px 0; text-align: center;">:</td>
                                <td style="border: none; padding: 2px 0;">{{ $group->district ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td style="border: none; padding: 2px 0;">Kabupaten</td>
                                <td style="border: none; padding: 2px 0; text-align: center;">:</td>
                                <td style="border: none; padding: 2px 0;">{{ $group->regency ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td style="border: none; padding: 2px 0;">Provinsi</td>
                                <td style="border: none; padding: 2px 0; text-align: center;">:</td>
                                <td style="border: none; padding: 2px 0;">{{ $group->province ?? '-' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div class="program-meta" style="margin-bottom: 8px;">
                <strong>Judul Program</strong> : {{ $programTitle }}
            </div>

            <table class="mentoring-table">
                <thead>
                    <tr>
                        <th style="width: 3.5%;">No</th>
                        <th style="width: 8.5%;">Tanggal</th>
                        <th style="width: 20%;">Kegiatan</th>
                        <th style="width: 22%;">Uraian &amp; Hambatan</th>
                        <th style="width: 18%;">Solusi / Saran dari Dosen KKN</th>
                        <th style="width: 8%;">Kelompok<br>Sasaran</th>
                        <th style="width: 7.5%;">Jumlah<br>Mahasiswa</th>
                        <th style="width: 12.5%;">Luaran</th>
                    </tr>
                </thead>
                <tbody>
                    @if($logs->isNotEmpty())
                        @foreach($logs as $index => $log)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td class="text-center">{{ $log->date ? $log->date->format('d/m/Y') : '-' }}</td>
                                <td>{{ $log->topic }}</td>
                                <td>{!! nl2br(e($log->discussion_summary)) !!}</td>
                                <td>{!! nl2br(e($log->dpl_feedback ?: '-')) !!}</td>
                                <td>{{ $log->target_group ?: '-' }}</td>
                                <td class="text-center">{{ $log->student_count ?: '-' }}</td>
                                <td>{{ $log->output ?: '-' }}</td>
                            </tr>
                        @endforeach
                    @else
                        {{-- Template baris kosong jika belum ada catatan log --}}
                        @for($i = 1; $i <= 9; $i++)
                            <tr>
                                <td class="text-center" style="height: 22px;">{{ $i }}</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        @endfor
                    @endif
                </tbody>
            </table>

            <table class="signature-table">
                <tr>
                    <td></td>
                    <td style="padding-bottom: 6px;">
                        {{ $kotaName }}, {{ $printDate }}
                    </td>
                </tr>
                <tr>
                    <td>
                        Dosen KKN
                        <div style="height: 60px;"></div>
                        <strong>{{ $dpl?->name ?? '____________________________' }}</strong><br>
                        NIP. {{ $dpl?->nip ?? '________________________' }}
                    </td>
                    <td>
                        Pelaksana
                        <div style="height: 60px;"></div>
                        <strong>{{ $student->name }}</strong><br>
                        NIM. {{ $student->nim }}
                    </td>
                </tr>
            </table>
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
