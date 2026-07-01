<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Rencana Kegiatan - {{ $group->name }}</title>
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

        .section-title {
            font-size: 14pt;
            font-weight: bold;
            margin: 1.5rem 0 1rem;
            text-transform: uppercase;
        }

        .subsection-title {
            font-size: 12pt;
            font-weight: bold;
            margin: 1.5rem 0 0.5rem;
        }

        /* ── Tables ── */
        .lrk-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0.8rem 0;
            font-size: 10pt;
        }
        .lrk-table th, .lrk-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: top;
            text-align: left;
        }
        .lrk-table th {
            background: #d9d9d9;
            font-weight: bold;
            text-align: center;
        }

        /* ── Cover ── */
        .cover-page {
            text-align: center;
            padding-top: 60px;
        }
        .cover-page h1 { font-size: 16pt; font-weight: bold; margin: 0.5rem 0; }
        .cover-page h2 { font-size: 14pt; font-weight: bold; margin: 0.3rem 0; }
        .institution { font-size: 12pt; font-weight: bold; margin-top: 50px; }

        /* ── Signatures ── */
        .signature-table { width: 100%; margin-top: 2rem; }
        .signature-table td { width: 50%; text-align: center; vertical-align: top; }
        .signature-line { margin: 70px auto 5px; border-bottom: 1px solid #000; width: 200px; }

        /* ── Helpers ── */
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>

    {{-- ══════════════════════════════════════════ --}}
    {{-- HALAMAN JUDUL (Cover)                     --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="cover-page">
        <h1>LAPORAN RENCANA KEGIATAN</h1>
        <h2>KULIAH KERJA NYATA {{ $period->semester->value === 'Ganjil' ? 'TIM I' : 'TIM II' }} TA {{ $period->year }}/{{ $period->year + 1 }}</h2>

        @if($group->partner_name)
            <p style="font-size:13pt; font-weight:bold; margin-top:1rem;">{{ strtoupper($group->partner_name) }}</p>
        @endif

        <div style="margin-top:2rem; font-size:12pt; line-height:2;">
            <p>KELURAHAN/DESA {{ strtoupper($group->village) }} KECAMATAN {{ strtoupper($group->district) }}</p>
            <p>KOTA/KABUPATEN {{ strtoupper($group->regency) }}</p>
        </div>

        <div style="margin-top:2rem;">
            <p style="font-weight:bold;">Oleh :</p>
            <table style="margin:1rem auto; border-collapse:collapse;">
                <tr>
                    <td style="padding:4px 24px; vertical-align:top; border:none;">
                        @foreach($students->take((int) ceil($students->count() / 2)) as $s)
                            <div>{{ $s->name }} / {{ $s->nim }}</div>
                        @endforeach
                    </td>
                    <td style="padding:4px 24px; vertical-align:top; border:none;">
                        @foreach($students->skip((int) ceil($students->count() / 2)) as $s)
                            <div>{{ $s->name }} / {{ $s->nim }}</div>
                        @endforeach
                    </td>
                </tr>
            </table>
        </div>

        <div class="institution">
            <p>PUSAT PELAYANAN KULIAH KERJA NYATA (P2KKN)</p>
            <p>LEMBAGA PENELITIAN DAN PENGABDIAN KEPADA MASYARAKAT (LPPM)</p>
            <p>UNIVERSITAS DIPONEGORO</p>
            <p>SEMARANG</p>
            <p>{{ $period->year }}</p>
        </div>
    </div>

    <div class="page-break"></div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- HALAMAN PENGESAHAN                        --}}
    {{-- ══════════════════════════════════════════ --}}
    <h1 class="section-title text-center">HALAMAN PENGESAHAN</h1>

    <p>
        Tim KKN {{ $period->semester->value === 'Ganjil' ? 'Tim I' : 'Tim II' }} TA {{ $period->year }}/{{ $period->year + 1 }}
        @if($group->partner_name) pada {{ $group->partner_name }} @endif
        di Desa {{ $group->village }} Kecamatan {{ $group->district }} Kota/Kabupaten {{ $group->regency }}.
    </p>

    <table class="lrk-table">
        <thead>
            <tr>
                <th style="width:40px;">No</th>
                <th>Nama</th>
                <th>NIM</th>
                <th style="width:150px;">Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $i => $student)
                <tr>
                    <td class="text-center">{{ $i + 1 }}.</td>
                    <td>{{ $student->name }}</td>
                    <td>{{ $student->nim }}</td>
                    <td style="height:40px;"></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                <p>Mengetahui,</p>
                <p>Dosen KKN</p>
                <div class="signature-line"></div>
                @foreach($dpls as $dpl)
                    <p class="font-bold">{{ $dpl->name }}</p>
                    <p>NIP. {{ $dpl->nip }}</p>
                @endforeach
            </td>
            <td>
                <p>Semarang, ________________ {{ $period->year + 1 }}</p>
                <p>Kepala Desa</p>
                <div class="signature-line"></div>
                <p class="font-bold">{{ $group->village_head ?? '______________________' }}</p>
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- DAFTAR ISI                                --}}
    {{-- ══════════════════════════════════════════ --}}
    <h1 class="section-title text-center">DAFTAR ISI</h1>
    <div style="line-height:2.2; font-size:12pt;">
        <p>HALAMAN PENGESAHAN</p>
        <p>DAFTAR ISI</p>
        <p>1. LATAR BELAKANG</p>
        <p>2. USULAN RENCANA PROGRAM</p>
        <p style="padding-left:2rem;">2.1 Multidisiplin</p>
        <p style="padding-left:2rem;">2.2 Sosial Kemasyarakatan</p>
        <p style="padding-left:2rem;">2.3 Program Lainnya</p>
        <p>3. RENCANA PELAKSANAAN</p>
        <p>4. JENIS-JENIS LUARAN</p>
        <p>LAMPIRAN 1 DOKUMENTASI SURVEY</p>
        <p>LAMPIRAN 2 PETA LOKASI</p>
    </div>

    <div class="page-break"></div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- BAB 1 — LATAR BELAKANG                    --}}
    {{-- ══════════════════════════════════════════ --}}
    <h1 class="section-title">1. LATAR BELAKANG</h1>
    <p style="text-align:justify;">
        {{ $group->background ?? 'Belum diisi.' }}
    </p>

    <div class="page-break"></div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- BAB 2 — USULAN RENCANA PROGRAM            --}}
    {{-- ══════════════════════════════════════════ --}}
    <h1 class="section-title">2. USULAN RENCANA PROGRAM</h1>

    {{-- 2.1 Multidisiplin --}}
    <h2 class="subsection-title">2.1 Multidisiplin</h2>
    <p>{{ $group->program_multidisiplin_text ?? 'Berikut adalah usulan program multidisiplin yang disesuaikan dengan permasalahan dan potensi lokasi KKN.' }}</p>

    @php
        // Group by program sequence (1=MD1, 2=MD2, 3=MD3/Video)
        $mdGroups = $multidisiplin->groupBy(fn($p) => $p->program->sequence ?? 1);
        $tableNum = 1;
    @endphp

    @foreach($mdGroups->sortKeys() as $number => $programs)
        <div style="margin-top:1rem;">
            <p class="font-bold">Tabel {{ $tableNum++ }}. Rencana Multidisiplin {{ $number }}</p>

            <table class="lrk-table">
                <thead>
                    <tr>
                        <th colspan="{{ $number == 3 ? 4 : 6 }}" style="text-align:left; font-weight:bold;">
                            TEMA MULTIDISIPLIN {{ $number }} : "{{ $programs->first()->program->title ?? '-' }}"
                        </th>
                    </tr>
                    @if($number == 3)
                        <tr>
                            <th style="width:30px;">No</th>
                            <th>Nama / NIM / Fakultas / Prodi</th>
                            <th>Peran</th>
                            <th>Deskripsi dan Tanggungjawab</th>
                        </tr>
                    @else
                        <tr>
                            <th style="width:30px;">No</th>
                            <th>Nama / NIM / Fakultas / Prodi</th>
                            <th>Potensi /Permasalahan &amp; Lokasi / Narsum</th>
                            <th>Usulan Program</th>
                            <th>Metode &amp; Kelompok Sasaran</th>
                            <th>Luaran</th>
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @foreach($programs->values() as $j => $program)
                        <tr>
                            <td class="text-center">{{ $j + 1 }}.</td>
                            <td>{{ $program->student->name ?? '-' }} / {{ $program->student->nim ?? '-' }} / {{ $program->student->fakultas ?? '-' }} / {{ $program->student->prodi ?? '-' }}</td>
                            @if($number == 3)
                                <td>{{ $program->role_in_program ?? '' }}</td>
                                <td>{{ $program->responsibility ?? '' }}</td>
                            @else
                                <td>{{ $program->problem_potential ?? '' }}</td>
                                <td>{{ $program->participant_title ?: ($program->program->title ?? '') }}</td>
                                <td>{{ $program->method ?? '' }}@if($program->target_audience)<br>{{ $program->target_audience }}@endif</td>
                                <td>{{ $program->output_target ?? '' }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($number == 3)
                <div style="margin-top:0.8rem;">
                    <p class="font-bold">Rencana Story Board :</p>
                    <div style="padding-left:1rem;">
                        {!! nl2br(e($group->storyboard_text ?? '-')) !!}
                    </div>
                    <p class="font-bold" style="margin-top:0.8rem;">Rencana Skrip Video :</p>
                    <div style="padding-left:1rem;">
                        {!! nl2br(e($group->video_script_text ?? '-')) !!}
                    </div>
                </div>
            @endif
        </div>
    @endforeach

    @if($multidisiplin->isEmpty())
        <p><em>Belum ada program multidisiplin yang disetujui.</em></p>
    @endif

    {{-- 2.2 Sosial Kemasyarakatan --}}
    <h2 class="subsection-title" style="margin-top:2rem;">2.2 Sosial Kemasyarakatan</h2>
    <p>{{ $group->program_sosmas_text ?? 'Tulis usulan program sosial kemasyarakatan yang disesuaikan dengan permasalahan dan potensi yang telah dijabarkan sebelumnya.' }}</p>

    @php
        $isSaintek = function($nim) {
            $nim = (string) $nim;
            if (str_starts_with($nim, '4003') || str_starts_with($nim, '4004')) return true;
            if (str_starts_with($nim, '4001')) return false;
            if (str_starts_with($nim, '2')) return true;
            return false;
        };
        
        $saintek = $sosialKemasyarakatan->filter(fn($p) => $isSaintek($p->student->nim ?? ''));
        $soshum = $sosialKemasyarakatan->reject(fn($p) => $isSaintek($p->student->nim ?? ''));
    @endphp

    @if($saintek->isNotEmpty())
        <div style="margin-top:1rem;">
            <p class="font-bold">Tabel {{ $tableNum++ }}. Rencana Sosial Kemasyarakatan Saintek</p>
            <table class="lrk-table">
                <thead>
                    <tr>
                        <th colspan="4" style="text-align:left; font-weight:bold;">
                            Program Sosial Kemasyarakatan Kelompok (Saintek)
                        </th>
                    </tr>
                    <tr>
                        <th style="width:30px;">No</th>
                        <th>Nama / NIM / Fakultas / Prodi</th>
                        <th>Peran</th>
                        <th>Deskripsi dan Tanggungjawab</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($saintek->values() as $j => $program)
                        <tr>
                            <td class="text-center">{{ $j + 1 }}.</td>
                            <td>{{ $program->student->name ?? '-' }} / {{ $program->student->nim ?? '-' }} / {{ $program->student->fakultas ?? '-' }} / {{ $program->student->prodi ?? '-' }}</td>
                            <td>{{ $program->role_in_program ?? '' }}</td>
                            <td>{{ $program->responsibility ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($soshum->isNotEmpty())
        <div style="margin-top:1rem;">
            <p class="font-bold">Tabel {{ $tableNum++ }}. Rencana Sosial Kemasyarakatan Soshum</p>
            <table class="lrk-table">
                <thead>
                    <tr>
                        <th colspan="4" style="text-align:left; font-weight:bold;">
                            Program Sosial Kemasyarakatan Kelompok (Soshum)
                        </th>
                    </tr>
                    <tr>
                        <th style="width:30px;">No</th>
                        <th>Nama / NIM / Fakultas / Prodi</th>
                        <th>Peran</th>
                        <th>Deskripsi dan Tanggungjawab</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($soshum->values() as $j => $program)
                        <tr>
                            <td class="text-center">{{ $j + 1 }}.</td>
                            <td>{{ $program->student->name ?? '-' }} / {{ $program->student->nim ?? '-' }} / {{ $program->student->fakultas ?? '-' }} / {{ $program->student->prodi ?? '-' }}</td>
                            <td>{{ $program->role_in_program ?? '' }}</td>
                            <td>{{ $program->responsibility ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($sosialKemasyarakatan->isEmpty())
        <p><em>Belum ada program sosial kemasyarakatan yang disetujui.</em></p>
    @endif

    {{-- 2.3 Program Lainnya --}}
    <h2 class="subsection-title" style="margin-top:2rem;">2.3 Program Lainnya</h2>
    <p>{{ $group->program_lainnya_text ?? 'Tulis usulan program lainnya yang disesuaikan dengan wilayah. Usulan program lainnya tidak ada batasan jumlah maupun jenis.' }}</p>

    @if($lainnya->isNotEmpty())
        <div style="margin-top:1rem;">
            <p class="font-bold">Tabel {{ $tableNum++ }}. Rencana Program Lainnya</p>
            <table class="lrk-table">
                <thead>
                    <tr>
                        <th style="width:30px;">No</th>
                        <th>Nama / NIM / Fakultas / Prodi</th>
                        <th>Peran</th>
                        <th>Deskripsi dan Tanggungjawab</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lainnya->values() as $j => $program)
                        <tr>
                            <td class="text-center">{{ $j + 1 }}.</td>
                            <td>{{ $program->student->name ?? '-' }} / {{ $program->student->nim ?? '-' }} / {{ $program->student->fakultas ?? '-' }} / {{ $program->student->prodi ?? '-' }}</td>
                            <td>{{ $program->role_in_program ?? '' }}</td>
                            <td>{{ $program->responsibility ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p><em>Belum ada program lainnya yang disetujui.</em></p>
    @endif

    <div class="page-break"></div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- BAB 3 — RENCANA PELAKSANAAN               --}}
    {{-- ══════════════════════════════════════════ --}}
    <h1 class="section-title">3. RENCANA PELAKSANAAN</h1>
    <p>Tetapkan rencana pelaksanaan baik yang bersifat umum maupun rencana pelaksanaan setiap program</p>

    @if($scheduleEvents->isNotEmpty())
        <p class="font-bold">Tabel {{ $tableNum++ }}. Rencana Umum Kegiatan</p>
        <table class="lrk-table">
            <thead>
                <tr>
                    <th style="width:30px;" rowspan="2">No</th>
                    <th rowspan="2">Kegiatan</th>
                    <th colspan="6">Minggu Ke-</th>
                </tr>
                <tr>
                    <th style="width:50px;">1</th>
                    <th style="width:50px;">2</th>
                    <th style="width:50px;">3</th>
                    <th style="width:50px;">4</th>
                    <th style="width:50px;">5</th>
                    <th style="width:50px;">6</th>
                </tr>
            </thead>
            <tbody>
                @foreach($scheduleEvents as $i => $event)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}.</td>
                        <td>{{ $event->title }}</td>
                        <td class="text-center">{!! $event->week_number == 1 ? '&#10003;' : '' !!}</td>
                        <td class="text-center">{!! $event->week_number == 2 ? '&#10003;' : '' !!}</td>
                        <td class="text-center">{!! $event->week_number == 3 ? '&#10003;' : '' !!}</td>
                        <td class="text-center">{!! $event->week_number == 4 ? '&#10003;' : '' !!}</td>
                        <td class="text-center">{!! $event->week_number == 5 ? '&#10003;' : '' !!}</td>
                        <td class="text-center">{!! $event->week_number >= 6 ? '&#10003;' : '' !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p><em>Belum ada jadwal kegiatan umum.</em></p>
    @endif

    @if(!empty($calendar))
        <p class="font-bold" style="margin-top:1.5rem;">Tabel {{ $tableNum++ }}. Rencana Usulan Program (Sesuai Kalender Berjalan)</p>
        <table class="lrk-table" style="font-size:8pt; table-layout:fixed;">
            <thead>
                <tr>
                    <th style="width:14.28%;">Senin</th>
                    <th style="width:14.28%;">Selasa</th>
                    <th style="width:14.28%;">Rabu</th>
                    <th style="width:14.28%;">Kamis</th>
                    <th style="width:14.28%;">Jum'at</th>
                    <th style="width:14.28%;">Sabtu</th>
                    <th style="width:14.28%;">Minggu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($calendar as $weekNum => $days)
                    <tr>
                        @foreach($days as $dayData)
                            <td style="vertical-align:top; height:70px; {{ !$dayData['in_period'] ? 'background-color:#f3f4f6;' : '' }}">
                                <div class="font-bold" style="margin-bottom:2px;">{{ $dayData['day'] }}</div>
                                @if($dayData['events']->isNotEmpty())
                                    @foreach($dayData['events'] as $event)
                                        <div>* {{ $event->title }}</div>
                                    @endforeach
                                @endif
                                @if(!empty($dayData['programs']))
                                    @foreach($dayData['programs'] as $prog)
                                        @php
                                            $code = $prog->participant_code ?? '';
                                        @endphp
                                        <div class="font-bold">* {{ $code }}</div>
                                    @endforeach
                                @endif
                            </td>
                        @endforeach
                        @for($i = count($days); $i < 7; $i++)
                            <td style="background-color:#f3f4f6;"></td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:0.8rem; font-size:9pt;">
            <p class="font-bold">Keterangan :</p>
            <ul style="margin:0; padding-left:1.5rem; column-count: 2; column-gap: 2rem;">
                @foreach($multidisiplin as $p)
                    <li><strong>{{ $p->participant_code }}</strong> : Multidisiplin {{ $p->program->sequence }} {{ $p->student->name }}</li>
                @endforeach
                @foreach($sosialKemasyarakatan as $p)
                    <li><strong>{{ $p->participant_code }}</strong> : Sosial Kemasyarakatan {{ $p->program->sequence }} {{ $p->student->name }}</li>
                @endforeach
                @foreach($lainnya as $p)
                    <li><strong>{{ $p->participant_code }}</strong> : Lainnya {{ $p->program->sequence }} {{ $p->student->name }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="page-break"></div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- BAB 4 — JENIS-JENIS LUARAN                --}}
    {{-- ══════════════════════════════════════════ --}}
    <h1 class="section-title">4. JENIS-JENIS LUARAN</h1>

    <p class="font-bold">Tabel {{ $tableNum++ }}. Jenis-jenis Luaran</p>
    <table class="lrk-table">
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th>Jenis Luaran</th>
                <th style="width:180px;">Sifat</th>
            </tr>
        </thead>
        <tbody>
            <tr><td class="text-center">1.</td><td>Update status kegiatan di Media Sosial</td><td>Wajib</td></tr>
            <tr><td class="text-center">2.</td><td>Reportase Media Berita</td><td>Wajib</td></tr>
            <tr><td class="text-center">3.</td><td>Poster / Leaflet / Lembar Balik / Prototype Produk / Teknologi / Modul / Kebijakan</td><td>Wajib salah satu</td></tr>
            <tr><td class="text-center">4.</td><td>Video : rasio 16:9, resolusi min 1080p</td><td>Wajib (Multidisiplin 3)</td></tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- LAMPIRAN 1 — DOKUMENTASI SURVEY           --}}
    {{-- ══════════════════════════════════════════ --}}
    <h1 class="section-title">LAMPIRAN 1 DOKUMENTASI SURVEY</h1>
    <p>{{ $group->survey_documentation_text ?? 'Masukkan dokumentasi survey pada lampiran ini, pilih foto yang memperlihatkan permasalahan atau potensi sesuai yang dipilih menjadi usulan program, sertakan juga caption foto yang sesuai.' }}</p>

    @if($group->survey_document_path)
        <div class="text-center">
            <img src="{{ public_path('storage/' . $group->survey_document_path) }}" alt="Dokumentasi Survey" style="max-width:100%; border:1px solid #ccc;">
            <p style="margin-top:8px;"><em>Dokumentasi Survey {{ $group->village }}, {{ $group->district }}, {{ $group->regency }}</em></p>
        </div>
    @else
        <p><em>Belum ada dokumentasi survey.</em></p>
    @endif

    <div class="page-break"></div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- LAMPIRAN 2 — PETA LOKASI                  --}}
    {{-- ══════════════════════════════════════════ --}}
    <h1 class="section-title">LAMPIRAN 2 PETA LOKASI</h1>
    <p>{{ $group->location_map_text ?? 'Gambarkan peta lokasi secara manual dengan keterangan/informasi terkait dengan permasalahan atau potensi yang menjadi usulan program KKN tersebut dilaksanakan baik multidisiplin maupun keilmuan.' }}</p>

    @if($group->location_map_path)
        <div class="text-center">
            <img src="{{ public_path('storage/' . $group->location_map_path) }}" alt="Peta Lokasi" style="max-width:100%; border:1px solid #ccc;">
            <p style="margin-top:8px;"><em>Peta Lokasi KKN {{ $group->village }}, {{ $group->district }}, {{ $group->regency }}</em></p>
        </div>
    @else
        <p><em>Belum ada peta lokasi.</em></p>
    @endif

</body>
</html>
