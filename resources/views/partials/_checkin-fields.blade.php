<div>
    <x-input-label value="Volume (opsional)" />
    <div class="grid grid-cols-3 gap-2 mt-1.5">
        @foreach ($flows as $key => $label)
            <button
                type="button"
                @click="flow = flow === '{{ $key }}' ? null : '{{ $key }}'"
                class="rounded-xl border px-3 py-2.5 text-sm font-medium transition-all duration-200"
                :class="flow === '{{ $key }}'
                    ? 'border-pink-500 bg-pink-500 text-white'
                    : 'border-gray-200 bg-white text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300'"
            >{{ $label }}</button>
        @endforeach
    </div>
</div>

<div>
    <x-input-label value="Suasana Hati (opsional)" />
    <div class="grid grid-cols-3 gap-2 mt-1.5">
        @foreach ($moods as $mood)
            <button
                type="button"
                @click="mood = mood === '{{ $mood->key }}' ? null : '{{ $mood->key }}'"
                class="flex flex-col items-center gap-0.5 rounded-xl border px-2 py-2.5 text-sm font-medium transition-all duration-200"
                :class="mood === '{{ $mood->key }}'
                    ? 'border-pink-500 bg-pink-500 text-white scale-[1.03]'
                    : 'border-gray-200 bg-white text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300'"
            >
                <span class="text-xl">{{ $mood->emoji }}</span>
                <span>{{ $mood->label }}</span>
            </button>
        @endforeach
    </div>
</div>

<div>
    <x-input-label value="Gejala (opsional)" />
    <div class="flex flex-wrap gap-2 mt-1.5">
        @foreach (\App\Models\Period::SYMPTOMS as $key => $label)
            <button
                type="button"
                @click="toggleSymptom('{{ $key }}')"
                class="rounded-full border px-3.5 py-1.5 text-xs font-medium transition-all duration-200"
                :class="symptoms.includes('{{ $key }}')
                    ? 'border-rose-500 bg-rose-500 text-white'
                    : 'border-gray-200 bg-white text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300'"
            >{{ $label }}</button>
        @endforeach
    </div>
</div>

<div>
    <x-input-label value="Catatan (opsional)" />
    <textarea x-model="note" rows="2" class="input-field mt-1.5 resize-none" placeholder="Tulis apa pun yang ingin diingat..."></textarea>
</div>
