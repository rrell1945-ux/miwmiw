<x-guest-layout>
    @push('scripts')
        <script>
            (function () {
                var shell = document.getElementById('login-shell');
                var isTouch = window.matchMedia && window.matchMedia('(pointer: coarse)').matches;

                if (isTouch && shell) {
                    function syncKb() {
                        var a = document.activeElement;
                        var isInput = a && (a.tagName === 'INPUT' || a.tagName === 'TEXTAREA');
                        shell.classList.toggle('kb-open', !!isInput);
                    }
                    document.addEventListener('focusin', syncKb);
                    document.addEventListener('focusout', syncKb);
                }

                var toggle = document.getElementById('toggle-password');
                if (toggle) {
                    toggle.addEventListener('click', function () {
                        var input = document.getElementById('password');
                        var show = input.type === 'password';
                        input.type = show ? 'text' : 'password';
                        this.querySelector('.eye-on').classList.toggle('hidden', show);
                        this.querySelector('.eye-off').classList.toggle('hidden', !show);
                    });
                }

                var form = document.getElementById('mimiw-login-form');
                if (form) {
                    form.addEventListener('submit', function () {
                        var btn = document.getElementById('mimiw-login-submit');
                        if (btn && !btn.disabled) {
                            btn.disabled = true;
                            btn.innerHTML =
                                '<svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">' +
                                    '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>' +
                                    '<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>' +
                                '</svg>' +
                                '<span>Memproses...</span>';
                        }
                    });
                }

                @if($errors->any())
                    document.addEventListener('DOMContentLoaded', function () {
                        Swal.fire({
                            icon: 'error',
                            title: @json($errors->first()),
                            showConfirmButton: true,
                            confirmButtonText: 'Coba Lagi',
                            confirmButtonColor: '#EC4899',
                        });
                    });
                @endif
            })();
        </script>
    @endpush

    <div id="login-page" class="relative min-h-dvh w-full overflow-hidden bg-[#FAFAF9] font-sans dark:bg-gray-900">
        <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
            <div class="login-orb login-orb-a"></div>
            <div class="login-orb login-orb-b"></div>
            <div class="login-orb login-orb-c"></div>
        </div>
        <section id="login-shell" class="login-shell relative flex min-h-dvh w-full flex-col items-center justify-center px-4 py-10 sm:px-8">
            <div class="w-full max-w-md" x-data="accountPicker()">
                <div class="flex flex-col items-center text-center">
                    <div class="login-card-d2">
                        <div class="login-logo-float">
                            <div class="login-logo-hover">
                                <x-bloom.logo class="h-14 w-14" />
                            </div>
                        </div>
                    </div>
                    <h1 class="login-card-d1 mt-4 text-2xl font-bold tracking-tight text-ink dark:text-gray-100">Mimiw</h1>
                    <p class="login-card-d2 mt-1 text-sm text-gray-500 dark:text-gray-400">Pelacak Siklus untuk Berdua</p>
                </div>

                <div class="login-card mt-8">
                    <h2 class="text-xl font-bold text-ink dark:text-gray-100">Selamat datang kembali</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilih akun, lalu masukkan kata sandi Anda.</p>

                    <div class="mt-6 space-y-3">
                        <template x-for="account in accounts" :key="account.email">
                            <button
                                type="button"
                                @click="select(account)"
                                class="flex w-full items-center gap-4 rounded-2xl border p-4 text-left transition-all duration-200"
                                :class="email === account.email
                                    ? 'scale-[1.01] border-pink-500 bg-pink-50 shadow-soft dark:border-pink-400/60 dark:bg-pink-500/10'
                                    : 'border-gray-200 dark:border-gray-700 hover:-translate-y-0.5 hover:border-pink-200 hover:bg-gray-50 hover:shadow-soft dark:hover:border-gray-600 dark:hover:bg-gray-800'"
                            >
                                <span
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-base font-semibold text-white"
                                    :class="account.role === 'admin' ? 'bg-gray-600' : 'bg-pink-500'"
                                    x-text="account.initials"
                                ></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-semibold text-ink dark:text-gray-100" x-text="account.name"></span>
                                    <span class="mt-0.5 block text-xs text-gray-400" x-text="account.role === 'admin' ? 'Admin · Penjaga' : 'Pemilik Siklus'"></span>
                                </span>
                                <span
                                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full transition-all duration-200"
                                    :class="email === account.email ? 'scale-105 bg-pink-500 text-white' : 'border border-gray-300 dark:border-gray-600 text-transparent'"
                                >
                                    <x-bloom.icon name="check" class="h-3.5 w-3.5" />
                                </span>
                            </button>
                        </template>
                    </div>

                    <form method="POST" action="{{ route('login') }}" class="mt-6" id="mimiw-login-form">
                        @csrf
                        <input type="hidden" name="email" x-model="email">

                        <div>
                            <x-input-label for="password" :value="__('Kata Sandi')" class="!text-ink dark:!text-gray-200" />
                            <div class="relative mt-1.5">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                                    <x-bloom.icon name="lock" class="h-5 w-5" />
                                </span>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    autocomplete="current-password"
                                    inputmode="text"
                                    placeholder="Masukkan kata sandi"
                                    class="input-field py-3.5 pl-12 pr-12 text-[16px]"
                                />
                                <button
                                    type="button"
                                    id="toggle-password"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 transition-colors hover:text-pink-600"
                                    aria-label="Tampilkan atau sembunyikan kata sandi"
                                >
                                    <x-bloom.icon name="eye" class="eye-on h-5 w-5" />
                                    <x-bloom.icon name="eye-off" class="eye-off hidden h-5 w-5" />
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            <p class="mt-2 text-xs text-gray-400" x-show="!email" x-transition.opacity.duration.300ms>
                                Pilih salah satu akun terlebih dahulu.
                            </p>
                        </div>

                        <button
                            type="submit"
                            id="mimiw-login-submit"
                            class="btn-primary login-submit mt-6 w-full py-3.5"
                            x-bind:disabled="!email"
                        >
                            <span>Masuk</span>
                        </button>
                    </form>
                </div>

                <p class="login-card-d3 mt-6 text-center text-xs text-gray-400">
                    Hanya untuk Salma &amp; Farel — data Anda tetap pribadi.
                </p>
            </div>
        </section>
    </div>

    <script>
        function accountPicker() {
            return {
                accounts: @json($accounts),
                email: '',
                select(account) {
                    this.email = this.email === account.email ? '' : account.email;
                },
            };
        }
    </script>
</x-guest-layout>
