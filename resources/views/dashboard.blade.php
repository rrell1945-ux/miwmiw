<x-app-layout>
    @section('title', 'Beranda')

    @push('scripts')
        <script>
            function dashboardFlow() {
                return {
                    today: @json($today),
                    startDate: @json($today),
                    saving: false,
                    missedDate: @json($missedDate),
                    missedLabel: '',
                    flow: null,
                    mood: null,
                    symptoms: [],
                    note: '',

                    init() {
                        if (this.missedDate) {
                            this.missedLabel = this.formatLabel(this.missedDate);
                            setTimeout(() => window.dispatchEvent(new CustomEvent('bloom:open-missed-day')), 250);
                        }
                    },

                    formatLabel(dateStr) {
                        try {
                            return new Date(dateStr + 'T00:00:00').toLocaleDateString('id-ID', {
                                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
                            });
                        } catch (err) {
                            return dateStr;
                        }
                    },

                    openStart(date) {
                        this.startDate = date || this.today;
                        window.dispatchEvent(new CustomEvent('bloom:open-dashboard-start'));
                    },

                    async startPeriod() {
                        this.saving = true;
                        try {
                            const payload = { start_date: this.startDate };
                            if (this.flow) payload.flow = this.flow;
                            if (this.mood) payload.mood = this.mood;
                            payload.symptoms = this.symptoms;
                            if (this.note) payload.notes = this.note;

                            const res = await fetch(@json(route('periods.start')), {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify(payload),
                            });
                            const data = await res.json();
                            if (!res.ok) {
                                window.bloomToast.error(data.message || 'Terjadi kesalahan');
                                return;
                            }
                            window.dispatchEvent(new CustomEvent('bloom:close-dashboard-start'));
                            window.bloomToast.success(data.message);
                            setTimeout(() => window.location.reload(), 900);
                        } catch (err) {
                            window.bloomToast.error('Terjadi kesalahan jaringan, coba lagi');
                        } finally {
                            this.saving = false;
                        }
                    },

                    openCheckin() {
                        this.flow = null;
                        this.mood = null;
                        this.symptoms = [];
                        this.note = '';
                        window.dispatchEvent(new CustomEvent('bloom:open-checkin-day'));
                    },

                    toggleSymptom(key) {
                        const i = this.symptoms.indexOf(key);
                        if (i > -1) {
                            this.symptoms.splice(i, 1);
                        } else {
                            this.symptoms.push(key);
                        }
                    },

                    async checkIn() {
                        await this.extendUntil(null);
                    },

                    async confirmMissed() {
                        await this.extendUntil(this.missedDate);
                    },

                    async extendUntil(until) {
                        this.saving = true;
                        try {
                            const payload = {};
                            if (until) payload.until = until;
                            if (this.flow) payload.flow = this.flow;
                            if (this.mood) payload.mood = this.mood;
                            payload.symptoms = this.symptoms;
                            if (this.note) payload.notes = this.note;

                            const res = await fetch(
                                @json(route('periods.extend', ['period' => '__ID__'])).replace('__ID__', @json($ongoingPeriod?->id)),
                                {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    },
                                    body: JSON.stringify(payload),
                                }
                            );
                            const data = await res.json();
                            if (!res.ok) {
                                window.bloomToast.error(data.message || 'Terjadi kesalahan');
                                return;
                            }
                            window.dispatchEvent(new CustomEvent('bloom:close-missed-day'));
                            window.dispatchEvent(new CustomEvent('bloom:close-checkin-day'));
                            window.bloomToast.success(data.message);
                            setTimeout(() => window.location.reload(), 900);
                        } catch (err) {
                            window.bloomToast.error('Terjadi kesalahan jaringan, coba lagi');
                        } finally {
                            this.saving = false;
                        }
                    },

                    finishPeriod() {
                        window.bloomToast.confirm({
                            title: 'Tandai menstruasi selesai?',
                            text: 'Pastikan Anda sudah tidak menstruasi lagi sebelum menyelesaikan.',
                            confirmText: 'Ya, selesai',
                            onConfirm: async () => {
                                try {
                                    const res = await fetch(
                                        @json(route('periods.finish', ['period' => '__ID__'])).replace('__ID__', @json($ongoingPeriod?->id)),
                                        {
                                            method: 'POST',
                                            headers: {
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                            },
                                        }
                                    );
                                    const data = await res.json();
                                    window.bloomToast.success(data.message || 'Menstruasi selesai');
                                    if (data.duration) {
                                        setTimeout(() => {
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Periode selesai',
                                                text: 'Menstruasi Anda berlangsung selama ' + data.duration + ' hari. Perhitungan siklus dan prediksi sudah diperbarui.',
                                                confirmButtonColor: '#EC4899',
                                            }).then(() => window.location.reload());
                                        }, 600);
                                    } else {
                                        setTimeout(() => window.location.reload(), 900);
                                    }
                                } catch (err) {
                                    window.bloomToast.error('Terjadi kesalahan jaringan, coba lagi');
                                }
                            },
                        });
                    },
                };
            }
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                fetch(@json(route('dashboard.notifications')), {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(r => r.json())
                    .then(async (data) => {
                        const enabled = {{ auth()->user()->setting()->notifications_enabled ? 'true' : 'false' }};
                        if (!enabled || !data.items?.length) return;
                        const granted = await BloomPWA.requestPermission();
                        if (granted) {
                            data.items.forEach(item => BloomPWA.notify(item.title, item.body));
                        }
                    })
                    .catch(() => {});
            });
        </script>
    @endpush

    <div class="space-y-5" x-data="dashboardFlow()">
        <div data-aos="fade-up">
            <p class="text-sm font-medium text-pink-500 dark:text-pink-300">{{ $greeting }}</p>
            <h1 class="mt-0.5 text-2xl font-bold text-ink sm:text-3xl dark:text-gray-100">
                Halo, {{ auth()->user()->displayName() }}
            </h1>
            <p class="mt-0.5 text-sm text-gray-400">{{ $today }}</p>
        </div>

        @if($isAdmin)
            {{-- ---------- Admin: pantauan siklus Salma ---------- --}}
            <div class="glass-card p-5 sm:p-6" data-aos="fade-up" data-aos-delay="50">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-pink-50 text-pink-500 dark:bg-pink-500/10 dark:text-pink-300">
                        <x-bloom.icon name="heart" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-[10px] font-medium uppercase tracking-wider text-gray-400">Pantauan Siklus</p>
                        <h2 class="font-bold leading-tight text-ink dark:text-gray-100">{{ $subject->name }}</h2>
                    </div>
                </div>

                @if($ongoingPeriod)
                    <div class="mt-5 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs text-gray-400">Sedang menstruasi · Hari ke-{{ $dayNumber }}</p>
                            <p class="mt-1 text-xl font-bold text-ink sm:text-2xl dark:text-gray-100">{{ $ongoingPeriod->start_date->format('d M') }} — {{ $ongoingPeriod->end_date->format('d M Y') }}</p>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1 text-xs font-medium text-rose-500 dark:bg-rose-500/10 dark:text-rose-300">
                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-rose-500"></span> Berlangsung
                        </span>
                    </div>
                @elseif($latestPeriod)
                    <div class="mt-5 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs text-gray-400">Menstruasi terakhir</p>
                            <p class="mt-1 text-xl font-bold text-ink sm:text-2xl dark:text-gray-100">{{ $latestPeriod->start_date->format('d M') }} — {{ $latestPeriod->end_date->format('d M Y') }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $latestPeriod->duration }} hari</span>
                    </div>
                @else
                    <p class="mt-5 text-sm text-gray-400">Belum ada data menstruasi yang tercatat.</p>
                @endif

                @if($prediction['has_data'])
                    <div class="mt-5 grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-gray-50 p-3 dark:bg-gray-800">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-gray-400">Perkiraan berikutnya</p>
                            <p class="mt-0.5 text-lg font-bold text-ink dark:text-gray-100">{{ \Carbon\CarbonImmutable::parse($prediction['next_start'])->format('d M') }}</p>
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-3 dark:bg-gray-800">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-gray-400">Panjang siklus</p>
                            <p class="mt-0.5 text-lg font-bold text-ink dark:text-gray-100">{{ $prediction['average_cycle'] ? $prediction['average_cycle'].' hr' : '—' }}</p>
                        </div>
                        <div class="rounded-2xl bg-gray-50 p-3 dark:bg-gray-800">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-gray-400">Ovulasi</p>
                            <p class="mt-0.5 text-lg font-bold text-ink dark:text-gray-100">{{ \Carbon\CarbonImmutable::parse($prediction['ovulation'])->format('d M') }}</p>
                        </div>
                    </div>
                @endif
            </div>

            @if($recentPeriods->isNotEmpty())
                <div class="glass-card p-5" data-aos="fade-up" data-aos-delay="100">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-bold text-ink dark:text-gray-100">Riwayat Terbaru</h2>
                        <a href="{{ route('history.index') }}" class="text-xs font-medium text-pink-500 hover:text-pink-600">Lihat semua →</a>
                    </div>
                    <div class="space-y-3">
                        @foreach($recentPeriods as $period)
                            <div class="flex items-center gap-3">
                                <span class="h-9 w-9 shrink-0 rounded-xl bg-rose-100 dark:bg-rose-500/20 flex items-center justify-center text-rose-500">
                                    <x-bloom.icon name="droplet" class="h-4 w-4" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-ink dark:text-gray-100">{{ $period->start_date->format('d M Y') }} — {{ $period->end_date->format('d M Y') }}</p>
                                    <p class="text-xs text-gray-400">{{ $period->duration }} hari{{ $period->cycle_length ? ' · siklus '.$period->cycle_length.' hari' : '' }}</p>
                                </div>
                                @if($period->isOngoing())
                                    <span class="badge bg-rose-100 text-rose-500 dark:bg-rose-500/20 dark:text-rose-300">Berlangsung</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            {{-- ---------- User (Salma): alur catat harian ---------- --}}
            @if($ongoingPeriod)
                @php
                    $stripFrom = \Carbon\CarbonImmutable::parse($ongoingPeriod->start_date);
                    $stripTo = \Carbon\CarbonImmutable::parse($ongoingPeriod->end_date)->max(today());
                @endphp

                <div class="glass-card p-5 sm:p-6" data-aos="fade-up" data-aos-delay="50">
                    <div class="flex items-center justify-between gap-3">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-pink-50 px-3 py-1 text-xs font-medium text-pink-600 dark:bg-pink-500/10 dark:text-pink-300">
                            <x-bloom.icon name="droplet" class="h-3.5 w-3.5" /> Sedang menstruasi
                        </span>
                        <span class="text-xs font-medium text-gray-400">Mulai {{ $ongoingPeriod->start_date->format('d M') }}</span>
                    </div>

                    <h2 class="mt-4 text-3xl font-bold leading-tight text-ink dark:text-gray-100">Hari ke-{{ $dayNumber }}</h2>
                    <p class="mt-1 text-sm text-gray-400">Jaga diri baik-baik, ya. Apakah menstruasi masih berlangsung?</p>

                    <div class="no-scrollbar mt-5 flex items-center gap-1.5 overflow-x-auto pb-1">
                        @foreach ($stripFrom->toPeriod($stripTo) as $day)
                            @php $isToday = $day->format('Y-m-d') === $today; @endphp
                            <div class="flex shrink-0 flex-col items-center gap-1">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full text-xs font-bold transition-all @class([
                                    'bg-pink-500 text-white scale-110' => $isToday,
                                    'bg-pink-50 text-pink-500 dark:bg-pink-500/10' => !$isToday,
                                ])">{{ $day->format('d') }}</span>
                                <span class="text-[9px] uppercase tracking-wide {{ $isToday ? 'font-semibold text-pink-600 dark:text-pink-300' : 'text-gray-400' }}">{{ $day->format('D') }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-3">
                        @if($checkedInToday)
                            <button type="button" @click="openCheckin()" x-bind:disabled="saving" class="btn-secondary rounded-xl py-3.5 text-emerald-600">
                                <x-bloom.icon name="check" class="h-5 w-5" /> Sudah Absen Hari Ini
                            </button>
                        @else
                            <button type="button" @click="openCheckin()" x-bind:disabled="saving" class="btn-primary rounded-xl py-3.5">
                                <template x-if="!saving">
                                    <span class="flex items-center gap-2"><x-bloom.icon name="check" class="h-5 w-5" /> Ya, masih</span>
                                </template>
                                <template x-if="saving">
                                    <span class="inline-block h-5 w-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                </template>
                            </button>
                        @endif
                        <button type="button" @click="finishPeriod()" class="btn-secondary rounded-xl py-3.5">
                            <x-bloom.icon name="check-circle" class="h-5 w-5" /> Sudah Selesai
                        </button>
                    </div>
                </div>
            @elseif($latestPeriod)
                <div class="glass-card p-5 sm:p-6" data-aos="fade-up" data-aos-delay="50">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-pink-50 px-3 py-1 text-xs font-medium text-pink-600 dark:bg-pink-500/10 dark:text-pink-300">
                        <x-bloom.icon name="sparkles" class="h-3.5 w-3.5" /> Menstruasi terakhir
                    </span>
                    <h2 class="mt-4 text-2xl font-bold text-ink dark:text-gray-100">{{ $latestPeriod->start_date->format('d M Y') }} — {{ $latestPeriod->end_date->format('d M Y') }}</h2>
                    <p class="mt-1 text-sm text-gray-400">{{ $latestPeriod->duration }} hari{{ $latestPeriod->cycle_length ? ' · siklus '.$latestPeriod->cycle_length.' hari' : '' }}</p>

                    <button type="button" @click="openStart(today)" class="btn-primary mt-6 w-full rounded-xl py-3.5">
                        <x-bloom.icon name="plus" class="h-5 w-5" /> Mulai Menstruasi Baru
                    </button>
                </div>
            @else
                <div class="glass-card p-6 text-center" data-aos="fade-up" data-aos-delay="50">
                    <div class="mx-auto h-14 w-14 rounded-full bg-pink-100 dark:bg-gray-700 flex items-center justify-center">
                        <x-bloom.icon name="droplet" class="h-7 w-7 text-pink-500" />
                    </div>
                    <h2 class="mt-3 font-bold text-ink dark:text-gray-100">Selamat datang di Mimiw</h2>
                    <p class="mt-1 text-sm text-gray-400">Ketika menstruasi tiba, tandai hari pertamanya di sini. Mimiw akan merawat catatanmu.</p>
                    <button type="button" @click="openStart(today)" class="btn-primary mt-5">
                        <x-bloom.icon name="plus" class="h-4 w-4" /> Mulai Menstruasi
                    </button>
                </div>
            @endif
        @endif

        @if($latestAdvice)
            <a href="{{ route('messages.index') }}" class="glass-card flex items-start gap-3 p-4 transition-all duration-200 hover:shadow-soft" data-aos="fade-up" data-aos-delay="50">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-pink-50 text-pink-500 dark:bg-pink-500/10 dark:text-pink-300">
                    <x-bloom.icon name="chat" class="h-5 w-5" />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-sm text-ink dark:text-gray-100 flex items-center gap-2">
                        Saran dari Admin
                        @if(! $latestAdvice->read_at)
                            <span class="inline-flex h-2 w-2 rounded-full bg-pink-500"></span>
                        @endif
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5 line-clamp-2">{{ $latestAdvice->body }}</p>
                    @if($latestAdvice->periodDay)
                        <p class="text-[10px] text-pink-500 dark:text-pink-300 mt-1">Rekomendasi · {{ $latestAdvice->periodDay->day_date->isoFormat('D MMM YYYY') }}</p>
                    @else
                        <p class="text-[10px] text-gray-400 mt-1">{{ $latestAdvice->created_at->format('d M Y, H:i') }}</p>
                    @endif
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-pink-600 dark:text-pink-300 mt-1.5">Buka Pesan <x-bloom.icon name="chevron-right" class="h-3.5 w-3.5" /></span>
                </div>
            </a>
        @endif

        @if($prediction['has_data'] && ! $ongoingPeriod)
            <div class="glass-card p-5 sm:p-6 lg:p-8" data-aos="fade-up" data-aos-delay="100">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-pink-50 px-3 py-1 text-xs font-medium text-pink-600 dark:bg-pink-500/10 dark:text-pink-300">
                        <x-bloom.icon name="sparkles" class="h-3.5 w-3.5" /> {{ $tip['phase'] }}
                    </span>
                    <span class="text-xs font-medium text-gray-400">
                        @if($prediction['days_until_next'] === 0)
                            Hari ini
                        @elseif($prediction['days_until_next'] === 1)
                            Besok
                        @else
                            Dalam {{ $prediction['days_until_next'] }} hari
                        @endif
                    </span>
                </div>

                <h2 class="mt-4 text-2xl font-bold leading-tight text-ink sm:text-3xl dark:text-gray-100">{{ $tip['title'] }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-gray-500 dark:text-gray-400">{{ $tip['body'] }}</p>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-gray-50 p-3 dark:bg-gray-800">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-gray-400">Perkiraan berikutnya</p>
                        <p class="mt-0.5 text-lg font-bold text-ink dark:text-gray-100">{{ \Carbon\CarbonImmutable::parse($prediction['next_start'])->format('d M') }}</p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-3 dark:bg-gray-800">
                        <p class="text-[10px] font-medium uppercase tracking-wider text-gray-400">Ovulasi</p>
                        <p class="mt-0.5 text-lg font-bold text-ink dark:text-gray-100">{{ \Carbon\CarbonImmutable::parse($prediction['ovulation'])->format('d M') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4" data-aos="fade-up" data-aos-delay="150">
            <x-bloom.stat-card icon="calendar" label="Menstruasi Terakhir" :value="$prediction['last_start'] ? \Carbon\CarbonImmutable::parse($prediction['last_start'])->format('d M Y') : '—'" :sub="$prediction['last_start'] ? 'terakhir '. \Carbon\CarbonImmutable::parse($prediction['last_end'])->format('d M') : 'belum ada data'" delay="0" />
            <x-bloom.stat-card icon="sparkles" label="Perkiraan berikutnya" :value="$prediction['next_start'] ? \Carbon\CarbonImmutable::parse($prediction['next_start'])->format('d M Y') : '—'" :sub="$prediction['is_late'] ? 'Terlambat '.$prediction['days_late'].' hari' : 'Dalam '. ($prediction['days_until_next'] ?? '—').' hari'" delay="50" />
            <x-bloom.stat-card icon="clock" label="Panjang Siklus" :value="$prediction['average_cycle'] ? $prediction['average_cycle'].' hari' : '—'" sub="Rata-rata dari riwayat" delay="100" />
            <x-bloom.stat-card icon="flame" label="Fase Saat Ini" :value="$prediction['phase']" sub="Kesadaran siklus" delay="150" />
        </div>

        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2" data-aos="fade-up" data-aos-delay="200">
            <x-bloom.stat-card
                icon="heart"
                label="Suasana Hati"
                :value="$lastMood ? (\App\Models\Mood::emojiFor($lastMood) ?? '😊').' '.(\App\Models\Mood::labelFor($lastMood) ?? '—') : '—'"
                sub="Suasana hati terakhir"
                delay="0"
            />

            <div class="glass-card flex items-start gap-3 p-4" data-aos="fade-up" data-aos-delay="50">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500 dark:bg-emerald-500/10 dark:text-emerald-300">
                    <x-bloom.icon name="droplet" class="h-5 w-5" />
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-sm text-ink dark:text-gray-100">Tips Hari Ini</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $tip['body'] }}</p>
                </div>
            </div>
        </div>

        <div data-aos="fade-up" data-aos-delay="250">
            <h2 class="text-sm font-semibold text-ink dark:text-gray-100 mb-3">Akses Cepat</h2>
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <a href="{{ route('calendar.index') }}" class="group glass-card flex items-center gap-3 p-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-soft">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-pink-50 text-pink-500 transition-transform duration-200 group-hover:scale-110 dark:bg-pink-500/10 dark:text-pink-300">
                        <x-bloom.icon name="calendar" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-ink dark:text-gray-100">Kalender</p>
                        <p class="text-xs text-gray-400">Catat & lihat</p>
                    </div>
                </a>
                <a href="{{ route('history.index') }}" class="group glass-card flex items-center gap-3 p-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-soft">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-500 transition-transform duration-200 group-hover:scale-110 dark:bg-purple-500/10 dark:text-purple-300">
                        <x-bloom.icon name="clock" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-ink dark:text-gray-100">Riwayat</p>
                        <p class="text-xs text-gray-400">Semua catatan</p>
                    </div>
                </a>
                <a href="{{ route('statistics.index') }}" class="group glass-card flex items-center gap-3 p-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-soft">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-500 transition-transform duration-200 group-hover:scale-110 dark:bg-sky-500/10 dark:text-sky-300">
                        <x-bloom.icon name="chart" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-ink dark:text-gray-100">Statistik</p>
                        <p class="text-xs text-gray-400">Analisis</p>
                    </div>
                </a>
                <a href="{{ route('settings.index') }}" class="group glass-card flex items-center gap-3 p-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-soft">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-500 transition-transform duration-200 group-hover:scale-110 dark:bg-amber-500/10 dark:text-amber-300">
                        <x-bloom.icon name="gear" class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-ink dark:text-gray-100">Pengaturan</p>
                        <p class="text-xs text-gray-400">Personalisasi</p>
                    </div>
                </a>
            </div>
        </div>

        {{-- Modal: mulai menstruasi --}}
        @if(! $isAdmin)
            <x-bloom.modal id="dashboard-start" title="Catat Menstruasi" maxWidth="md">
                <div class="space-y-4">
                    <div>
                        <x-input-label for="dashboard-start-date" :value="__('Hari Pertama')" />
                        <input id="dashboard-start-date" type="date" x-model="startDate" :max="today" class="input-field mt-1.5">
                        <button type="button" @click="startDate = today" class="text-xs font-medium text-pink-500 hover:text-pink-600 mt-1.5">Gunakan hari ini</button>
                    </div>

                    @include('partials._checkin-fields')

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <button type="button" @click="$dispatch('bloom:close-dashboard-start')" class="btn-ghost">Batal</button>
                        <button type="button" @click="startPeriod()" x-bind:disabled="saving || !startDate" class="btn-primary flex items-center justify-center gap-2">
                            <template x-if="!saving">
                                <span class="flex items-center gap-2"><x-bloom.icon name="check" class="h-4 w-4" /> Mulai Menstruasi</span>
                            </template>
                            <template x-if="saving">
                                <span class="inline-block h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            </template>
                        </button>
                    </div>
                </div>
            </x-bloom.modal>

            {{-- Modal: absen harian dengan kondisi --}}
            <x-bloom.modal id="checkin-day" title="Absen Hari Ini" maxWidth="md">
                <div class="space-y-4">
                    <div class="rounded-xl bg-pink-50 dark:bg-gray-800 px-4 py-3 flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-300">Hari ini, tetap menstruasi</span>
                        <span class="inline-flex items-center gap-1.5 text-sm font-bold text-pink-600 dark:text-pink-300">
                            <x-bloom.icon name="droplet" class="h-4 w-4" /> Hari ke-{{ $dayNumber ?? 1 }}
                        </span>
                    </div>

                    @include('partials._checkin-fields')

                    <button type="button" @click="checkIn()" x-bind:disabled="saving" class="btn-primary w-full">
                        <template x-if="!saving">
                            <span class="flex items-center gap-2"><x-bloom.icon name="check" class="h-4 w-4" /> Absen Sekarang</span>
                        </template>
                        <template x-if="saving">
                            <span class="inline-block h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        </template>
                    </button>
                </div>
            </x-bloom.modal>

            {{-- Modal: hari terlewat --}}
            @if($ongoingPeriod && $missedDate)
                <x-bloom.modal id="missed-day" title="Cek Cepat" maxWidth="md">
                    <div class="space-y-4">
                        <div class="rounded-xl bg-pink-50 dark:bg-gray-800 px-4 py-3">
                            <p class="font-semibold text-ink dark:text-gray-100 text-center">
                                Apakah menstruasi masih berlangsung pada <span class="text-pink-600 dark:text-pink-300" x-text="missedLabel"></span>?
                            </p>
                            <p class="mt-1 text-sm text-gray-400 text-center">Anda belum sempat membuka aplikasi hari itu. Kami ingin mencatat siklus Anda dengan akurat.</p>
                        </div>

                        @include('partials._checkin-fields')

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <button type="button" @click="$dispatch('bloom:close-missed-day')" class="btn-ghost">Tidak</button>
                            <button type="button" @click="confirmMissed()" x-bind:disabled="saving" class="btn-primary flex items-center justify-center gap-2">
                                <template x-if="!saving">
                                    <span class="flex items-center gap-2"><x-bloom.icon name="check" class="h-4 w-4" /> Ya, masih</span>
                                </template>
                                <template x-if="saving">
                                    <span class="inline-block h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                </template>
                            </button>
                        </div>
                    </div>
                </x-bloom.modal>
            @endif
        @endif
    </div>
</x-app-layout>
