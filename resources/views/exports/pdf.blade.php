<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { color: #374151; font-size: 12px; }
        .header { text-align: center; padding-bottom: 16px; border-bottom: 3px solid #EC4899; }
        .logo { font-size: 28px; font-weight: 800; color: #EC4899; margin: 0; }
        .subtitle { color: #9CA3AF; font-size: 11px; margin: 2px 0 0; }
        .meta { color: #9CA3AF; font-size: 10px; margin-top: 6px; }
        .summary { display: flex; justify-content: center; gap: 24px; margin: 16px 0; }
        .summary-item { text-align: center; }
        .summary-item strong { display: block; font-size: 18px; color: #EC4899; }
        .summary-item span { font-size: 10px; color: #9CA3AF; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #EC4899; color: #fff; padding: 8px 6px; font-size: 10px; text-align: left; }
        td { padding: 7px 6px; border-bottom: 1px solid #FBCFE8; font-size: 10px; }
        tr:nth-child(even) td { background: #FFF7FB; }
        .footer { text-align: center; color: #F472B6; font-size: 10px; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="header">
        <p class="logo">Mimiw</p>
        <p class="subtitle">Pelacak Siklus Pribadi — Laporan Riwayat</p>
        <p class="meta">Dibuat untuk {{ $user->displayName() }} · {{ $exportedAt }}</p>
    </div>

    @php
        $count = $periods->count();
        $avgDuration = $periods->avg('duration');
        $avgCycle = $periods->whereNotNull('cycle_length')->avg('cycle_length');
    @endphp

    <div class="summary">
        <div class="summary-item">
            <strong>{{ $count }}</strong>
            <span>Total Menstruasi</span>
        </div>
        <div class="summary-item">
            <strong>{{ $avgCycle ? number_format($avgCycle, 1) : '—' }}</strong>
            <span>Rata-rata Siklus</span>
        </div>
        <div class="summary-item">
            <strong>{{ $avgDuration ? number_format($avgDuration, 1) : '—' }}</strong>
            <span>Rata-rata Durasi</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Mulai</th>
                <th>Selesai</th>
                <th>Durasi</th>
                <th>Siklus</th>
                <th>Volume</th>
                <th>Suasana Hati</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($periods as $period)
                <tr>
                    <td>{{ $period->start_date->format('d M Y') }}</td>
                    <td>{{ $period->end_date->format('d M Y') }}</td>
                    <td>{{ $period->duration }} hari</td>
                    <td>{{ $period->cycle_length ? $period->cycle_length.' hari' : '—' }}</td>
                    <td>{{ $period->flowLabel() ?? '—' }}</td>
                    <td>{{ $period->moodEmoji() ? $period->moodEmoji().' '.(\App\Models\Mood::labelFor($period->mood) ?? $period->mood) : '—' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($period->notes, 40) ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; color:#9CA3AF;">Belum ada data menstruasi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">Mimiw — Pelacak Siklus Pribadi</p>
</body>
</html>
