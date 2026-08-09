<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#FAFAF9">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Mimiw - Pelacak Siklus Pribadi">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Mimiw">
    <title>@yield('title', 'Beranda') — Mimiw</title>

    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/icons/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    @php
        $preferredTheme = auth()->check()
            ? (auth()->user()->setting()->theme ?: 'light')
            : 'light';
    @endphp
    <script>
        (function () {
            var serverTheme = @json($preferredTheme);
            var theme = localStorage.getItem('mimiw-theme');
            if (theme !== 'light' && theme !== 'dark') theme = serverTheme;
            if (!localStorage.getItem('mimiw-theme')) localStorage.setItem('mimiw-theme', theme);
            document.documentElement.classList.toggle('dark', theme === 'dark');
            document.documentElement.setAttribute('data-color-scheme', theme);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen font-sans antialiased">
    <div x-data="bloomApp" class="relative min-h-screen">
        @php
            $nav = [
                ['route' => 'dashboard', 'label' => 'Beranda', 'icon' => 'home'],
                ['route' => 'calendar.index', 'label' => 'Kalender', 'icon' => 'calendar'],
                ['route' => 'history.index', 'label' => 'Riwayat', 'icon' => 'clock'],
                ['route' => 'messages.index', 'label' => 'Pesan', 'icon' => 'chat'],
                ['route' => 'statistics.index', 'label' => 'Statistik', 'icon' => 'chart'],
                ['route' => 'settings.index', 'label' => 'Pengaturan', 'icon' => 'gear'],
            ];

            $unreadMessages = auth()->check() && ! auth()->user()->isAdmin()
                ? auth()->user()->receivedMessages()->whereNull('read_at')->count()
                : 0;
        @endphp

        <aside class="fixed inset-y-0 left-0 z-40 hidden w-60 flex-col border-r border-gray-200/80 bg-white px-4 py-6 dark:border-gray-800 dark:bg-gray-900 lg:flex">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-2">
                <x-bloom.logo class="h-10 w-10" />
                <div class="leading-tight">
                    <p class="text-base font-bold text-ink dark:text-gray-100">Mimiw</p>
                    <p class="text-[11px] text-gray-400">Pelacak Siklus Pribadi</p>
                </div>
            </a>

            <nav class="mt-8 flex flex-1 flex-col gap-1">
                @foreach ($nav as $item)
                    @php $active = request()->routeIs($item['route'].'*'); @endphp
                    <a
                        href="{{ route($item['route']) }}"
                        class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors duration-150 {{ $active ? 'bg-pink-50 text-pink-600 dark:bg-pink-500/10 dark:text-pink-300' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100' }}"
                        @if($active) aria-current="page" @endif
                    >
                        <x-bloom.icon :name="$item['icon']" class="h-5 w-5" />
                        <span>{{ $item['label'] }}</span>
                        @if($item['route'] === 'messages.index' && $unreadMessages > 0)
                            <span class="ml-auto inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-pink-500 px-1.5 text-[10px] font-bold text-white">{{ $unreadMessages }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="space-y-1 border-t border-gray-100 pt-4 dark:border-gray-800">
                <button
                    type="button"
                    @click="toggleTheme()"
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-500 transition-colors duration-150 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100"
                    title="Ganti tema"
                >
                    <x-bloom.icon name="moon" x-show="theme === 'light'" class="h-5 w-5" />
                    <x-bloom.icon name="sun" x-show="theme === 'dark'" class="h-5 w-5" />
                    <span x-text="theme === 'light' ? 'Mode Gelap' : 'Mode Terang'"></span>
                </button>

                <a
                    href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-500 transition-colors duration-150 hover:bg-rose-50 hover:text-rose-600 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-rose-400"
                >
                    <x-bloom.icon name="logout" class="h-5 w-5" /> Keluar
                </a>
            </div>

            <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                @csrf
            </form>
        </aside>

        <div class="lg:pl-60">
            <header class="sticky top-0 z-30 lg:hidden">
                <div class="mx-auto max-w-lg px-4 pt-4">
                    <div class="glass-panel flex items-center justify-between rounded-2xl px-4 py-3">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                            <x-bloom.logo class="h-9 w-9" />
                            <div class="leading-tight">
                                <p class="text-base font-bold text-ink dark:text-gray-100">Mimiw</p>
                                <p class="-mt-0.5 text-[10px] text-gray-400">Pelacak Siklus Pribadi</p>
                            </div>
                        </a>

                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                @click="toggleTheme()"
                                class="rounded-xl p-2.5 text-gray-500 transition-colors duration-150 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                                title="Ganti tema"
                                aria-label="Ganti tema"
                            >
                                <svg x-show="theme === 'light'" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
                                </svg>
                                <svg x-show="theme === 'dark'" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636m8.364-1.743L12 6.75 10.318 4.393A2.625 2.625 0 1012.75 2.05M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>

                            <a
                                href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                class="rounded-xl p-2.5 text-gray-500 transition-colors duration-150 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                                title="Sign out"
                                aria-label="Sign out"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <main class="page-enter mx-auto w-full max-w-lg px-4 pt-5 pb-28 lg:max-w-5xl lg:px-10 lg:py-10 lg:pb-16">
                {{ $slot }}
            </main>
        </div>

        <nav class="fixed inset-x-0 bottom-0 z-30 pb-safe lg:hidden" aria-label="main navigation">
            <div class="mx-auto max-w-lg px-4 pb-3">
                <div class="flex items-center justify-around rounded-2xl border border-gray-200/70 bg-white px-2 py-1.5 shadow-card dark:border-gray-700/60 dark:bg-gray-800/90">
                    @foreach ($nav as $item)
                        @php $active = request()->routeIs($item['route'].'*'); @endphp
                        <a
                            href="{{ route($item['route']) }}"
                            class="relative flex min-w-0 flex-1 flex-col items-center gap-0.5 rounded-xl px-1 py-1.5 transition-colors duration-150 {{ $active ? 'text-pink-600 dark:text-pink-300' : 'text-gray-400 hover:text-pink-500' }}"
                            @if($active) aria-current="page" @endif
                        >
                            <x-bloom.icon :name="$item['icon']" class="h-5 w-5" />
                            <span class="w-full max-w-full truncate text-center text-[10px] font-medium leading-none {{ $active ? 'text-pink-600 dark:text-pink-300' : '' }}">{{ $item['label'] }}</span>
                            @if($item['route'] === 'messages.index' && $unreadMessages > 0)
                                <span class="absolute -top-1 right-1 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-pink-500 px-1 text-[9px] font-bold text-white">{{ $unreadMessages }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </nav>
    </div>

    @stack('scripts')
    @vite(['resources/js/pwa.js'])
</body>
</html>
