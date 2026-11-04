<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Logbook KKN - {{ $student->name }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 20mm 20mm 20mm 20mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
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

        .text-center { text-align: center !important; }
        .font-bold { font-weight: bold; }
        
        .cover-page {
            text-align: center;
            padding-top: 30px;
        }
        
        .cover-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            line-height: 1.35;
            margin: 0;
        }

        .meta-table {
            margin: 28px auto;
            border-collapse: collapse;
            border: none;
            width: auto;
        }
        .meta-table td {
            border: none;
            padding: 0;
            text-align: left;
            vertical-align: top;
        }

        .meta-inner-table {
            border-collapse: collapse;
            border: none;
            margin: 0 auto;
            width: 340px;
        }
        .meta-inner-table td {
            border: none;
            padding: 2px 0;
            font-size: 12pt;
            vertical-align: top;
        }
        .meta-label {
            width: 95px;
        }
        .meta-sep {
            width: 15px;
            text-align: center;
        }
        .meta-val {
            text-align: left;
        }

        .signature-table {
            width: 100%;
            margin-top: 35px;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            border: none;
            font-size: 12pt;
            line-height: 1.4;
        }
        .signature-line {
            margin: 60px auto 5px;
            border-bottom: 1px solid #000;
            width: 220px;
        }

        .daily-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .daily-header td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: middle;
        }
        .daily-header .logo {
            width: 90px;
            text-align: center;
        }
        .daily-header .title-center {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            line-height: 1.35;
        }
        .daily-header .meta-right {
            width: 190px;
            font-size: 12pt;
            line-height: 1.45;
        }

        .activity-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 15px;
        }
        .activity-table th, .activity-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: top;
            font-size: 12pt;
            line-height: 1.35;
        }
        .activity-table th {
            text-align: center;
            font-weight: bold;
        }

        .notes-box {
            border: 1px solid #000;
            min-height: 110px;
            padding: 8px 10px;
            margin-top: 8px;
            font-size: 12pt;
            line-height: 1.4;
        }
        
        .notes-image {
            max-width: 100%;
            max-height: 250px;
            display: block;
            margin: 10px auto;
        }

        .section-title {
            font-weight: bold;
            font-size: 12pt;
            margin-top: 15px;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
    @php
        $period = $group->period;
        $tim = $period->semester->value === 'Ganjil' ? 'TIM I' : 'TIM II';
        $ta = $period->year . '/' . ($period->year + 1);
        $kota = explode(' ', $group->regency ?? '');
        $kotaName = isset($kota[1]) ? ucfirst(strtolower($kota[1])) : (ucfirst(strtolower($group->regency ?? 'Semarang')) ?: 'Semarang');
        $printDate = \Carbon\Carbon::now()->translatedFormat('d F Y');

        $timName = trim($group->name ?? '');
        $groupTimName = str_starts_with(strtoupper($timName), 'TIM') ? strtoupper($timName) : ('TIM ' . (strtoupper($timName) ?: '...........'));

        $logoPath = public_path('images/logo-undip-logbook.png');
        if (!file_exists($logoPath)) {
            $logoPath = public_path('images/logo-undip.png');
        }
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
    @endphp

    @foreach($logsGroupedByWeek as $weekNumber => $logs)
        {{-- ================= COVER MINGGUAN ================= --}}
        <div class="cover-page">
            <div class="cover-title">PENGESAHAN BUKU CATATAN HARIAN</div>
            <div class="cover-title">MINGGU KE - {{ $weekNumber }}</div>
            <div style="height: 14pt;"></div>
            <div class="cover-title">KULIAH KERJA NYATA</div>
            <div class="cover-title">UNIVERSITAS DIPONEGORO</div>
            <div class="cover-title">{{ $groupTimName }}</div>

            <table class="meta-table">
                <tr>
                    <td>
                        <table class="meta-inner-table">
                            <tr>
                                <td class="meta-label">Desa</td>
                                <td class="meta-sep">:</td>
                                <td class="meta-val">{{ $group->village ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Kecamatan</td>
                                <td class="meta-sep">:</td>
                                <td class="meta-val">{{ $group->district ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Kabupaten</td>
                                <td class="meta-sep">:</td>
                                <td class="meta-val">{{ $group->regency ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Provinsi</td>
                                <td class="meta-sep">:</td>
                                <td class="meta-val">{{ $group->province ?? '-' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="text-center font-bold" style="padding: 18px 0 10px 0; font-size: 12pt;">
                        Disusun Oleh
                    </td>
                </tr>
                <tr>
                    <td>
                        <table class="meta-inner-table">
                            <tr>
                                <td class="meta-label">Nama</td>
                                <td class="meta-sep">:</td>
                                <td class="meta-val">{{ $student->name }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">NIM</td>
                                <td class="meta-sep">:</td>
                                <td class="meta-val">{{ $student->nim }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Prodi</td>
                                <td class="meta-sep">:</td>
                                <td class="meta-val">{{ $student->prodi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label">Fakultas</td>
                                <td class="meta-sep">:</td>
                                <td class="meta-val">{{ $student->fakultas ?? '-' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <table class="signature-table">
                <tr>
                    <td></td>
                    <td style="padding-bottom: 8px;">
                        {{ $kotaName }}, {{ $printDate }}
                    </td>
                </tr>
                <tr>
                    <td>
                        Disetujui Oleh :<br>
                        Dosen KKN
                        <div class="signature-line"></div>
                        {{ $dpl->name }}<br>
                        NIP. {{ $dpl->nip ?? '-' }}
                    </td>
                    <td>
                        Pelaksana Mahasiswa<br>
                        <br>
                        <div class="signature-line"></div>
                        {{ $student->name }}<br>
                        NIM. {{ $student->nim }}
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="page-break"></div>

        {{-- ================= ISI LOG HARIAN ================= --}}
        @foreach($logs as $log)
            <table class="daily-header">
                <tr>
                    <td class="logo">
                        @if($logoBase64)
                            <img src="{{ $logoBase64 }}" style="width: 70px; height: auto;" alt="Logo Universitas Diponegoro">
                        @else
                            <div style="font-size: 8pt; font-style: italic;">UNIVERSITAS DIPONEGORO</div>
                        @endif
                    </td>
                    <td class="title-center">
                        BUKU CATATAN HARIAN<br>
                        KULIAH KERJA NYATA<br>
                        UNIVERSITAS DIPONEGORO<br>
                        {{ $ta }}
                    </td>
                    <td class="meta-right">
                        Hari ke : {{ $log->day_number }}<br>
                        Hari : {{ $log->date->translatedFormat('l') }}<br>
                        Tanggal : {{ $log->date->translatedFormat('d M Y') }}
                    </td>
                </tr>
            </table>

            <div class="section-title">A. Jadwal Kegiatan</div>
            <table class="activity-table">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="22%">Waktu</th>
                        <th width="73%">Kegiatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($log->activities as $index => $activity)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">
                                {{ \Carbon\Carbon::parse($activity->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($activity->end_time)->format('H:i') }}
                            </td>
                            <td>{{ $activity->activity_description }}</td>
                        </tr>
                    @endforeach
                    @if($log->activities->isEmpty())
                        <tr>
                            <td colspan="3" class="text-center font-italic text-zinc-500">Belum ada aktivitas dicatat.</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <div class="section-title">B. Catatan Penting Harian</div>
            <div class="notes-box">
                @if($log->important_notes)
                    {!! nl2br(e($log->important_notes)) !!}
                @endif

                @if($log->image_path)
                    @php
                        $imageSrc = app(\App\Services\PdfImageResolver::class)->resolve($log->image_path, $log->image_url);
                    @endphp
                    @if($imageSrc)
                        <div style="margin-top: 10px; text-align: center;">
                            <img src="{{ $imageSrc }}" class="notes-image" alt="Lampiran Catatan">
                        </div>
                    @elseif($log->image_url)
                        <div style="margin-top: 10px; font-size: 10pt; font-style: italic;">
                            Lampiran: <a href="{{ $log->image_url }}" target="_blank" style="color: #3b82f6; text-decoration: underline;">{{ $log->image_path }}</a>
                        </div>
                    @endif
                @endif
            </div>

            <table class="signature-table" style="margin-top: 30px;">
                <tr>
                    <td style="width: 50%;"></td>
                    <td style="width: 50%; padding-bottom: 8px;">
                        {{ $kotaName }}, {{ $log->date->translatedFormat('d F Y') }}
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        Pelaksana Mahasiswa
                        <div class="signature-line"></div>
                        {{ $student->name }}<br>
                        NIM. {{ $student->nim }}
                    </td>
                </tr>
            </table>

            @if(!$loop->last)
                <div class="page-break"></div>
            @endif
        @endforeach

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

</body>
</html>
