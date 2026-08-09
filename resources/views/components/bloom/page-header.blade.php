@props([
    'icon' => 'heart',
    'title' => '',
    'subtitle' => null,
    'actions' => null,
])

<div class="mb-6 flex items-center justify-between gap-3" data-aos="fade-up">
    <div class="flex min-w-0 items-center gap-3">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-pink-50 text-pink-500 dark:bg-pink-500/10 dark:text-pink-300 sm:h-12 sm:w-12">
            <x-bloom.icon :name="$icon" class="h-5 w-5 sm:h-6 sm:w-6" />
        </div>
        <div class="min-w-0">
            <h1 class="truncate text-lg font-bold text-ink sm:text-2xl dark:text-gray-100">{{ $title }}</h1>
            @if($subtitle)
                <p class="truncate text-xs text-gray-400 sm:text-sm">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    @if($actions)
        <div class="flex shrink-0 items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
