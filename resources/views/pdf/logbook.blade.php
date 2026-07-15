<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Logbook KKN - {{ $student->name }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .page-break {
            page-break-after: always;
        }

        h1, h2, h3 {
            font-family: Arial, Helvetica, sans-serif;
        }

        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        .cover-page {
            text-align: center;
            padding-top: 40px;
        }
        
        .cover-page h1 { font-size: 14pt; margin-bottom: 5px; }
        .cover-page h2 { font-size: 12pt; margin-bottom: 5px; }

        .meta-table {
            width: 80%;
            margin: 30px auto;
            border-collapse: collapse;
        }
        .meta-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }

        .signature-table { width: 100%; margin-top: 40px; }
        .signature-table td { width: 50%; text-align: center; vertical-align: top; }
        .signature-line { margin: 60px auto 5px; border-bottom: 1px solid #000; width: 220px; }

        .daily-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .daily-header td {
            border: 1px solid #000;
            padding: 10px;
            vertical-align: middle;
        }
        .daily-header .logo { width: 100px; text-align: center; }
        .daily-header .title-center { text-align: center; font-weight: bold; font-size: 12pt; }
        .daily-header .meta-right { width: 180px; font-size: 10pt; line-height: 1.6; }

        .activity-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        .activity-table th, .activity-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }
        .activity-table th {
            text-align: center;
            font-weight: bold;
        }

        .notes-box {
            border: 1px solid #000;
            min-height: 100px;
            padding: 10px;
            margin-top: 10px;
        }
        
        .notes-image {
            max-width: 100%;
            max-height: 300px;
            display: block;
            margin-top: 15px;
        }

        .section-title {
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    @php
        $period = $group->period;
        $tim = $period->semester->value === 'Ganjil' ? 'TIM I' : 'TIM II';
        $ta = $period->year . '/' . ($period->year + 1);
        $kota = explode(' ', $group->regency);
        $kotaName = isset($kota[1]) ? ucfirst(strtolower($kota[1])) : ucfirst(strtolower($group->regency));
        $printDate = \Carbon\Carbon::now()->translatedFormat('d F Y');
    @endphp

    @foreach($logsGroupedByWeek as $weekNumber => $logs)
        {{-- ================= COVER MINGGUAN ================= --}}
        <div class="cover-page">
            <h1 class="font-bold uppercase">PENGESAHAN BUKU CATATAN HARIAN</h1>
            <h2 class="font-bold uppercase">MINGGU KE - {{ $weekNumber }}</h2>
            <br>
            <h1 class="font-bold uppercase">KULIAH KERJA NYATA</h1>
            <h1 class="font-bold uppercase">UNIVERSITAS DIPONEGORO</h1>
            <h2 class="font-bold uppercase">{{ $tim }} TA {{ $ta }}</h2>

            <table class="meta-table">
                <tr>
                    <td>
                        <table>
                            <tr><td width="90">Desa</td><td width="10">:</td><td>{{ $group->village }}</td></tr>
                            <tr><td>Kecamatan</td><td>:</td><td>{{ $group->district }}</td></tr>
                            <tr><td>Kabupaten</td><td>:</td><td>{{ $group->regency }}</td></tr>
                            <tr><td>Provinsi</td><td>:</td><td>{{ $group->province }}</td></tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="text-center font-bold" style="padding: 5px;">Disusun Oleh</td>
                </tr>
                <tr>
                    <td>
                        <table>
                            <tr><td width="90">Nama</td><td width="10">:</td><td>{{ $student->name }}</td></tr>
                            <tr><td>NIM</td><td>:</td><td>{{ $student->nim }}</td></tr>
                            <tr><td>Prodi</td><td>:</td><td>{{ $student->prodi }}</td></tr>
                            <tr><td>Fakultas</td><td>:</td><td>{{ $student->fakultas }}</td></tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div style="text-align: right; margin-right: 10%;">
                {{ $kotaName }}, {{ $printDate }}
            </div>

            <table class="signature-table">
                <tr>
                    <td>
                        Disetujui Oleh :<br>
                        Dosen KKN
                        <div class="signature-line"></div>
                        {{ $dpl->name }}<br>
                        NIP. {{ $dpl->nip ?? '-' }}
                    </td>
                    <td>
                        <br>
                        Pelaksana Mahasiswa
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
                        {{-- Placeholder untuk Logo Undip jika diperlukan, sementara kosong/teks --}}
                        <div style="font-size: 8pt; font-style: italic;">[Logo Undip]</div>
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

            <div class="section-title">1. Jadwal Kegiatan</div>
            <table class="activity-table">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="20%">Waktu</th>
                        <th width="75%">Kegiatan</th>
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

            <div class="section-title">2. Catatan Penting Harian</div>
            <div class="notes-box">
                @if($log->important_notes)
                    {!! nl2br(e($log->important_notes)) !!}
                @endif

                @if($log->image_path)
                    @php
                        $imgPath = storage_path('app/public/' . $log->image_path);
                    @endphp
                    @if(file_exists($imgPath))
                        @php
                            $imgData = base64_encode(file_get_contents($imgPath));
                            $mime = mime_content_type($imgPath);
                            $src = 'data:' . $mime . ';base64,' . $imgData;
                        @endphp
                        <img src="{{ $src }}" class="notes-image" alt="Catatan Penting">
                    @endif
                @endif
            </div>

            <div style="text-align: right; margin-top: 40px; margin-right: 10%;">
                {{ $kotaName }}, {{ $log->date->translatedFormat('d F Y') }}<br>
                Pelaksana Mahasiswa
                <div class="signature-line" style="margin: 60px 0 5px auto; width: 200px;"></div>
                {{ $student->name }}<br>
                NIM. {{ $student->nim }}
            </div>

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
