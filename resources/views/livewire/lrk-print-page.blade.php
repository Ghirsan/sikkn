<div>
    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- HALAMAN JUDUL (Cover Page)                                 --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="print-page">
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
                <table style="margin:1rem auto; border-collapse:collapse;">
                    <tr>
                        <td style="padding:4px 24px; vertical-align:top;">
                            @foreach($students->take((int) ceil($students->count() / 2)) as $s)
                                <div>{{ $s->name }} / {{ $s->nim }}</div>
                            @endforeach
                        </td>
                        <td style="padding:4px 24px; vertical-align:top;">
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
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- HALAMAN PENGESAHAN                                         --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="print-page page-break">
        <h1 class="section-title" style="text-align:center;">HALAMAN PENGESAHAN</h1>

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
                        <td style="text-align:center;">{{ $i + 1 }}.</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->nim }}</td>
                        <td style="height:40px;"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="signature-block" style="margin-top:3rem;">
            <div class="signature-box">
                <p>Mengetahui,</p>
                <p>Dosen KKN</p>
                <div class="signature-line"></div>
                @foreach($dpls as $dpl)
                    <p style="font-weight:bold;">{{ $dpl->name }}</p>
                    <p>NIP. {{ $dpl->nip }}</p>
                @endforeach
            </div>
            <div class="signature-box">
                <p>Semarang, ________________ {{ $period->year + 1 }}</p>
                <p>Kepala Desa</p>
                <div class="signature-line"></div>
                <p style="font-weight:bold;">{{ $group->village_head ?? '______________________' }}</p>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- DAFTAR ISI                                                 --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="print-page page-break">
        <h1 class="section-title" style="text-align:center;">DAFTAR ISI</h1>
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
    <div class="print-page page-break">
        <h1 class="section-title">1. LATAR BELAKANG</h1>
        <p style="text-align:justify;">
            {{ $group->background ?? 'Belum diisi.' }}
        </p>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- BAB 2 — USULAN RENCANA PROGRAM                             --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="print-page page-break">
        <h1 class="section-title">2. USULAN RENCANA PROGRAM</h1>

        {{-- 2.1 Multidisiplin --}}
        <h2 class="subsection-title">2.1 Multidisiplin</h2>
        <p>Berikut adalah usulan program multidisiplin yang disesuaikan dengan permasalahan dan potensi lokasi KKN.</p>

        @php
            $mdGroups = $multidisiplin->groupBy('multidisciplinary_number');
        @endphp

        @foreach($mdGroups as $number => $programs)
            <div class="avoid-break" style="margin-top:1.5rem;">
                <p style="font-weight:bold;">
                    Tabel {{ $loop->iteration }}. Rencana Multidisiplin {{ $number ?? $loop->iteration }}
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
                        <tr>
                            <th style="width:35px;">No</th>
                            <th>Nama / NIM / Fakultas / Prodi</th>
                            <th>Potensi / Permasalahan</th>
                            <th>Usulan Program</th>
                            <th>Metode & Kelompok Sasaran</th>
                            <th>Luaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($programs as $j => $program)
                            <tr>
                                <td style="text-align:center;">{{ $j + 1 }}.</td>
                                <td>{{ $program->student->name }} / {{ $program->student->nim }} / {{ $program->student->fakultas }} / {{ $program->student->prodi }}</td>
                                <td>{{ $program->problem_potential ?? '-' }}</td>
                                <td>{{ $program->title }}</td>
                                <td>{{ $program->method ?? '-' }}<br>{{ $program->target_audience ?? '' }}</td>
                                <td>{{ $program->output_target ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach

        @if($multidisiplin->isEmpty())
            <p><em>Belum ada program multidisiplin yang disetujui.</em></p>
        @endif

        {{-- 2.2 Sosial Kemasyarakatan --}}
        <h2 class="subsection-title" style="margin-top:2rem;">2.2 Sosial Kemasyarakatan</h2>
        <p>Berikut adalah usulan program sosial kemasyarakatan yang disesuaikan dengan permasalahan dan potensi lokasi KKN.</p>

        @if($sosialKemasyarakatan->isNotEmpty())
            <div class="avoid-break" style="margin-top:1rem;">
                <p style="font-weight:bold;">
                    Tabel {{ $mdGroups->count() + 1 }}. Rencana Sosial Kemasyarakatan
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
                        @foreach($sosialKemasyarakatan as $j => $program)
                            <tr>
                                <td style="text-align:center;">{{ $j + 1 }}.</td>
                                <td>{{ $program->student->name }} / {{ $program->student->nim }} / {{ $program->student->fakultas }} / {{ $program->student->prodi }}</td>
                                <td>{{ $program->role_in_program ?? '-' }}</td>
                                <td>{{ $program->responsibility ?? $program->title }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p><em>Belum ada program sosial kemasyarakatan yang disetujui.</em></p>
        @endif

        {{-- 2.3 Program Lainnya --}}
        <h2 class="subsection-title" style="margin-top:2rem;">2.3 Program Lainnya</h2>

        @if($lainnya->isNotEmpty())
            <div class="avoid-break" style="margin-top:1rem;">
                <p style="font-weight:bold;">
                    Tabel {{ $mdGroups->count() + ($sosialKemasyarakatan->isNotEmpty() ? 2 : 1) }}. Rencana Program Lainnya
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
                                <td style="text-align:center;">{{ $j + 1 }}.</td>
                                <td>{{ $program->student->name }} / {{ $program->student->nim }} / {{ $program->student->fakultas }} / {{ $program->student->prodi }}</td>
                                <td>{{ $program->role_in_program ?? '-' }}</td>
                                <td>{{ $program->responsibility ?? $program->title }}</td>
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
    <div class="print-page page-break">
        <h1 class="section-title">3. RENCANA PELAKSANAAN</h1>

        @if($scheduleEvents->isNotEmpty())
            @php
                $eventsByWeek = $scheduleEvents->groupBy('week_number');
            @endphp

            <p style="font-weight:bold;">Tabel Rencana Umum Kegiatan</p>
            <table class="lrk-table">
                <thead>
                    <tr>
                        <th style="width:35px;">No</th>
                        <th>Kegiatan</th>
                        <th>Tanggal</th>
                        <th>Minggu Ke-</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($scheduleEvents as $i => $event)
                        <tr>
                            <td style="text-align:center;">{{ $i + 1 }}.</td>
                            <td>{{ $event->title }}</td>
                            <td>{{ $event->date->format('d M Y') }}</td>
                            <td style="text-align:center;">{{ $event->week_number ?? '-' }}</td>
                            <td>{{ $event->description ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p><em>Belum ada jadwal kegiatan.</em></p>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- BAB 4 — JENIS-JENIS LUARAN                                 --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="print-page page-break">
        <h1 class="section-title">4. JENIS-JENIS LUARAN</h1>

        <table class="lrk-table">
            <thead>
                <tr>
                    <th style="width:35px;">No</th>
                    <th>Jenis Luaran</th>
                    <th style="width:120px;">Sifat</th>
                </tr>
            </thead>
            <tbody>
                <tr><td style="text-align:center;">1.</td><td>Update status kegiatan di Media Sosial</td><td>Wajib</td></tr>
                <tr><td style="text-align:center;">2.</td><td>Reportase Media Berita</td><td>Wajib</td></tr>
                <tr><td style="text-align:center;">3.</td><td>Poster / Leaflet / Lembar Balik / Prototype Produk / Teknologi / Modul / Kebijakan</td><td>Wajib salah satu</td></tr>
                <tr><td style="text-align:center;">4.</td><td>Video : rasio 16:9, resolusi min 1080p</td><td>Wajib (Multidisiplin 3)</td></tr>
            </tbody>
        </table>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- LAMPIRAN 1 — DOKUMENTASI SURVEY                            --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="print-page page-break">
        <h1 class="section-title">LAMPIRAN 1 — DOKUMENTASI SURVEY</h1>

        @if($surveyDocuments->isNotEmpty())
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem;">
                @foreach($surveyDocuments as $doc)
                    <div class="avoid-break" style="text-align:center;">
                        <img src="{{ asset('storage/' . $doc->image_path) }}" alt="{{ $doc->caption }}" style="max-width:100%; max-height:200px; border:1px solid #ccc;">
                        <p style="font-size:10pt; margin-top:4px;"><em>{{ $doc->caption ?? 'Tanpa keterangan' }}</em></p>
                    </div>
                @endforeach
            </div>
        @else
            <p><em>Belum ada dokumentasi survey.</em></p>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- LAMPIRAN 2 — PETA LOKASI                                   --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div class="print-page page-break">
        <h1 class="section-title">LAMPIRAN 2 — PETA LOKASI</h1>

        @if($group->location_map_path)
            <div style="text-align:center;">
                <img src="{{ asset('storage/' . $group->location_map_path) }}" alt="Peta Lokasi" style="max-width:100%; border:1px solid #ccc;">
                <p style="margin-top:8px;"><em>Peta Lokasi KKN — {{ $group->village }}, {{ $group->district }}, {{ $group->regency }}</em></p>
            </div>
        @else
            <p><em>Belum ada peta lokasi.</em></p>
        @endif
    </div>
</div>
