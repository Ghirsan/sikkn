<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Rencana Kegiatan - {{ $group->name }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 0;
        }

        @page { size: a4 portrait; }
        @page landscape_page { size: a4 landscape; }
        .landscape { page: landscape_page; }

        /* ── Print Styles ──────────────────────────── */
        .page-break {
            page-break-before: always;
        }
        . {
            page-break-inside: avoid;
        }

        h1, h2, h3 { 
            font-family: 'Times New Roman', Times, serif; 
        }

        .section-title {
            font-size: 14pt;
            font-weight: bold;
            margin: 2rem 0 1rem;
            text-transform: uppercase;
        }
        
        .subsection-title {
            font-size: 12pt;
            font-weight: bold;
            margin: 1.5rem 0 0.5rem;
        }

        /* ── Tables ──────────────────────────────────────────── */
        .lrk-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
            font-size: 11pt;
        }
        .lrk-table th, .lrk-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
            text-align: left;
        }
        .lrk-table th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        /* ── Cover page ──────────────────────────────────────── */
        .cover-page {
            text-align: center;
            margin-top: 100px;
        }
        .cover-page h1 { font-size: 16pt; font-weight: bold; margin: 0.5rem 0; }
        .cover-page h2 { font-size: 14pt; font-weight: bold; margin: 0.3rem 0; }
        .institution { font-size: 13pt; font-weight: bold; margin-top: 100px; }

        /* ── Signatures ──────────────────────────────────────── */
        .signature-table {
            width: 100%;
            margin-top: 3rem;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .signature-line { 
            margin: 80px auto 10px; 
            border-bottom: 1px solid #000; 
            width: 200px; 
        }
        
        /* Layout helpers for DomPDF */
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .mt-2 { margin-top: 2rem; }
    </style>
</head>
<body>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- HALAMAN JUDUL (Cover Page)                                 --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="cover-page">
        <h1>LAPORAN RENCANA KEGIATAN</h1>
        <h2>KULIAH KERJA NYATA {{ $period->semester->value === 'Ganjil' ? 'TIM I' : 'TIM II' }} TA {{ $period->year }}/{{ $period->year + 1 }}</h2>

        @if($group->partner_name)
            <p style="font-size:13pt; font-weight:bold; margin-top:1rem;">{{ strtoupper($group->partner_name) }}</p>
        @endif

        <div style="margin-top:2rem; font-size:12pt; line-height:2;">
            <p>{{ strtoupper($group->village) }} {{ strtoupper($group->district) }}</p>
            <p>{{ strtoupper($group->regency) }}</p>
        </div>

        <div style="margin-top:2rem;">
            <p style="font-weight:bold;">Oleh :</p>
            <table style="margin:1rem auto; border-collapse:collapse; text-align: left;">
                <tr>
                    <td style="padding:4px 24px; vertical-align:top; border: none;">
                        @foreach($students->take((int) ceil($students->count() / 2)) as $s)
                            <div>{{ $s->name }} / {{ $s->nim }}</div>
                        @endforeach
                    </td>
                    <td style="padding:4px 24px; vertical-align:top; border: none;">
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

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- HALAMAN PENGESAHAN                                         --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="page-break">
        <h1 class="section-title text-center">HALAMAN PENGESAHAN</h1>

        <p>
            Tim KKN {{ $period->semester->value === 'Ganjil' ? 'Tim I' : 'Tim II' }} TA {{ $period->year }}/{{ $period->year + 1 }}
            @if($group->partner_name) pada {{ $group->partner_name }} @endif
            di {{ $group->village }} {{ $group->district }} {{ $group->regency }}.
        </p>

        <table class="lrk-table">
            <thead>
                <tr>
                    <th style="width:40px;">No</th>
                    <th>Nama</th>
                    <th>NIM</th>
                    <th style="width:120px;">Tanda Tangan</th>
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

        <table class="signature-table ">
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
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- DAFTAR ISI                                                 --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="page-break">
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
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- BAB 1 — LATAR BELAKANG                                     --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="landscape">
    <div class="page-break">
        <h1 class="section-title">1. LATAR BELAKANG</h1>
        <p style="text-align:justify;">
            {{ $group->background ?? 'Belum diisi.' }}
        </p>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- BAB 2 — USULAN RENCANA PROGRAM                             --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="page-break">
        <h1 class="section-title">2. USULAN RENCANA PROGRAM</h1>

        {{-- 2.1 Multidisiplin --}}
        <h2 class="subsection-title">2.1 Multidisiplin</h2>
        <p>Berikut adalah usulan program multidisiplin yang disesuaikan dengan permasalahan dan potensi lokasi KKN.</p>

        @php
            $mdGroups = $multidisiplin->groupBy('multidisciplinary_number');
            $tableNum = 1;
        @endphp

        @foreach($mdGroups as $number => $programs)
            <div class="" style="margin-top:1.5rem;">
                <p class="font-bold">
                    Tabel {{ $tableNum++ }}. Rencana Multidisiplin {{ $number ?? $loop->iteration }}
                </p>

                @php $theme = $programs->first()?->theme; @endphp
                <table class="lrk-table">
                    <thead>
                        @if($theme)
                            <tr>
                                <th colspan="6" style="text-align:left; font-weight:bold;">
                                    TEMA MULTIDISIPLIN {{ $number }} : "{{ $theme }}"
                                </th>
                            </tr>
                        @endif
                        @if($number == 3)
                            <tr>
                                <th style="width:35px;">No</th>
                                <th>Nama / NIM / Fakultas / Prodi</th>
                                <th>Peran</th>
                                <th>Deskripsi dan Tanggungjawab</th>
                            </tr>
                        @else
                            <tr>
                                <th style="width:35px;">No</th>
                                <th>Nama / NIM / Fakultas / Prodi</th>
                                <th>Potensi / Permasalahan</th>
                                <th>Usulan Program</th>
                                <th>Metode & Kelompok Sasaran</th>
                                <th>Luaran</th>
                            </tr>
                        @endif
                    </thead>
                    <tbody>
                        @foreach($programs as $j => $program)
                            <tr>
                                <td class="text-center">{{ $j + 1 }}.</td>
                                <td>{{ $program->student->name }} / {{ $program->student->nim }} / {{ $program->student->fakultas }} / {{ $program->student->prodi }}</td>
                                @if($number == 3)
                                    <td>{{ $program->role_in_program ?? '-' }}</td>
                                <td>{{ $program->responsibility ?? ($program->participant_title ?: $program->program->title) }}</td>
                                @else
                                    <td>{{ $program->problem_potential ?? '-' }}</td>
                                    <td>{{ $program->participant_title ?: $program->program->title }}</td>
                                    <td>{{ $program->method ?? '-' }}<br>{{ $program->target_audience ?? '' }}</td>
                                    <td>{{ $program->output_target ?? '-' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($number == 3)
                    <div style="margin-top:1rem; padding-left:0.5rem;">
                        <p class="font-bold">Rencana Story Board :</p>
                        <ul style="margin:0; padding-left:1.5rem;">
                            <li>{!! nl2br(e($programs->first()?->storyboard ?? '-')) !!}</li>
                        </ul>
                        
                        <p class="font-bold" style="margin-top:1rem;">Rencana Skrip Video :</p>
                        <ul style="margin:0; padding-left:1.5rem;">
                            <li>{!! nl2br(e($programs->first()?->video_script ?? '-')) !!}</li>
                        </ul>
                    </div>
                @endif
            </div>
        @endforeach

        @if($multidisiplin->isEmpty())
            <p><em>Belum ada program multidisiplin yang disetujui.</em></p>
        @endif

        {{-- 2.2 Sosial Kemasyarakatan --}}
        <h2 class="subsection-title" style="margin-top:2rem;">2.2 Sosial Kemasyarakatan</h2>
        <p>Berikut adalah usulan program sosial kemasyarakatan yang disesuaikan dengan permasalahan dan potensi lokasi KKN.</p>

        @php
            $saintekFaculties = ['FSM', 'FT', 'FK', 'FKM', 'FPP', 'FPIK'];
            $saintek = $sosialKemasyarakatan->filter(fn($p) => in_array($p->student->fakultas, $saintekFaculties));
            $soshum = $sosialKemasyarakatan->reject(fn($p) => in_array($p->student->fakultas, $saintekFaculties));
        @endphp

        @if($saintek->isNotEmpty())
            <div class="" style="margin-top:1rem;">
                <p class="font-bold">
                    Tabel {{ $tableNum++ }}. Rencana Sosial Kemasyarakatan Saintek
                </p>
                <table class="lrk-table">
                    <thead>
                        <tr>
                            <th style="width:35px;">No</th>
                            <th>Nama / NIM / Fakultas / Prodi</th>
                            <th>Peran</th>
                            <th>Deskripsi dan Tanggungjawab</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($saintek as $j => $program)
                            <tr>
                                <td class="text-center">{{ $j + 1 }}.</td>
                                <td>{{ $program->student->name }} / {{ $program->student->nim }} / {{ $program->student->fakultas }} / {{ $program->student->prodi }}</td>
                                <td>{{ $program->role_in_program ?? '-' }}</td>
                                <td>{{ $program->responsibility ?? $program->program->title }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($soshum->isNotEmpty())
            <div class="" style="margin-top:1rem;">
                <p class="font-bold">
                    Tabel {{ $tableNum++ }}. Rencana Sosial Kemasyarakatan Soshum
                </p>
                <table class="lrk-table">
                    <thead>
                        <tr>
                            <th style="width:35px;">No</th>
                            <th>Nama / NIM / Fakultas / Prodi</th>
                            <th>Peran</th>
                            <th>Deskripsi dan Tanggungjawab</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($soshum as $j => $program)
                            <tr>
                                <td class="text-center">{{ $j + 1 }}.</td>
                                <td>{{ $program->student->name }} / {{ $program->student->nim }} / {{ $program->student->fakultas }} / {{ $program->student->prodi }}</td>
                                <td>{{ $program->role_in_program ?? '-' }}</td>
                                <td>{{ $program->responsibility ?? $program->program->title }}</td>
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

        @if($lainnya->isNotEmpty())
            <div class="" style="margin-top:1rem;">
                <p class="font-bold">
                    Tabel {{ $tableNum++ }}. Rencana Program Lainnya
                </p>
                <table class="lrk-table">
                    <thead>
                        <tr>
                            <th style="width:35px;">No</th>
                            <th>Nama / NIM / Fakultas / Prodi</th>
                            <th>Peran</th>
                            <th>Deskripsi dan Tanggung Jawab</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lainnya as $j => $program)
                            <tr>
                                <td class="text-center">{{ $j + 1 }}.</td>
                                <td>{{ $program->student->name }} / {{ $program->student->nim }} / {{ $program->student->fakultas }} / {{ $program->student->prodi }}</td>
                                <td>{{ $program->role_in_program ?? '-' }}</td>
                                <td>{{ $program->responsibility ?? $program->program->title }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p><em>Belum ada program lainnya yang disetujui.</em></p>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- BAB 3 — RENCANA PELAKSANAAN                                --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="page-break">
        <h1 class="section-title">3. RENCANA PELAKSANAAN</h1>

        @if($scheduleEvents->isNotEmpty())
            <p class="font-bold">Tabel {{ $tableNum++ }}. Rencana Umum Kegiatan</p>
            <table class="lrk-table ">
                <thead>
                    <tr>
                        <th style="width:35px;" rowspan="2">No</th>
                        <th rowspan="2">Kegiatan</th>
                        <th colspan="6">Minggu Ke-</th>
                    </tr>
                    <tr>
                        <th style="width:40px;">1</th>
                        <th style="width:40px;">2</th>
                        <th style="width:40px;">3</th>
                        <th style="width:40px;">4</th>
                        <th style="width:40px;">5</th>
                        <th style="width:40px;">6</th>
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
            <p class="font-bold" style="margin-top:2rem;">Tabel {{ $tableNum++ }}. Rencana Usulan Program (Sesuai Kalender Berjalan)</p>
            <table class="lrk-table " style="font-size:9pt; table-layout: fixed;">
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
                                <td style="vertical-align:top; height:80px; {{ !$dayData['in_period'] ? 'background-color:#f3f4f6;' : '' }}">
                                    <div class="font-bold" style="margin-bottom:4px;">{{ $dayData['day'] }}</div>
                                    @if($dayData['events']->isNotEmpty())
                                        <ul style="margin:0; padding-left:1.2rem; list-style-type:disc;">
                                            @foreach($dayData['events'] as $event)
                                                <li>{{ $event->title }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    @if(!empty($dayData['programs']))
                                        <ul style="margin:4px 0 0 0; padding-left:1.2rem; list-style-type:circle; font-weight:bold; color:#0d6efd;">
                                            @foreach($dayData['programs'] as $prog)
                                                @php
                                                    $code = strtoupper(substr($prog->type->value ?? 'P', 0, 1));
                                                    if ($prog->type === \App\Enums\ProgramType::Multidisiplin) {
                                                        $code .= $prog->multidisciplinary_number ?? '1';
                                                    }
                                                @endphp
                                                <li>{{ $code }} {{ strtoupper(substr($prog->student->name, 0, 2)) }}</li>
                                            @endforeach
                                        </ul>
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
            
            <div style="margin-top:1rem; font-size:10pt;">
                <p><strong>Keterangan :</strong></p>
                <p>Kode Program ditampilkan berdasarkan inisial jenis kegiatan (M = Multidisiplin, S = Sosial Kemasyarakatan, L = Lainnya) diikuti inisial nama mahasiswa pelaksana.</p>
            </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- BAB 4 — JENIS-JENIS LUARAN                                 --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="page-break">
        <h1 class="section-title">4. JENIS-JENIS LUARAN</h1>

        <p class="font-bold">Tabel {{ $tableNum++ }}. Jenis-jenis Luaran</p>
        <table class="lrk-table ">
            <thead>
                <tr>
                    <th style="width:35px;">No</th>
                    <th>Jenis Luaran</th>
                    <th style="width:120px;">Sifat</th>
                </tr>
            </thead>
            <tbody>
                <tr><td class="text-center">1.</td><td>Update status kegiatan di Media Sosial</td><td>Wajib</td></tr>
                <tr><td class="text-center">2.</td><td>Reportase Media Berita</td><td>Wajib</td></tr>
                <tr><td class="text-center">3.</td><td>Poster / Leaflet / Lembar Balik / Prototype Produk / Teknologi / Modul / Kebijakan</td><td>Wajib salah satu</td></tr>
                <tr><td class="text-center">4.</td><td>Video : rasio 16:9, resolusi min 1080p</td><td>Wajib (Multidisiplin 3)</td></tr>
            </tbody>
        </table>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- LAMPIRAN 1 — DOKUMENTASI SURVEY                            --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="page-break">
        <h1 class="section-title">LAMPIRAN 1 — DOKUMENTASI SURVEY</h1>

        @if($surveyDocuments->isNotEmpty())
            <table style="width: 100%; border-collapse: separate; border-spacing: 15px;">
                <tr>
                @foreach($surveyDocuments as $index => $doc)
                    @if($index > 0 && $index % 2 == 0)
                        </tr><tr>
                    @endif
                    <td class="text-center " style="width: 50%; vertical-align: top;">
                        <img src="{{ public_path('storage/' . $doc->image_path) }}" alt="{{ $doc->caption }}" style="max-width:100%; max-height:250px; border:1px solid #ccc;">
                        <p style="font-size:10pt; margin-top:4px;"><em>{{ $doc->caption ?? 'Tanpa keterangan' }}</em></p>
                    </td>
                @endforeach
                </tr>
            </table>
        @else
            <p><em>Belum ada dokumentasi survey.</em></p>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- LAMPIRAN 2 — PETA LOKASI                                   --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="page-break">
        <h1 class="section-title">LAMPIRAN 2 — PETA LOKASI</h1>

        @if($group->location_map_path)
            <div class="text-center">
                <img src="{{ public_path('storage/' . $group->location_map_path) }}" alt="Peta Lokasi" style="max-width:100%; border:1px solid #ccc;">
                <p style="margin-top:8px;"><em>Peta Lokasi KKN — {{ $group->village }}, {{ $group->district }}, {{ $group->regency }}</em></p>
            </div>
        @else
            <p><em>Belum ada peta lokasi.</em></p>
        @endif
    </div>

    </div>
</body>
</html>
