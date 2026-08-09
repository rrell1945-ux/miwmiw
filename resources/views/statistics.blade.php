<x-app-layout>
    @section('title', 'Statistik')

    @push('scripts')
        @vite(['resources/js/statistics.js'])
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const stats = @json($stats);

                if (stats.cycle_series.length) {
                    BloomCharts.render('chart-cycle', {
                        chart: { type: 'area', height: 280 },
                        series: [{ name: 'Panjang siklus (hari)', data: stats.cycle_series }],
                        stroke: { curve: 'smooth', width: 3 },
                        colors: ['#EC4899'],
                        fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.02 } },
                        xaxis: { type: 'category' },
                        tooltip: { y: { formatter: (v) => v + ' hari' } },
                    });
                }

                if (stats.duration_series.length) {
                    BloomCharts.render('chart-duration', {
                        chart: { type: 'bar', height: 260 },
                        series: [{ name: 'Durasi (hari)', data: stats.duration_series }],
                        plotOptions: { bar: { borderRadius: 8, columnWidth: '55%' } },
                        colors: ['#F472B6'],
                        xaxis: { type: 'category' },
                        tooltip: { y: { formatter: (v) => v + ' hari' } },
                    });
                }

                if (stats.monthly_totals.length) {
                    BloomCharts.render('chart-monthly', {
                        chart: { type: 'bar', height: 240 },
                        series: [{ name: 'Menstruasi', data: stats.monthly_totals }],
                        plotOptions: { bar: { borderRadius: 8, columnWidth: '50%' } },
                        colors: ['#8B5CF6'],
                        xaxis: { type: 'category' },
                    });
                }

                if (stats.mood_counts.some((i) => i.value > 0)) {
                    BloomCharts.render('chart-mood', {
                        chart: { type: 'donut', height: 320 },
                        series: stats.mood_counts.map((i) => i.value),
                        labels: stats.mood_counts.map((i) => i.label),
                        colors: stats.mood_counts.map((i) => i.color),
                        legend: { position: 'bottom' },
                        responsive: [{ breakpoint: 480, options: { legend: { position: 'bottom' } } }],
                    });
                }

                if (stats.flow_counts.some((f) => f.value > 0)) {
                    BloomCharts.render('chart-flow', {
                        chart: { type: 'pie', height: 280 },
                        series: stats.flow_counts.map((f) => f.value),
                        labels: stats.flow_counts.map((f) => f.label),
                        colors: ['#34D399', '#F472B6', '#EF4444'],
                        legend: { position: 'bottom' },
                    });
                }
            });
        </script>
    @endpush

    <x-bloom.page-header icon="chart" title="Statistik" subtitle="Analisis mendalam tentang siklus Anda" />

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4" data-aos="fade-up">
        <x-bloom.stat-card icon="heart" label="Total Menstruasi" :value="$stats['count']" sub="Siklus tercatat" delay="0" />
        <x-bloom.stat-card icon="clock" label="Rata-rata Siklus" :value="$stats['average_cycle'] ? $stats['average_cycle'].' hari' : '—'" sub="Dari riwayat Anda" delay="50" />
        <x-bloom.stat-card icon="flame" label="Rata-rata Durasi" :value="$stats['average_duration'] ? $stats['average_duration'].' hari' : '—'" sub="Per menstruasi" delay="100" />
        <x-bloom.stat-card icon="sparkles" label="Siklus Terpanjang" :value="$stats['longest_cycle'] ? $stats['longest_cycle'].' hari' : '—'" sub="Rekor Anda" delay="150" />
    </div>

    @if($stats['count'] === 0)
        <div class="mt-5">
            <x-bloom.empty-state
                icon="chart"
                title="Belum ada statistik"
                message="Catat beberapa siklus menstruasi dan Mimiw akan menampilkan analisis tentang siklus Anda."
            >
                <x-slot:action>
                    <a href="{{ route('calendar.index') }}" class="btn-primary">
                        <x-bloom.icon name="calendar" class="h-4 w-4" /> Mulai mencatat
                    </a>
                </x-slot:action>
            </x-bloom.empty-state>
        </div>
    @else
        <div class="grid grid-cols-1 gap-5 mt-5 lg:grid-cols-2">
            <div class="glass-card p-4" data-aos="fade-up" data-aos-delay="50">
                <h2 class="font-semibold text-ink dark:text-gray-100 mb-2">Tren Panjang Siklus</h2>
                <div id="chart-cycle"></div>
            </div>

            <div class="glass-card p-4" data-aos="fade-up" data-aos-delay="100">
                <h2 class="font-semibold text-ink dark:text-gray-100 mb-2">Durasi per Menstruasi</h2>
                <div id="chart-duration"></div>
            </div>

            <div class="glass-card p-4" data-aos="fade-up" data-aos-delay="150">
                <h2 class="font-semibold text-ink dark:text-gray-100 mb-2">Menstruasi per Bulan</h2>
                <div id="chart-monthly"></div>
            </div>

            <div class="glass-card p-4" data-aos="fade-up" data-aos-delay="200">
                <h2 class="font-semibold text-ink dark:text-gray-100 mb-2">Ringkasan Suasana Hati</h2>
                <div id="chart-mood"></div>
            </div>

            <div class="glass-card p-4 lg:col-span-2" data-aos="fade-up" data-aos-delay="250">
                <h2 class="font-semibold text-ink dark:text-gray-100 mb-2">Distribusi Volume</h2>
                <div id="chart-flow" class="lg:max-w-md lg:mx-auto w-full"></div>
            </div>
        </div>
    @endif
</x-app-layout>
