<x-app-layout>
    @section('title', 'Riwayat')

    @push('scripts')
        <script>
            function deletePeriod(id, label) {
                window.bloomToast.confirm({
                    title: 'Hapus catatan ini?',
                    text: label + ' akan dihapus permanen dan tidak dapat dibatalkan.',
                    confirmText: 'Ya, hapus',
                    onConfirm: async () => {
                        try {
                            const res = await fetch(@json(route('periods.destroy', ['period' => '__ID__'])).replace('__ID__', id), {
                                method: 'DELETE',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                            });
                            const data = await res.json();
                            if (!res.ok) {
                                window.bloomToast.error(data.message || (res.status === 419 ? 'Sesi berakhir, muat ulang halaman' : 'Gagal menghapus, coba lagi'));
                                return;
                            }
                            window.bloomToast.success(data.message || 'Dihapus');
                            setTimeout(() => window.location.reload(), 700);
                        } catch (err) {
                            window.bloomToast.error('Terjadi kesalahan jaringan, coba lagi');
                        }
                    },
                });
            }
        </script>
    @endpush

    <x-bloom.page-header icon="clock" title="Riwayat" subtitle="Perjalanan siklus Anda, satu catatan pada satu waktu" />

    @if($periods->isEmpty())
        <x-bloom.empty-state
            icon="calendar"
            title="Belum ada catatan menstruasi"
            message="Setiap perjalanan dimulai dari satu hari. Tambahkan menstruasi pertama Anda dari beranda atau kalender."
        >
            <x-slot:action>
                <a href="{{ route('dashboard') }}" class="btn-primary">
                    <x-bloom.icon name="plus" class="h-4 w-4" /> Mulai mencatat
                </a>
            </x-slot:action>
        </x-bloom.empty-state>
    @else
        <div class="relative space-y-4" data-aos="fade-up">
            <div class="absolute bottom-2 left-4 top-2 w-px bg-pink-100 dark:bg-pink-500/20"></div>

            @foreach($periods as $period)
                @php
                    $moodEmoji = \App\Models\Mood::emojiFor($period->mood);
                    $cycleLabel = $period->cycle_length
                        ? 'Siklus '.$period->cycle_length.' hari'
                        : 'Siklus pertama tercatat';
                @endphp

                <div class="relative pl-10">
                    <div class="absolute left-2 top-4 h-5 w-5 rounded-full border-4 border-white bg-pink-500 dark:border-gray-900"></div>

                    <div class="glass-card p-4 hover:-translate-y-0.5 hover:shadow-soft transition-all duration-300 overflow-hidden">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold text-ink dark:text-gray-100">
                                    {{ $period->start_date->format('d M Y') }}
                                    <span class="font-normal text-gray-400 text-sm">→ {{ $period->end_date->format('d M Y') }}</span>
                                </p>
                                <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                    <span class="badge bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-300">
                                        {{ $period->duration }} hari
                                    </span>
                                    @if($period->flow)
                                        <span class="badge bg-pink-100 text-pink-600 dark:bg-pink-500/20 dark:text-pink-300">
                                            {{ $flows[$period->flow] ?? $period->flow }}
                                        </span>
                                    @endif
                                    <span class="badge bg-purple-100 text-purple-600 dark:bg-purple-500/20 dark:text-purple-300">
                                        {{ $cycleLabel }}
                                    </span>
                                    @if($period->isOngoing())
                                        <span class="badge bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-300">
                                            Berlangsung
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if($moodEmoji)
                                <div class="h-10 w-10 shrink-0 rounded-xl bg-pink-50 dark:bg-gray-700 flex items-center justify-center text-xl">
                                    {{ $moodEmoji }}
                                </div>
                            @endif
                        </div>

                        @if(!empty($period->symptoms))
                            <div class="flex flex-wrap gap-1.5 mt-3">
                                @foreach($period->symptomsLabels() as $symptom)
                                    <span class="badge bg-rose-50 text-rose-500 dark:bg-rose-500/10 dark:text-rose-300 border border-rose-100 dark:border-rose-500/20">
                                        {{ $symptom }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @if($period->notes)
                            <p class="mt-3 text-sm text-gray-500 dark:text-gray-300 border-l-2 border-pink-200 dark:border-pink-500/40 pl-3">
                                {{ $period->notes }}
                            </p>
                        @endif

                        <div class="mt-3 pt-3 border-t border-pink-100/60 dark:border-gray-700/60 flex items-center justify-between">
                            @if($canEdit)
                                <div class="flex items-center gap-1">
                                    <button
                                        type="button"
                                        class="btn-ghost text-xs"
                                        onclick="window.dispatchEvent(new CustomEvent('bloom:period-modal-open', { detail: { date: '{{ $period->start_date->format('Y-m-d') }}' }})); window.dispatchEvent(new CustomEvent('bloom:open-period-modal'));"
                                    >
                                        <x-bloom.icon name="pencil" class="h-3.5 w-3.5" /> Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl font-medium text-xs text-rose-500 transition-all duration-200 hover:bg-rose-50 dark:hover:bg-gray-700 active:scale-95"
                                        onclick="deletePeriod({{ $period->id }}, 'Catatan {{ $period->start_date->format('d M Y') }}')"
                                    >
                                        <x-bloom.icon name="trash" class="h-3.5 w-3.5" /> Hapus
                                    </button>
                                </div>
                            @endif
                            <a href="{{ route('calendar.index') }}" class="text-xs text-pink-500 hover:text-pink-600 font-medium ml-auto">
                                Lihat di kalender →
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $periods->links() }}
        </div>

        @if($canEdit)
            <x-bloom.modal id="period-modal" title="Edit Menstruasi" maxWidth="lg">
                <x-bloom.period-form />
            </x-bloom.modal>
        @endif
    @endif
</x-app-layout>
