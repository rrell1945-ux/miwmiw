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
    <title>@yield('title', 'Mimiw') — Mimiw</title>

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
</head>
<body class="min-h-screen font-sans antialiased">
    <div x-data="bloomApp" class="min-h-screen">
        {{ $slot }}
    </div>

    @stack('scripts')
    @vite(['resources/js/pwa.js'])
</body>
</html>
