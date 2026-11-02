<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Buku Pembimbingan KKN - {{ $student->name }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 15mm 15mm 15mm 15mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .page-break {
            page-break-after: always;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .doc-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 12px;
            text-transform: capitalize;
        }

        .program-meta {
            font-size: 10pt;
            margin-bottom: 6px;
        }

        /* ── Info Box Table ── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .info-table td {
            border: 1px solid #000;
            padding: 6px 10px;
            vertical-align: top;
            width: 50%;
            font-size: 9.5pt;
            line-height: 1.5;
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
            padding: 6px 5px;
            vertical-align: top;
        }

        .mentoring-table th {
            background-color: #d9d9d9;
            font-weight: bold;
            text-align: center;
            font-size: 9pt;
        }

        .mentoring-table td {
            font-size: 8.5pt;
            line-height: 1.35;
        }

        .mentoring-table tr {
            page-break-inside: avoid;
        }

        /* ── Signatures ── */
        .date-location {
            text-align: right;
            margin-bottom: 6px;
            font-size: 9.5pt;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .signature-table td {
            width: 50%;
            text-align: left;
            vertical-align: top;
            font-size: 9.5pt;
            padding-left: 10px;
        }

        .signature-line {
            margin: 45px 0 5px 0;
            border-bottom: 1px solid #000;
            width: 220px;
        }
    </style>
</head>
<body>
    @php
        $kota = explode(' ', $group->regency ?? '');
        $kotaName = isset($kota[1]) ? ucfirst(strtolower($kota[1])) : (ucfirst(strtolower($group->regency ?? 'Semarang')) ?: 'Semarang');
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
                    <td>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr><td style="border:none; padding:1px 0; width:75px;">Nama</td><td style="border:none; padding:1px 0; width:10px;">:</td><td style="border:none; padding:1px 0;">{{ $student->name }}</td></tr>
                            <tr><td style="border:none; padding:1px 0;">NIM</td><td style="border:none; padding:1px 0;">:</td><td style="border:none; padding:1px 0;">{{ $student->nim }}</td></tr>
                            <tr><td style="border:none; padding:1px 0;">Prodi</td><td style="border:none; padding:1px 0;">:</td><td style="border:none; padding:1px 0;">{{ $student->prodi ?? '-' }}</td></tr>
                            <tr><td style="border:none; padding:1px 0;">Fakultas</td><td style="border:none; padding:1px 0;">:</td><td style="border:none; padding:1px 0;">{{ $student->fakultas ?? '-' }}</td></tr>
                        </table>
                    </td>
                    <td>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr><td style="border:none; padding:1px 0; width:85px;">Desa</td><td style="border:none; padding:1px 0; width:10px;">:</td><td style="border:none; padding:1px 0;">{{ $group->village ?? '-' }}</td></tr>
                            <tr><td style="border:none; padding:1px 0;">Kecamatan</td><td style="border:none; padding:1px 0;">:</td><td style="border:none; padding:1px 0;">{{ $group->district ?? '-' }}</td></tr>
                            <tr><td style="border:none; padding:1px 0;">Kabupaten</td><td style="border:none; padding:1px 0;">:</td><td style="border:none; padding:1px 0;">{{ $group->regency ?? '-' }}</td></tr>
                            <tr><td style="border:none; padding:1px 0;">Provinsi</td><td style="border:none; padding:1px 0;">:</td><td style="border:none; padding:1px 0;">{{ $group->province ?? '-' }}</td></tr>
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
                        <th style="width: 30px;">No</th>
                        <th style="width: 75px;">Tanggal</th>
                        <th style="width: 130px;">Kegiatan</th>
                        <th>Uraian &amp; Hambatan</th>
                        <th>Solusi / Saran dari Dosen KKN</th>
                        <th style="width: 100px;">Kelompok Sasaran</th>
                        <th style="width: 60px;">Jumlah Mahasiswa</th>
                        <th style="width: 90px;">Luaran</th>
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
                        @for($i = 1; $i <= 8; $i++)
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

            <div class="date-location">
                {{ $kotaName }}, {{ $printDate }}
            </div>

            <table class="signature-table">
                <tr>
                    <td>
                        Dosen KKN
                        <div class="signature-line"></div>
                        <strong>{{ $dpl?->name ?? '____________________________' }}</strong><br>
                        NIP. {{ $dpl?->nip ?? '________________________' }}
                    </td>
                    <td>
                        Pelaksana
                        <div class="signature-line"></div>
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
