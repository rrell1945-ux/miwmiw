@props([
    'id' => '',
    'title' => '',
    'maxWidth' => 'lg',
])

@php
$widths = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
];
$width = $widths[$maxWidth] ?? $widths['lg'];
@endphp

<div
    x-data="{ open: false }"
    x-cloak
    @keydown.escape.window="open = false"
    x-show="open"
    x-init="
        window.addEventListener('bloom:open-{{ $id }}', () => { open = true; });
        window.addEventListener('bloom:close-{{ $id }}', () => { open = false; });
    "
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
>
    <div class="flex min-h-full items-end justify-center p-0 sm:items-center sm:p-4">
        <div
            class="fixed inset-0 bg-gray-900/50 transition-opacity dark:bg-black/60"
            x-show="open"
            x-transition.opacity
            @click="open = false"
        ></div>

        <div
            class="relative z-10 w-full {{ $width }} rounded-t-3xl border border-gray-200/70 bg-white shadow-card dark:border-gray-700/60 dark:bg-gray-800 transition-all sm:rounded-3xl"
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-3 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-6"
        >
            <div class="flex items-center justify-between px-5 pb-3 pt-5">
                <h2 class="text-lg font-bold text-ink dark:text-gray-100">{{ $title }}</h2>
                <button type="button" class="rounded-xl p-2 text-gray-400 transition-colors hover:bg-gray-100 dark:hover:bg-gray-700" @click="open = false" aria-label="Tutup">
                    <x-bloom.icon name="x" class="h-5 w-5" />
                </button>
            </div>

            <div class="max-h-[75vh] overflow-y-auto px-5 pb-6">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
