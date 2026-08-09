<x-app-layout>
    @section('title', 'Kalender')

    @push('scripts')
        @vite(['resources/js/calendar.js'])
        <script>
            function startPeriodModal() {
                return {
                    date: '',
                    dateLabel: '',
                    saving: false,
                    flow: null,
                    mood: null,
                    symptoms: [],
                    note: '',

                    init() {
                        window.addEventListener('bloom:start-period-open', (e) => {
                            this.date = e.detail.date;
                            this.dateLabel = formatDateLabel(e.detail.date);
                            this.flow = null;
                            this.mood = null;
                            this.symptoms = [];
                            this.note = '';
                            window.dispatchEvent(new CustomEvent('bloom:open-start-period'));
                        });
                    },

                    toggleSymptom(key) {
                        const i = this.symptoms.indexOf(key);
                        if (i > -1) {
                            this.symptoms.splice(i, 1);
                        } else {
                            this.symptoms.push(key);
                        }
                    },

                    async start() {
                        this.saving = true;
                        try {
                            const payload = { start_date: this.date };
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
                            let data = {};
                            try {
                                data = await res.json();
                            } catch (err) {
                                /* body response bukan JSON (mis. 419/500) */
                            }
                            if (!res.ok) {
                                window.bloomToast.error(data.message || (res.status === 419 ? 'Sesi berakhir, muat ulang halaman' : 'Terjadi kesalahan'));
                                return;
                            }
                            window.dispatchEvent(new CustomEvent('bloom:close-start-period'));
                            window.bloomToast.success(data.message);
                            this.refresh();
                        } catch (err) {
                            window.bloomToast.error('Terjadi kesalahan jaringan, coba lagi');
                        } finally {
                            this.saving = false;
                        }
                    },

                    async refresh() {
                        if (window.BloomCalendar) {
                            try {
                                const res = await fetch(@json(route('calendar.events')), { headers: { 'Accept': 'application/json' } });
                                if (!res.ok) throw new Error('bad status');
                                const events = await res.json();
                                BloomCalendar.reRender(events);
                                window.dispatchEvent(new CustomEvent('bloom:calendar-events-loaded', { detail: { events } }));
                            } catch (err) {
                                window.location.reload();
                            }
                        } else {
                            window.location.reload();
                        }
                    },
                };
            }

            function periodDetailModal() {
                return {
                    loading: true,
                    saving: false,
                    editing: false,
                    form: { id: null, startDate: '', endDate: '', duration: 0, status: 'completed' },

                    init() {
                        window.addEventListener('bloom:period-detail-open', (e) => {
                            this.open(e.detail.id, e.detail.date);
                        });
                    },

                    async open(id, date) {
                        this.loading = true;
                        this.editing = false;
                        window.dispatchEvent(new CustomEvent('bloom:open-period-detail'));
                        try {
                            const res = await fetch(@json(route('periods.show', ['date' => '__DATE__'])).replace('__DATE__', date), {
                                headers: { 'Accept': 'application/json' }
                            });
                            if (!res.ok) throw new Error('bad status');
                            const data = await res.json();
                            const p = data.period;
                            this.form = {
                                id: p.id,
                                startDate: p.start_date,
                                endDate: p.end_date,
                                duration: p.duration,
                                status: p.status,
                            };
                        } catch (err) {
                            window.bloomToast.error('Gagal memuat detail menstruasi');
                        } finally {
                            this.loading = false;
                        }
                    },

                    get duration() {
                        if (!this.form.startDate) return 0;
                        const end = this.form.endDate || this.form.startDate;
                        const start = new Date(this.form.startDate + 'T00:00:00');
                        const e = new Date(end + 'T00:00:00');
                        if (e < start) return 0;
                        return Math.round((e - start) / 86400000) + 1;
                    },

                    startEdit() {
                        this.editing = true;
                    },

                    cancelEdit() {
                        this.editing = false;
                    },

                    async save() {
                        if (!this.form.startDate || !this.form.endDate) {
                            Swal.fire({ icon: 'warning', title: 'Pilih tanggal mulai dan tanggal selesai', confirmButtonColor: '#EC4899' });
                            return;
                        }
                        if (this.form.endDate < this.form.startDate) {
                            Swal.fire({ icon: 'warning', title: 'Tanggal selesai tidak boleh sebelum tanggal mulai', confirmButtonColor: '#EC4899' });
                            return;
                        }

                        this.saving = true;
                        try {
                            const res = await fetch(@json(route('periods.update', ['period' => '__ID__'])).replace('__ID__', this.form.id), {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({
                                    start_date: this.form.startDate,
                                    end_date: this.form.endDate,
                                }),
                            });
                            let data = {};
                            try {
                                data = await res.json();
                            } catch (err) {
                                /* body response bukan JSON (mis. 419/500) */
                            }
                            if (!res.ok) {
                                const msg = data.errors
                                    ? Object.values(data.errors).flat().join('\n')
                                    : (data.message || (res.status === 419 ? 'Sesi berakhir, muat ulang halaman' : 'Terjadi kesalahan'));
                                Swal.fire({ icon: 'error', title: msg, confirmButtonColor: '#EC4899' });
                                return;
                            }
                            window.bloomToast.success(data.message);
                            window.dispatchEvent(new CustomEvent('bloom:close-period-detail'));
                            this.refresh();
                        } catch (err) {
                            Swal.fire({ icon: 'error', title: 'Terjadi kesalahan jaringan, coba lagi', confirmButtonColor: '#EC4899' });
                        } finally {
                            this.saving = false;
                        }
                    },

                    remove() {
                        window.bloomToast.confirm({
                            title: 'Hapus menstruasi ini?',
                            text: 'Tindakan ini tidak dapat dibatalkan.',
                            confirmText: 'Ya, hapus',
                            onConfirm: async () => {
                                try {
                                    const res = await fetch(
                                        @json(route('periods.destroy', ['period' => '__ID__'])).replace('__ID__', this.form.id),
                                        {
                                            method: 'DELETE',
                                            headers: {
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                            },
                                        }
                                    );
                                    let data = {};
                                    try {
                                        data = await res.json();
                                    } catch (err) {
                                        /* body response bukan JSON (mis. 419/500) */
                                    }
                                    if (!res.ok) {
                                        window.bloomToast.error(data.message || (res.status === 419 ? 'Sesi berakhir, muat ulang halaman' : 'Gagal menghapus, coba lagi'));
                                        return;
                                    }
                                    window.bloomToast.success(data.message || 'Dihapus');
                                    window.dispatchEvent(new CustomEvent('bloom:close-period-detail'));
                                    this.refresh();
                                } catch (err) {
                                    window.bloomToast.error('Terjadi kesalahan jaringan, coba lagi');
                                }
                            },
                        });
                    },

                    async refresh() {
                        if (window.BloomCalendar) {
                            try {
                                const res = await fetch(@json(route('calendar.events')), { headers: { 'Accept': 'application/json' } });
                                if (!res.ok) throw new Error('bad status');
                                const events = await res.json();
                                BloomCalendar.reRender(events);
                                window.dispatchEvent(new CustomEvent('bloom:calendar-events-loaded', { detail: { events } }));
                            } catch (err) {
                                window.location.reload();
                            }
                        } else {
                            window.location.reload();
                        }
                    },
                };
            }

            function formatDateLabel(dateStr) {
                try {
                    return new Date(dateStr + 'T00:00:00').toLocaleDateString('id-ID', {
                        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
                    });
                } catch (err) {
                    return dateStr;
                }
            }

            document.addEventListener('DOMContentLoaded', async () => {
                const canEdit = @json($canEdit);
                const events = await fetch(@json(route('calendar.events')), {
                    headers: { 'Accept': 'application/json' }
                }).then(r => r.json());

                window.dispatchEvent(new CustomEvent('bloom:calendar-events-loaded', { detail: { events, canEdit } }));

                BloomCalendar.init({
                    events,
                    canEdit,
                    onDayClick: (dateStr) => {
                        if (!canEdit) {
                            window.bloomToast.success('Mode admin — kalender hanya untuk dilihat');
                            return;
                        }
                        const covered = events.find(e => e.extendedProps?.type === 'period' && dateStr >= e.start && dateStr < e.end);
                        if (covered) {
                            window.dispatchEvent(new CustomEvent('bloom:period-detail-open', {
                                detail: { id: covered.extendedProps.period_id, date: covered.extendedProps.date },
                            }));
                        } else {
                            window.dispatchEvent(new CustomEvent('bloom:start-period-open', { detail: { date: dateStr } }));
                        }
                    },
                    onEventClick: (info) => {
                        if (!canEdit) return;
                        const props = info.event.extendedProps || {};
                        if (props.type === 'period' || props.type === 'checkin') {
                            window.dispatchEvent(new CustomEvent('bloom:period-detail-open', {
                                detail: { id: props.period_id, date: props.date },
                            }));
                        }
                    },
                });
            });
        </script>
    @endpush

    <x-bloom.page-header
        icon="calendar"
        title="Kalender"
        :subtitle="$canEdit ? 'Ketuk tanggal hari pertama menstruasi untuk mulai mencatat siklus Anda' : 'Tampilan siklus dalam mode admin (hanya lihat)'"
    />

    <div class="w-full lg:mx-auto lg:max-w-4xl space-y-4" data-aos="fade-up">
        @if(! $hasData)
            <div class="glass-card px-6 py-8 text-center">
                <div class="mx-auto h-14 w-14 rounded-full bg-pink-100 dark:bg-gray-700 flex items-center justify-center">
                    <x-bloom.icon name="droplet" class="h-7 w-7 text-pink-500" />
                </div>
                <h2 class="mt-3 text-lg font-bold text-ink dark:text-gray-100">Belum ada data menstruasi.</h2>
                <p class="mt-1 text-sm text-gray-400">Ketuk tanggal hari pertama menstruasi untuk mulai mencatat siklus Anda.</p>
            </div>
        @endif

        <div class="glass-card p-3 sm:p-4">
            <div id="bloom-calendar"></div>
        </div>

        <div class="glass-card p-4 sm:p-5">
            <div class="flex items-center gap-2 mb-3">
                <x-bloom.icon name="info" class="h-4 w-4 text-pink-500" />
                <h2 class="text-sm font-semibold text-ink dark:text-gray-100">Keterangan Warna</h2>
            </div>
            <div class="grid grid-cols-2 gap-x-4 gap-y-3 sm:grid-cols-3">
                <div class="flex items-center gap-2.5">
                    <span class="h-3.5 w-3.5 shrink-0 rounded-md" style="background:#F43F5E"></span>
                    <span class="text-xs text-gray-500 dark:text-gray-300">Hari menstruasi</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="h-3 w-3 shrink-0 rounded-full" style="background:#10B981"></span>
                    <span class="text-xs text-gray-500 dark:text-gray-300">Sudah absen harian</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="h-3.5 w-3.5 shrink-0 rounded-md" style="background:#BFDBFE"></span>
                    <span class="text-xs text-gray-500 dark:text-gray-300">Masa subur</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="h-3 w-3 shrink-0 rounded-full" style="background:#7C3AED"></span>
                    <span class="text-xs text-gray-500 dark:text-gray-300">Ovulasi</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="h-3.5 w-3.5 shrink-0 rounded-md" style="background:#F9A8D4"></span>
                    <span class="text-xs text-gray-500 dark:text-gray-300">Prediksi menstruasi</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="h-3.5 w-3.5 shrink-0 rounded-md bg-pink-100 dark:bg-pink-500/15 border border-dashed border-pink-400"></span>
                    <span class="text-xs text-gray-500 dark:text-gray-300">Hari ini</span>
                </div>
            </div>
        </div>
    </div>

    @if($canEdit)
        {{-- Modal: mulai menstruasi --}}
        <x-bloom.modal id="start-period" title="Catat Menstruasi" maxWidth="md">
            <div x-data="startPeriodModal()" class="space-y-4">
                <div class="rounded-xl bg-pink-50 dark:bg-gray-800 px-4 py-3 flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-300">Tanggal</span>
                    <span class="text-sm font-bold text-pink-600 dark:text-pink-300" x-text="dateLabel"></span>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-300 text-center">
                    Apakah hari ini adalah hari pertama menstruasi Anda?
                </p>

                @include('partials._checkin-fields')

                <div class="grid grid-cols-2 gap-2 pt-1">
                    <button type="button" @click="$dispatch('bloom:close-start-period')" class="btn-ghost">
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="start()"
                        x-bind:disabled="saving"
                        class="btn-primary flex items-center justify-center gap-2"
                    >
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

        {{-- Modal: detail / edit --}}
        <x-bloom.modal id="period-detail" title="Detail Menstruasi" maxWidth="md">
            <div x-data="periodDetailModal()">
                <div x-show="loading" class="flex items-center justify-center py-10">
                    <span class="inline-block h-6 w-6 border-2 border-pink-400 border-t-transparent rounded-full animate-spin"></span>
                </div>

                <div x-show="!loading" x-cloak>
                    {{-- Mode lihat --}}
                    <div x-show="!editing" class="space-y-3">
                        <div class="grid grid-cols-3 gap-3">
                            <div class="rounded-2xl bg-rose-50 dark:bg-gray-800 p-3 text-center">
                                <p class="text-[10px] uppercase tracking-wider text-gray-400">Tanggal mulai</p>
                                <p class="mt-1 text-sm font-bold text-rose-600 dark:text-rose-300" x-text="formatShort(form.startDate)"></p>
                            </div>
                            <div class="rounded-2xl bg-rose-50 dark:bg-gray-800 p-3 text-center">
                                <p class="text-[10px] uppercase tracking-wider text-gray-400">Tanggal selesai</p>
                                <p class="mt-1 text-sm font-bold text-rose-600 dark:text-rose-300" x-text="formatShort(form.endDate)"></p>
                            </div>
                            <div class="rounded-2xl bg-rose-50 dark:bg-gray-800 p-3 text-center">
                                <p class="text-[10px] uppercase tracking-wider text-gray-400">Durasi</p>
                                <p class="mt-1 text-sm font-bold text-rose-600 dark:text-rose-300"><span x-text="form.duration"></span> hari</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-center">
                            <span
                                class="badge"
                                x-bind:class="form.status === 'ongoing' ? 'bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-300' : 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-300'"
                                x-text="form.status === 'ongoing' ? 'Berlangsung' : 'Selesai'"
                            ></span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <button type="button" @click="startEdit()" class="btn-primary">
                                <x-bloom.icon name="pencil" class="h-4 w-4" /> Edit
                            </button>
                            <button type="button" @click="remove()" class="btn-danger">
                                <x-bloom.icon name="trash" class="h-4 w-4" /> Hapus
                            </button>
                        </div>
                    </div>

                    {{-- Mode edit --}}
                    <div x-show="editing" x-cloak class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <x-input-label for="detail-start" :value="__('Tanggal Mulai')" />
                                <input id="detail-start" type="date" x-model="form.startDate" class="input-field mt-1.5">
                            </div>
                            <div>
                                <x-input-label for="detail-end" :value="__('Tanggal Selesai')" />
                                <input id="detail-end" type="date" x-model="form.endDate" class="input-field mt-1.5">
                            </div>
                        </div>

                        <div class="rounded-xl bg-pink-50 dark:bg-gray-800 px-4 py-3 flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-300">Durasi</span>
                            <span class="font-bold text-pink-600 dark:text-pink-300"><span x-text="duration"></span> hari</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <button type="button" @click="cancelEdit()" class="btn-ghost">Batal</button>
                            <button type="button" @click="save()" x-bind:disabled="saving" class="btn-primary flex items-center justify-center gap-2">
                                <template x-if="!saving">
                                    <span class="flex items-center gap-2"><x-bloom.icon name="check" class="h-4 w-4" /> Simpan</span>
                                </template>
                                <template x-if="saving">
                                    <span class="inline-block h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                </template>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </x-bloom.modal>

        <script>
            function formatShort(dateStr) {
                try {
                    return new Date(dateStr + 'T00:00:00').toLocaleDateString('id-ID', {
                        day: 'numeric', month: 'short', year: 'numeric',
                    });
                } catch (err) {
                    return dateStr;
                }
            }
        </script>
    @endif
</x-app-layout>
