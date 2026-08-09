<x-app-layout>
    @section('title', 'Pengaturan')

    <x-bloom.page-header icon="gear" title="Pengaturan" subtitle="Sesuaikan Mimiw dengan kebutuhan Anda" />

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2 lg:items-start">

        <div class="glass-card p-5" x-data="{ name: @js($user->name), saving: false }" data-aos="fade-up">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 rounded-2xl bg-pink-100 dark:bg-pink-500/20 flex items-center justify-center text-pink-500">
                    <x-bloom.icon name="heart" class="h-5 w-5" />
                </div>
                <div>
                    <h2 class="font-bold text-ink dark:text-gray-100">Profil</h2>
                    <p class="text-xs text-gray-400">Nama tampilan Anda</p>
                </div>
            </div>
            <x-input-label for="display-name" :value="__('Nama')" />
            <div class="flex gap-2 mt-1.5">
                <input id="display-name" type="text" x-model="name" class="input-field" placeholder="Sayang">
                <button
                    type="button"
                    class="btn-primary shrink-0"
                    x-bind:disabled="saving || !name.trim()"
                    @click="
                        saving = true;
                        fetch('{{ route('settings.profile') }}', {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            body: JSON.stringify({ name })
                        })
                        .then(r => r.json())
                        .then(d => { saving = false; d.message ? bloomToast.success(d.message) : bloomToast.error(d.message); })
                        .catch(() => { saving = false; bloomToast.error('Terjadi kesalahan'); });
                    "
                >
                    <span x-text="saving ? 'Menyimpan...' : 'Simpan'"></span>
                </button>
            </div>
        </div>

        <div class="glass-card p-5" x-data="{ saving: false }" data-aos="fade-up" data-aos-delay="50">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 rounded-2xl bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center text-purple-500">
                    <x-bloom.icon name="lock" class="h-5 w-5" />
                </div>
                <div>
                    <h2 class="font-bold text-ink dark:text-gray-100">Keamanan</h2>
                    <p class="text-xs text-gray-400">Ubah kata sandi pribadi Anda</p>
                </div>
            </div>
            <form @submit.prevent="
                saving = true;
                const form = new FormData($el);
                fetch('{{ route('settings.password') }}', {
                    method: 'PATCH',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: form
                })
                .then(r => r.json())
                .then(d => {
                    saving = false;
                    if (d.message) { bloomToast.success(d.message); $el.reset(); }
                    else { bloomToast.error(Object.values(d.errors || {}).flat().join('\n') || 'Kata sandi saat ini salah'); }
                })
                .catch(() => { saving = false; bloomToast.error('Terjadi kesalahan'); });
            " class="space-y-3">
                <div>
                    <x-input-label for="current_password" :value="__('Kata Sandi Saat Ini')" />
                    <input id="current_password" type="password" name="current_password" class="input-field mt-1.5" required autocomplete="current-password">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <x-input-label for="password" :value="__('Kata Sandi Baru')" />
                        <input id="password" type="password" name="password" class="input-field mt-1.5" required minlength="8" autocomplete="new-password">
                    </div>
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi')" />
                        <input id="password_confirmation" type="password" name="password_confirmation" class="input-field mt-1.5" required autocomplete="new-password">
                    </div>
                </div>
                <button type="submit" class="btn-primary w-full" x-bind:disabled="saving">
                    <span x-text="saving ? 'Memperbarui...' : 'Perbarui Kata Sandi'"></span>
                </button>
            </form>
        </div>

        <div class="glass-card p-5" x-data="themeSettings()" data-aos="fade-up" data-aos-delay="100">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 rounded-2xl bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center text-amber-500">
                    <x-bloom.icon name="moon" class="h-5 w-5" />
                </div>
                <div>
                    <h2 class="font-bold text-ink dark:text-gray-100">Tampilan</h2>
                    <p class="text-xs text-gray-400">Pilih tampilan Mimiw</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <button
                    type="button"
                    @click="setTheme('light')"
                    class="rounded-2xl border-2 p-4 flex flex-col items-center gap-2 transition-all duration-300"
                    :class="theme === 'light' ? 'border-pink-500 bg-pink-50 dark:bg-pink-500/10 shadow-soft' : 'border-gray-200 dark:border-gray-700'"
                >
                    <span :class="theme === 'light' ? 'text-pink-500' : 'text-gray-400'">
                        <x-bloom.icon name="sun" class="h-6 w-6" />
                    </span>
                    <span class="text-sm font-medium text-ink dark:text-gray-100">Terang</span>
                </button>
                <button
                    type="button"
                    @click="setTheme('dark')"
                    class="rounded-2xl border-2 p-4 flex flex-col items-center gap-2 transition-all duration-300"
                    :class="theme === 'dark' ? 'border-pink-500 bg-pink-50 dark:bg-pink-500/10 shadow-soft' : 'border-gray-200 dark:border-gray-700'"
                >
                    <span :class="theme === 'dark' ? 'text-pink-500' : 'text-gray-400'">
                        <x-bloom.icon name="moon" class="h-6 w-6" />
                    </span>
                    <span class="text-sm font-medium text-ink dark:text-gray-100">Gelap</span>
                </button>
            </div>
        </div>

        <div class="glass-card p-5" x-data="reminderSettings(@js($setting))" data-aos="fade-up" data-aos-delay="150">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 rounded-2xl bg-sky-100 dark:bg-sky-500/20 flex items-center justify-center text-sky-500">
                    <x-bloom.icon name="bell" class="h-5 w-5" />
                </div>
                <div>
                    <h2 class="font-bold text-ink dark:text-gray-100">Pengingat</h2>
                    <p class="text-xs text-gray-400">Pengingat lembut selama siklus Anda</p>
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-ink dark:text-gray-100">Notifikasi</p>
                        <p class="text-xs text-gray-400">Izinkan Mimiw mengirim notifikasi</p>
                    </div>
                    <button type="button" @click="form.notifications_enabled = !form.notifications_enabled; requestPermission();"
                        class="relative inline-flex h-7 w-12 shrink-0 items-center rounded-full transition-colors duration-300"
                        :class="form.notifications_enabled ? 'bg-pink-500' : 'bg-gray-300 dark:bg-gray-600'">
                        <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform duration-300"
                            :class="form.notifications_enabled ? 'translate-x-6' : 'translate-x-1'"></span>
                    </button>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-ink dark:text-gray-100">Pengingat Minum Air</p>
                        <p class="text-xs text-gray-400">Tetap terhidrasi setiap siklus</p>
                    </div>
                    <button type="button" @click="form.drink_water_reminder = !form.drink_water_reminder"
                        class="relative inline-flex h-7 w-12 shrink-0 items-center rounded-full transition-colors duration-300"
                        :class="form.drink_water_reminder ? 'bg-sky-500' : 'bg-gray-300 dark:bg-gray-600'">
                        <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform duration-300"
                            :class="form.drink_water_reminder ? 'translate-x-6' : 'translate-x-1'"></span>
                    </button>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-ink dark:text-gray-100">Pengingat Menstruasi</p>
                        <p class="text-xs text-gray-400">Ketahui kapan menstruasi akan tiba</p>
                    </div>
                    <button type="button" @click="form.period_reminder = !form.period_reminder"
                        class="relative inline-flex h-7 w-12 shrink-0 items-center rounded-full transition-colors duration-300"
                        :class="form.period_reminder ? 'bg-pink-500' : 'bg-gray-300 dark:bg-gray-600'">
                        <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform duration-300"
                            :class="form.period_reminder ? 'translate-x-6' : 'translate-x-1'"></span>
                    </button>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-ink dark:text-gray-100">Pengingat Siklus</p>
                        <p class="text-xs text-gray-400">Pengingat harian selama menstruasi</p>
                    </div>
                    <button type="button" @click="form.cycle_reminder = !form.cycle_reminder"
                        class="relative inline-flex h-7 w-12 shrink-0 items-center rounded-full transition-colors duration-300"
                        :class="form.cycle_reminder ? 'bg-purple-500' : 'bg-gray-300 dark:bg-gray-600'">
                        <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform duration-300"
                            :class="form.cycle_reminder ? 'translate-x-6' : 'translate-x-1'"></span>
                    </button>
                </div>

                <div>
                    <x-input-label for="water-interval" :value="__('Interval Pengingat Air')" />
                    <select id="water-interval" x-model="form.water_interval_minutes" class="input-field mt-1.5">
                        <option value="30">Setiap 30 menit</option>
                        <option value="45">Setiap 45 menit</option>
                        <option value="60">Setiap jam</option>
                        <option value="90">Setiap 1,5 jam</option>
                        <option value="120">Setiap 2 jam</option>
                    </select>
                </div>

                <button type="button" class="btn-secondary w-full" @click="save()" x-bind:disabled="saving">
                    <span x-text="saving ? 'Menyimpan...' : 'Simpan Pengingat'"></span>
                </button>
            </div>
        </div>

        <div class="glass-card p-5" data-aos="fade-up" data-aos-delay="200">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 rounded-2xl bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center text-emerald-500">
                    <x-bloom.icon name="download" class="h-5 w-5" />
                </div>
                <div>
                    <h2 class="font-bold text-ink dark:text-gray-100">Data & Cadangan</h2>
                    <p class="text-xs text-gray-400">Data Anda milik Anda — jaga tetap aman</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('backup.download') }}" class="btn-secondary">
                    <x-bloom.icon name="download" class="h-4 w-4" /> Cadangkan
                </a>
                <a href="{{ route('export.pdf') }}" class="btn-secondary">
                    <x-bloom.icon name="info" class="h-4 w-4" /> Ekspor PDF
                </a>
                <a href="{{ route('export.excel') }}" class="btn-secondary">
                    <x-bloom.icon name="chart" class="h-4 w-4" /> Ekspor Excel
                </a>
                <label class="btn-secondary cursor-pointer">
                    <x-bloom.icon name="upload" class="h-4 w-4" /> Pulihkan
                    <input type="file" accept=".json,application/json" class="hidden" id="backup-file-input">
                </label>
            </div>
            <p class="text-xs text-gray-400 mt-3">Cadangan menghasilkan file JSON. Memulihkan akan mengganti data saat ini dengan cadangan.</p>
        </div>

        <div class="glass-card p-5" x-data="{ installing: false }" data-aos="fade-up" data-aos-delay="250">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 rounded-2xl bg-rose-100 dark:bg-rose-500/20 flex items-center justify-center text-rose-500">
                    <x-bloom.icon name="phone" class="h-5 w-5" />
                </div>
                <div>
                    <h2 class="font-bold text-ink dark:text-gray-100">Pasang Mimiw</h2>
                    <p class="text-xs text-gray-400">Tambahkan Mimiw ke layar utama seperti aplikasi</p>
                </div>
            </div>
            <button type="button" class="btn-primary w-full" @click="
                if (BloomPWA.promptInstall()) { bloomToast.success('Memasang Mimiw...'); }
                else { Swal.fire({ icon: 'info', title: 'Gunakan menu browser', text: 'Ketuk ikon menu/berbagi dan pilih Tambahkan ke Layar Utama.', confirmButtonColor: '#EC4899' }); }
            ">
                <x-bloom.icon name="plus" class="h-4 w-4" /> Tambahkan ke Layar Utama
            </button>
        </div>
    </div>

    @push('scripts')
        <script>
            function themeSettings() {
                return {
                    theme: @json(auth()->user()->setting()->theme === 'dark' ? 'dark' : 'light'),
                    setTheme(value) {
                        this.theme = value;
                        localStorage.setItem('mimiw-theme', value);
                        window.dispatchEvent(new CustomEvent('bloom:theme-changed', { detail: { theme: value } }));
                        fetch('{{ route('settings.theme') }}', {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            body: JSON.stringify({ theme: value }),
                        }).then(d => d.json()).then(d => bloomToast.success(d.message));
                    },
                };
            }

            function reminderSettings(setting) {
                return {
                    saving: false,
                    form: {
                        notifications_enabled: !!setting.notifications_enabled,
                        drink_water_reminder: !!setting.drink_water_reminder,
                        period_reminder: !!setting.period_reminder,
                        cycle_reminder: !!setting.cycle_reminder,
                        water_interval_minutes: setting.water_interval_minutes || '60',
                    },
                    async requestPermission() {
                        if (this.form.notifications_enabled) {
                            await BloomPWA.requestPermission();
                        }
                    },
                    save() {
                        this.saving = true;
                        fetch('{{ route('settings.reminders') }}', {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            body: JSON.stringify(this.form),
                        })
                        .then(r => r.json())
                        .then(d => { this.saving = false; d.message ? bloomToast.success(d.message) : bloomToast.error(d.message); })
                        .catch(() => { this.saving = false; bloomToast.error('Terjadi kesalahan'); });
                    },
                };
            }

            document.getElementById('backup-file-input').addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;
                const form = new FormData();
                form.append('backup_file', file);
                fetch('{{ route('backup.restore') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: form,
                })
                .then(r => r.json())
                .then(d => {
                    d.message ? bloomToast.success(d.message) : bloomToast.error(d.message || 'Tidak dapat memulihkan cadangan');
                    this.value = '';
                })
                .catch(() => bloomToast.error('Tidak dapat memulihkan cadangan'));
            });
        </script>
    @endpush
</x-app-layout>
