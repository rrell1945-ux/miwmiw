<x-app-layout>
    @section('title', 'Pesan')

    @push('scripts')
        <script>
            function chatComposer() {
                const DRAFT_KEY = 'mimiw-chat-draft';
                return {
                    body: localStorage.getItem(DRAFT_KEY) || '',
                    saving: false,
                    init() {
                        this.$watch('body', (value) => {
                            if (value) localStorage.setItem(DRAFT_KEY, value);
                            else localStorage.removeItem(DRAFT_KEY);
                        });
                    },
                    async send() {
                        if (!this.body.trim()) {
                            Swal.fire({ icon: 'warning', title: 'Tulis pesannya dulu', confirmButtonColor: '#EC4899' });
                            return;
                        }
                        this.saving = true;
                        try {
                            const res = await fetch(@json(route('messages.store')), {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({ body: this.body }),
                            });
                            let data = {};
                            try {
                                data = await res.json();
                            } catch (err) {
                                /* body response bukan JSON (mis. 419/500) */
                            }
                            if (!res.ok) {
                                Swal.fire({ icon: 'error', title: data.message || (res.status === 419 ? 'Sesi berakhir, muat ulang halaman' : 'Gagal mengirim, coba lagi'), confirmButtonColor: '#EC4899' });
                                return;
                            }
                            localStorage.removeItem(DRAFT_KEY);
                            window.location.reload();
                        } catch (err) {
                            window.bloomToast.error('Terjadi kesalahan jaringan, coba lagi');
                        } finally {
                            this.saving = false;
                        }
                    },
                };
            }

            function commentBox(id, initial) {
                return {
                    id,
                    body: initial,
                    saving: false,
                    async submit() {
                        if (!this.body.trim()) {
                            Swal.fire({ icon: 'warning', title: 'Tulis pesan rekomendasinya dulu', confirmButtonColor: '#EC4899' });
                            return;
                        }
                        this.saving = true;
                        try {
                            const res = await fetch(@json(route('messages.store')), {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({ period_day_id: this.id, body: this.body }),
                            });
                            let data = {};
                            try {
                                data = await res.json();
                            } catch (err) {
                                /* body response bukan JSON (mis. 419/500) */
                            }
                            if (!res.ok) {
                                Swal.fire({ icon: 'error', title: data.message || (res.status === 419 ? 'Sesi berakhir, muat ulang halaman' : 'Gagal mengirim, coba lagi'), confirmButtonColor: '#EC4899' });
                                return;
                            }
                            window.bloomToast.success(data.message || 'Rekomendasi terkirim');
                            setTimeout(() => window.location.reload(), 700);
                        } catch (err) {
                            window.bloomToast.error('Terjadi kesalahan jaringan, coba lagi');
                        } finally {
                            this.saving = false;
                        }
                    },
                };
            }
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const thread = document.getElementById('chat-thread');
                if (thread) thread.scrollTop = thread.scrollHeight;
            });
        </script>
    @endpush

    <x-bloom.page-header
        icon="chat"
        title="Pesan"
        :subtitle="$isAdmin ? 'Kirim atau balas pesan dari '.$subject->name : 'Kirim kondisi Anda atau baca balasan dari admin'"
    />

    {{-- ---------- Percakapan dua arah ---------- --}}
    <div class="glass-card flex flex-col overflow-hidden" data-aos="fade-up">
        <div class="flex items-center gap-3 border-b border-pink-100/60 bg-gray-50 px-4 py-3 dark:border-gray-700/60 dark:bg-gray-800/60 sm:px-5">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-pink-500 text-sm font-bold text-white">
                {{ mb_substr(($isAdmin ? $subject->name : $counterpart->name), 0, 1) }}
            </div>
            <div class="min-w-0">
                <p class="font-semibold text-sm text-ink dark:text-gray-100 truncate">{{ $isAdmin ? $subject->name : $counterpart->name }}</p>
                <p class="text-[11px] text-gray-400">{{ $isAdmin ? 'Obrolan dengan user' : 'Balasan dari admin' }}</p>
            </div>
        </div>

        <div x-data="chatComposer()" class="flex flex-col">
            <div id="chat-thread" class="max-h-[28rem] min-h-[16rem] overflow-y-auto px-4 sm:px-5 py-4 space-y-3 no-scrollbar">
                @forelse($messages as $message)
                    @php $mine = $message->sender_id === auth()->id(); @endphp
                    <div class="flex @if($mine) justify-end @else justify-start @endif">
                        <div class="max-w-[80%] sm:max-w-[70%]">
                            @if(! $mine)
                                <p class="text-[11px] text-gray-400 mb-1 ml-1">{{ $message->sender->name }}</p>
                            @endif
                            <div class="rounded-2xl px-4 py-2.5 text-sm leading-relaxed @if($mine)
                                rounded-br-md bg-pink-500 text-white
                            @else
                                rounded-bl-md bg-gray-100 text-ink dark:bg-gray-700/60 dark:text-gray-100
                            @endif">
                                @if($message->periodDay)
                                    <p class="text-[10px] font-semibold uppercase tracking-wider mb-1 @if($mine) text-white/75 @else text-pink-500 dark:text-pink-300 @endif">
                                        Rekomendasi · {{ $message->periodDay->day_date->isoFormat('D MMM YYYY') }}
                                    </p>
                                @endif
                                <p class="whitespace-pre-wrap">{{ $message->body }}</p>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1 @if($mine) text-right mr-1 @else ml-1 @endif">
                                {{ $message->created_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center">
                        <div class="mx-auto h-14 w-14 rounded-full bg-pink-100 dark:bg-gray-700 flex items-center justify-center">
                            <x-bloom.icon name="chat" class="h-7 w-7 text-pink-500 dark:text-pink-300" />
                        </div>
                        <p class="mt-3 text-sm font-semibold text-ink dark:text-gray-100">Belum ada percakapan</p>
                        <p class="mt-1 text-xs text-gray-400">Mulai dengan mengirim pesan kondisi Anda.</p>
                    </div>
                @endforelse
            </div>

            <div class="border-t border-pink-100/60 bg-gray-50 p-3 dark:border-gray-700/60 dark:bg-gray-800/60 sm:p-4">
                <form @submit.prevent="send()" class="flex items-end gap-2">
                    <textarea
                        x-model="body"
                        rows="1"
                        placeholder="Tulis pesan..."
                        class="input-field resize-none flex-1"
                        @keydown.enter.exact.prevent="send()"
                    ></textarea>
                    <button type="submit" x-bind:disabled="saving" class="btn-primary shrink-0 flex items-center justify-center gap-2">
                        <template x-if="!saving">
                            <span class="flex items-center gap-2"><x-bloom.icon name="chat" class="h-4 w-4" /> Kirim</span>
                        </template>
                        <template x-if="saving">
                            <span class="inline-block h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        </template>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ---------- Admin: rekomendasi per absen harian ---------- --}}
    @if($isAdmin)
        <div class="mt-8">
            <div class="mb-4 flex items-center gap-3" data-aos="fade-up">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-pink-50 text-pink-500 dark:bg-pink-500/10 dark:text-pink-300">
                    <x-bloom.icon name="check-circle" class="h-4 w-4" />
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-ink dark:text-gray-100">Rekomendasi Absen Harian</h2>
                    <p class="text-xs text-gray-400">Berikan dukungan lembut untuk setiap hari yang dicatat {{ $subject->name }}.</p>
                </div>
            </div>

            @if($checkins->isEmpty())
                <x-bloom.empty-state
                    icon="chat"
                    title="Belum ada absen harian"
                    message="{{ $subject->name }} belum mencatat absen harian selama menstruasi. Setiap hari yang dicatat akan muncul di sini untuk diberi rekomendasi."
                />
            @else
                <div class="space-y-4" data-aos="fade-up">
                    @foreach($checkins as $day)
                        <div class="glass-card p-4 transition-all duration-200 hover:shadow-soft sm:p-5" x-data="commentBox({{ $day->id }}, @js($day->message?->body ?? ''))">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex min-w-0 items-start gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-500 dark:bg-rose-500/10 dark:text-rose-300">
                                        <x-bloom.icon name="droplet" class="h-4 w-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-ink dark:text-gray-100">{{ $day->day_date->isoFormat('dddd, D MMMM YYYY') }}</p>
                                        <p class="text-xs text-gray-400">
                                            Menstruasi {{ $day->period->start_date->format('d M Y') }} — {{ $day->period->end_date->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                                @if($day->message)
                                    <span class="badge shrink-0 bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-300">Sudah direkomendasikan</span>
                                @else
                                    <span class="badge shrink-0 bg-pink-100 text-pink-600 dark:bg-pink-500/20 dark:text-pink-300">Belum ada rekomendasi</span>
                                @endif
                            </div>

                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @if($day->flow)
                                    <span class="badge bg-pink-100 text-pink-600 dark:bg-pink-500/20 dark:text-pink-300">{{ \App\Models\Period::FLOWS[$day->flow] ?? $day->flow }}</span>
                                @endif
                                @if($day->mood)
                                    <span class="badge bg-purple-100 text-purple-600 dark:bg-purple-500/20 dark:text-purple-300">
                                        {{ \App\Models\Mood::emojiFor($day->mood) }} {{ \App\Models\Mood::labelFor($day->mood) ?? $day->mood }}
                                    </span>
                                @endif
                                @foreach($day->symptomsLabels() as $symptom)
                                    <span class="badge bg-rose-50 text-rose-500 dark:bg-rose-500/10 dark:text-rose-300 border border-rose-100 dark:border-rose-500/20">{{ $symptom }}</span>
                                @endforeach
                                @if($day->notes)
                                    <span class="mt-1 w-full text-xs italic text-gray-400">“{{ $day->notes }}”</span>
                                @endif
                            </div>

                            <div class="mt-4 border-t border-gray-100 pt-4 dark:border-gray-700/60">
                                <x-input-label value="Rekomendasi admin" />
                                <textarea
                                    x-model="body"
                                    rows="2"
                                    class="input-field mt-1.5 resize-none"
                                    placeholder="Tulis rekomendasi atau komentar untuk hari ini..."
                                ></textarea>
                                <div class="mt-2 flex justify-end">
                                    <button type="button" @click="submit()" x-bind:disabled="saving" class="btn-primary flex items-center justify-center gap-2">
                                        <template x-if="!saving">
                                            <span class="flex items-center gap-2"><x-bloom.icon name="chat" class="h-4 w-4" /> Kirim</span>
                                        </template>
                                        <template x-if="saving">
                                            <span class="inline-block h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                        </template>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $checkins->links() }}
                </div>
            @endif
        </div>
    @endif
</x-app-layout>
