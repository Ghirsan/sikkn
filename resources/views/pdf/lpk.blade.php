<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>LPK KKN - {{ $group->name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        h1, h2, h3 { text-align: center; }
    </style>
</head>
<body>
    <h1>Laporan Pelaksanaan Kegiatan (LPK)</h1>
    <h2>Kuliah Kerja Nyata</h2>
    <h3>Tim: {{ $group->name }}</h3>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Program</th>
                <th>Mahasiswa</th>
                <th>Status LPK</th>
                <th>Pencapaian</th>
                <th>Kendala</th>
            </tr>
        </thead>
        <tbody>
            @foreach($programs as $index => $program)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ ($program->program->type === \App\Enums\ProgramType::Multidisiplin && $program->program->sequence == 3) ? $program->program->title : ($program->participant_title ?: $program->program->title) }}</td>
                <td>{{ $program->student->name }}</td>
                <td>{{ $program->lpk_status->label() }}</td>
                <td>{{ $program->achievement ?? '-' }}</td>
                <td>{{ $program->obstacle ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
