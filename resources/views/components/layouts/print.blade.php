<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Laporan Rencana Kegiatan' }}</title>
    @vite(['resources/css/app.css'])
    <style>
        /* ── Print-optimized base styles ─────────────────────── */
        @media screen {
            body { background: #e5e7eb; }
            .print-page { max-width: 210mm; margin: 2rem auto; background: white; box-shadow: 0 4px 24px rgba(0,0,0,.12); }
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
        }

        .print-page {
            padding: 25mm 30mm;
            box-sizing: border-box;
        }

        h1, h2, h3 { font-family: 'Times New Roman', Times, serif; }

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
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            min-height: calc(297mm - 50mm);
        }
        .cover-page h1 { font-size: 16pt; font-weight: bold; margin: 0.5rem 0; }
        .cover-page h2 { font-size: 14pt; font-weight: bold; margin: 0.3rem 0; }
        .cover-page .institution { font-size: 13pt; font-weight: bold; margin-top: 3rem; }

        /* ── Signatures ──────────────────────────────────────── */
        .signature-block {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-top: 3rem;
        }
        .signature-box { text-align: center; }
        .signature-line { margin-top: 80px; border-bottom: 1px solid #000; display: inline-block; min-width: 200px; }

        /* ── Section titles ───────────────────────────────────── */
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

        /* ── Print styles ────────────────────────────────────── */
        @media print {
            body { background: white; margin: 0; }
            .print-page { padding: 20mm 25mm; box-shadow: none; margin: 0; max-width: none; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            .avoid-break { page-break-inside: avoid; }
        }

        /* ── Print button (screen only) ──────────────────────── */
        .print-btn-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #1e293b;
            color: white;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 9999;
            box-shadow: 0 2px 8px rgba(0,0,0,.3);
        }
        .print-btn-bar button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 8px 24px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        .print-btn-bar button:hover { background: #2563eb; }

        @media print { .print-btn-bar { display: none !important; } }
        @media screen { .print-page:first-of-type { margin-top: 5rem; } }
    </style>
</head>
<body>
    <!-- Print Controls (screen only) -->
    <div class="print-btn-bar no-print">
        <span style="font-weight:600;">Laporan Rencana Kegiatan — Preview</span>
        <button onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
    </div>

    {{ $slot }}

    @livewireScripts
</body>
</html>
