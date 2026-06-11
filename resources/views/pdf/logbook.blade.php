<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Catatan Harian (Logbook)</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .page-break {
            page-break-after: always;
        }
        .text-center {
            text-align: center;
        }
        h2, h3, h4 {
            text-align: center;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        .no-border, .no-border th, .no-border td {
            border: none !important;
            padding: 4px;
        }
        .signature-table {
            width: 100%;
            margin-top: 40px;
            border: none;
        }
        .signature-table td {
            border: none;
            text-align: center;
            width: 50%;
        }
        .header-table {
            width: 100%;
            border: none;
            margin-bottom: 20px;
        }
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        .logo {
            width: 80px;
            height: auto;
        }
    </style>
</head>
<body>

@foreach($logsGroupedByWeek as $weekNumber => $logsInWeek)

    <!-- PENGESAHAN MINGGUAN -->
    <h2>PENGESAHAN BUKU CATATAN HARIAN</h2>
    <h2>MINGGU KE - {{ $weekNumber }}</h2>
    <h2>KULIAH KERJA NYATA</h2>
    <h2>UNIVERSITAS DIPONEGORO</h2>
    <h2>TIM {{ strtoupper($group->name) }}</h2>
    <br><br>

    <table class="no-border" style="width: 100%;">
        <tr>
            <td style="width: 20%;">Desa</td><td style="width: 2%;">:</td><td>{{ $group->village }}</td>
        </tr>
        <tr>
            <td>Kecamatan</td><td>:</td><td>{{ $group->district }}</td>
        </tr>
        <tr>
            <td>Kabupaten</td><td>:</td><td>{{ $group->regency }}</td>
        </tr>
        <tr>
            <td>Provinsi</td><td>:</td><td>{{ $group->province }}</td>
        </tr>
    </table>
    <br>
    <p style="text-align: center;"><strong>Disusun Oleh</strong></p>
    <table class="no-border" style="width: 100%;">
        <tr>
            <td style="width: 20%;">Nama</td><td style="width: 2%;">:</td><td>{{ $student->name }}</td>
        </tr>
        <tr>
            <td>NIM</td><td>:</td><td>{{ $student->nim }}</td>
        </tr>
        <tr>
            <td>Prodi</td><td>:</td><td>{{ $student->prodi }}</td>
        </tr>
        <tr>
            <td>Fakultas</td><td>:</td><td>{{ $student->fakultas }}</td>
        </tr>
    </table>

    <table class="signature-table">
        <tr>
            <td></td>
            <td>{{ $group->village ?? 'Kota' }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td>Disetujui Oleh :<br>Dosen KKN<br><br><br><br><br><strong>{{ $dpl ? $dpl->name : '......................' }}</strong><br>NIP. {{ $dpl ? $dpl->nip : '................' }}</td>
            <td>Pelaksana<br>Mahasiswa<br><br><br><br><br><strong>{{ $student->name }}</strong><br>NIM. {{ $student->nim }}</td>
        </tr>
    </table>

    <div class="page-break"></div>

    <!-- ISI CATATAN HARIAN -->
    @foreach($logsInWeek as $log)
        <table class="header-table">
            <tr>
                <td style="width: 15%; text-align: center;">
                    <!-- Placeholder logo -->
                    <img src="{{ public_path('images/undip-logo.png') }}" alt="Logo" class="logo" style="max-width: 80px;">
                </td>
                <td style="width: 85%; text-align: center;">
                    <h3>BUKU CATATAN HARIAN</h3>
                    <h3>KULIAH KERJA NYATA</h3>
                    <h3>UNIVERSITAS DIPONEGORO</h3>
                    <h3>{{ $group->period ? $group->period->year . '/' . ($group->period->year + 1) : '2024/2025' }}</h3>
                </td>
            </tr>
        </table>
        <hr>

        <p>Hari ke : {{ $log->day_number }} &nbsp;&nbsp;&nbsp;&nbsp; Hari : {{ \Carbon\Carbon::parse($log->date)->translatedFormat('l') }} &nbsp;&nbsp;&nbsp;&nbsp; Tanggal : {{ \Carbon\Carbon::parse($log->date)->translatedFormat('d F Y') }}</p>

        <p><strong>1. Jadwal Kegiatan</strong></p>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 20%;">Waktu</th>
                    <th>Kegiatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($log->activities as $index => $activity)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center;">{{ \Carbon\Carbon::parse($activity->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($activity->end_time)->format('H:i') }}</td>
                    <td>{{ $activity->activity_description }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align: center;">Tidak ada kegiatan</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <p><strong>2. Catatan Penting Harian</strong></p>
        <p style="text-align: justify; border: 1px solid #000; padding: 10px; min-height: 100px;">
            {!! nl2br(e($log->important_notes)) !!}
        </p>

        <table class="signature-table" style="margin-top: 20px;">
            <tr>
                <td></td>
                <td>{{ $group->village ?? 'Kota' }}, {{ \Carbon\Carbon::parse($log->date)->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td></td>
                <td>Pelaksana<br>Mahasiswa<br><br><br><br><br><strong>{{ $student->name }}</strong><br>NIM. {{ $student->nim }}</td>
            </tr>
        </table>

        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    @if (!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

</body>
</html>
