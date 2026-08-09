<div x-data="periodForm()" class="space-y-4">
    <div class="text-center">
        <p class="text-sm font-semibold text-pink-500 dark:text-pink-300" x-text="form.dateLabel"></p>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <x-input-label for="period-start" :value="__('Tanggal Mulai')" />
            <input x-model="form.startDate" type="date" id="period-start" class="input-field mt-1.5">
        </div>
        <div>
            <x-input-label for="period-end" :value="__('Tanggal Selesai')" />
            <input x-model="form.endDate" type="date" id="period-end" class="input-field mt-1.5">
        </div>
    </div>

    <div class="rounded-xl bg-pink-50 dark:bg-gray-800 px-4 py-3 flex items-center justify-between">
        <span class="text-sm text-gray-500 dark:text-gray-300">Durasi</span>
        <span class="font-bold text-pink-600 dark:text-pink-300" x-text="form.duration + ' hari'"></span>
    </div>

    <div>
        <x-input-label value="Volume" />
        <div class="grid grid-cols-3 gap-2 mt-1.5">
            <template x-for="(label, key) in options.flows" :key="key">
                <button
                    type="button"
                    @click="form.flow = form.flow === key ? null : key"
                    class="rounded-xl border px-3 py-2.5 text-sm font-medium transition-all duration-200"
                    :class="form.flow === key
                        ? 'border-pink-500 bg-pink-500 text-white'
                        : 'border-gray-200 bg-white text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300'"
                    x-text="label"
                ></button>
            </template>
        </div>
    </div>

    <div>
        <x-input-label value="Suasana Hati" />
        <div class="grid grid-cols-3 gap-2 mt-1.5">
            <template x-for="mood in options.moods" :key="mood.key">
                <button
                    type="button"
                    @click="form.mood = form.mood === mood.key ? null : mood.key"
                    class="flex flex-col items-center gap-0.5 rounded-xl border px-2 py-2.5 text-sm font-medium transition-all duration-200"
                    :class="form.mood === mood.key
                        ? 'border-pink-500 bg-pink-500 text-white scale-[1.03]'
                        : 'border-gray-200 bg-white text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300'"
                >
                    <span class="text-xl" x-text="mood.emoji"></span>
                    <span x-text="mood.label"></span>
                </button>
            </template>
        </div>
    </div>

    <div>
        <x-input-label value="Gejala" />
        <div class="flex flex-wrap gap-2 mt-1.5">
            <template x-for="(label, key) in options.symptoms" :key="key">
                <button
                    type="button"
                    @click="toggleSymptom(key)"
                    class="rounded-full border px-3.5 py-1.5 text-xs font-medium transition-all duration-200"
                    :class="form.symptoms.includes(key)
                        ? 'border-rose-500 bg-rose-500 text-white'
                        : 'border-gray-200 bg-white text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300'"
                    x-text="label"
                ></button>
            </template>
        </div>
    </div>

    <div>
        <x-input-label for="period-notes" :value="__('Catatan')" />
        <textarea
            x-model="form.notes"
            id="period-notes"
            rows="3"
            class="input-field mt-1.5 resize-none"
            placeholder="Bagaimana perasaan Anda? Tulis apa pun yang ingin Anda ingat..."
        ></textarea>
    </div>

    <div class="flex items-center gap-2 pt-1">
        <template x-if="form.id">
            <button type="button" @click="remove()" class="btn-danger flex-1">
                <x-bloom.icon name="trash" class="h-4 w-4" /> Hapus
            </button>
        </template>
        <button type="button" @click="save()" class="btn-primary flex-1" x-bind:disabled="saving">
            <template x-if="!saving">
                <span class="flex items-center gap-2"><x-bloom.icon name="check" class="h-4 w-4" /> <span x-text="form.id ? 'Simpan' : 'Simpan'"></span></span>
            </template>
            <template x-if="saving">
                <span class="inline-block h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            </template>
        </button>
    </div>
</div>

<script>
    function periodForm() {
        return {
            saving: false,
            form: {
                id: null,
                dateLabel: '',
                startDate: '',
                endDate: '',
                flow: null,
                mood: null,
                symptoms: [],
                notes: '',
            },
            options: {
                flows: {},
                moods: [],
                symptoms: {},
            },

            init() {
                window.addEventListener('bloom:period-modal-open', (e) => {
                    this.open(e.detail.date);
                });
            },

            get duration() {
                if (!this.form.startDate) return 0;
                const end = this.form.endDate || this.form.startDate;
                const start = new Date(this.form.startDate + 'T00:00:00');
                const e = new Date(end + 'T00:00:00');
                if (e < start) return 0;
                return Math.round((e - start) / 86400000) + 1;
            },

            async open(date) {
                this.saving = false;
                this.reset();
                try {
                    const res = await fetch(@json(route('periods.show', ['date' => '__DATE__'])).replace('__DATE__', date), {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!res.ok) throw new Error('bad status');
                    const data = await res.json();
                    this.options = data.options;
                    this.form.dateLabel = data.date_label;
                    this.form.startDate = data.period?.start_date ?? date;
                    this.form.endDate = data.period?.end_date ?? date;
                    this.form.id = data.period?.id ?? null;
                    this.form.flow = data.period?.flow ?? null;
                    this.form.mood = data.period?.mood ?? null;
                    this.form.symptoms = data.period?.symptoms ?? [];
                    this.form.notes = data.period?.notes ?? '';
                } catch (err) {
                    window.bloomToast.error('Gagal memuat data');
                }
            },

            reset() {
                this.form = {
                    id: null, dateLabel: '', startDate: '', endDate: '',
                    flow: null, mood: null, symptoms: [], notes: '',
                };
            },

            toggleSymptom(key) {
                const index = this.form.symptoms.indexOf(key);
                if (index > -1) {
                    this.form.symptoms.splice(index, 1);
                } else {
                    this.form.symptoms.push(key);
                }
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
                const url = this.form.id
                    ? @json(route('periods.update', ['period' => '__ID__'])).replace('__ID__', this.form.id)
                    : @json(route('periods.store'));
                const method = this.form.id ? 'PUT' : 'POST';

                try {
                    const res = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            start_date: this.form.startDate,
                            end_date: this.form.endDate,
                            flow: this.form.flow,
                            mood: this.form.mood,
                            symptoms: this.form.symptoms,
                            notes: this.form.notes,
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
                    this.saving = false;
                    this.refreshCalendar();
                } catch (err) {
                    this.saving = false;
                    Swal.fire({ icon: 'error', title: 'Terjadi kesalahan jaringan, coba lagi', confirmButtonColor: '#EC4899' });
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
                            this.refreshCalendar();
                        } catch (err) {
                            window.bloomToast.error('Terjadi kesalahan jaringan, coba lagi');
                        }
                    },
                });
            },

            async refreshCalendar() {
                window.dispatchEvent(new CustomEvent('bloom:close-period-modal'));
                if (window.BloomCalendar) {
                    try {
                        const res = await fetch(@json(route('calendar.events')), { headers: { 'Accept': 'application/json' } });
                        if (!res.ok) throw new Error('bad status');
                        const events = await res.json();
                        BloomCalendar.reRender(events);
                    } catch (err) {
                        window.location.reload();
                    }
                } else {
                    window.location.reload();
                }
            },
        };
    }
</script>
