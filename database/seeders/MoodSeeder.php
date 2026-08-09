<?php

namespace Database\Seeders;

use App\Models\Mood;
use Illuminate\Database\Seeder;

class MoodSeeder extends Seeder
{
    public function run(): void
    {
        $moods = [
            ['key' => 'happy', 'label' => 'Bahagia', 'emoji' => '😊', 'color' => '#F59E0B', 'sort_order' => 1],
            ['key' => 'tired', 'label' => 'Lelah', 'emoji' => '😴', 'color' => '#9CA3AF', 'sort_order' => 2],
            ['key' => 'sad', 'label' => 'Sedih', 'emoji' => '😢', 'color' => '#60A5FA', 'sort_order' => 3],
            ['key' => 'angry', 'label' => 'Marah', 'emoji' => '😡', 'color' => '#EF4444', 'sort_order' => 4],
            ['key' => 'loved', 'label' => 'Sayang', 'emoji' => '🥰', 'color' => '#EC4899', 'sort_order' => 5],
            ['key' => 'cramps', 'label' => 'Kram', 'emoji' => '😣', 'color' => '#8B5CF6', 'sort_order' => 6],
        ];

        foreach ($moods as $mood) {
            Mood::query()->updateOrCreate(['key' => $mood['key']], $mood);
        }
    }
}
