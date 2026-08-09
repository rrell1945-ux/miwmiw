@props([
    'icon' => 'heart',
    'title' => 'Belum ada data',
    'message' => 'Data yang Anda tambahkan akan muncul di sini.',
    'action' => null,
])

<div class="glass-card p-8 text-center" data-aos="fade-up">
    <div class="mx-auto h-16 w-16 rounded-full bg-pink-100 dark:bg-gray-700 flex items-center justify-center">
        <x-bloom.icon :name="$icon" class="h-8 w-8 text-pink-500 dark:text-pink-300" />
    </div>
    <h3 class="mt-4 font-semibold text-ink dark:text-gray-100">{{ $title }}</h3>
    <p class="mt-1 text-sm text-gray-400">{{ $message }}</p>
    @if($action)
        <div class="mt-5">
            {{ $action }}
        </div>
    @endif
</div>
