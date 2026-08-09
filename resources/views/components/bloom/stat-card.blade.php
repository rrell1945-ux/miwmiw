@props([
    'icon' => 'heart',
    'label' => '',
    'value' => '—',
    'sub' => null,
    'gradient' => '',
    'delay' => '0',
])

@php
$tones = [
    'calendar' => 'bg-pink-50 text-pink-500 dark:bg-pink-500/10 dark:text-pink-300',
    'sparkles' => 'bg-purple-50 text-purple-500 dark:bg-purple-500/10 dark:text-purple-300',
    'clock' => 'bg-sky-50 text-sky-500 dark:bg-sky-500/10 dark:text-sky-300',
    'flame' => 'bg-amber-50 text-amber-500 dark:bg-amber-500/10 dark:text-amber-300',
    'heart' => 'bg-rose-50 text-rose-500 dark:bg-rose-500/10 dark:text-rose-300',
    'chart' => 'bg-emerald-50 text-emerald-500 dark:bg-emerald-500/10 dark:text-emerald-300',
];
$tone = $tones[$icon] ?? 'bg-pink-50 text-pink-500 dark:bg-pink-500/10 dark:text-pink-300';
@endphp

<div class="stat-card" data-aos="fade-up" data-aos-delay="{{ $delay }}">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ $label }}</p>
            <p
                x-data="bloomCountUp(@js($value))"
                x-text="display"
                class="mt-1 truncate text-xl font-bold text-ink sm:text-2xl dark:text-gray-100"
            >{{ $value }}</p>
            @if($sub)
                <p class="mt-0.5 text-xs text-gray-400">{{ $sub }}</p>
            @endif
        </div>
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $tone }} transition-transform duration-200 group-hover:scale-110">
            <x-bloom.icon :name="$icon" class="h-5 w-5" />
        </div>
    </div>
</div>
