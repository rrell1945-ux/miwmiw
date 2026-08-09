<?php

namespace App\Services;

use App\Models\Period;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class StatisticsService
{
    public function __construct(protected User $user)
    {
    }

    /**
     * Aggregate every statistic shown on the statistics page.
     *
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $periods = $this->user->periods()
            ->orderBy('start_date')
            ->get();

        $lengths = $periods
            ->filter(fn (Period $p) => $p->cycle_length !== null)
            ->map(fn (Period $p) => (int) $p->cycle_length)
            ->values();

        $durations = $periods->map(fn (Period $p) => (int) $p->duration)->values();

        return [
            'count' => $periods->count(),
            'average_cycle' => $lengths->isNotEmpty() ? round($lengths->average(), 1) : null,
            'average_duration' => $durations->isNotEmpty() ? round($durations->average(), 1) : null,
            'longest_cycle' => $lengths->max(),
            'shortest_cycle' => $lengths->min(),
            'cycle_series' => $this->seriesFor($periods, 'cycle'),
            'duration_series' => $this->seriesFor($periods, 'duration'),
            'mood_counts' => $this->moodCounts($periods),
            'mood_series' => $this->moodSeries(),
            'flow_counts' => $this->flowCounts($periods),
            'monthly_totals' => $this->monthlyTotals($periods),
        ];
    }

    protected function seriesFor(Collection $periods, string $type): array
    {
        return $periods
            ->sortBy('start_date')
            ->filter(fn (Period $p) => $type === 'cycle'
                ? $p->cycle_length !== null
                : true)
            ->values()
            ->map(fn (Period $p) => [
                'x' => CarbonImmutable::parse($p->start_date)->format('M Y'),
                'y' => (int) ($type === 'cycle' ? $p->cycle_length : $p->duration),
            ])
            ->values()
            ->all();
    }

    protected function moodCounts(Collection $periods): array
    {
        $byKey = $periods
            ->filter(fn (Period $p) => $p->mood)
            ->groupBy('mood')
            ->map->count();

        $moods = \App\Models\Mood::query()->orderBy('sort_order')->get();

        return $moods->map(fn ($mood) => [
            'label' => $mood->emoji.' '.$mood->label,
            'value' => (int) ($byKey[$mood->key] ?? 0),
            'color' => $mood->color,
        ])->values()->all();
    }

    protected function moodSeries(): array
    {
        return $this->user->periods()
            ->whereNotNull('mood')
            ->orderBy('start_date')
            ->get()
            ->map(fn (Period $p) => [
                'x' => CarbonImmutable::parse($p->start_date)->format('d M Y'),
                'y' => \App\Models\Mood::query()->where('key', $p->mood)->value('sort_order') ?? 0,
                'mood' => $p->mood,
            ])
            ->values()
            ->all();
    }

    protected function flowCounts(Collection $periods): array
    {
        $byFlow = $periods->filter(fn (Period $p) => $p->flow)->groupBy('flow')->map->count();

        return collect(Period::FLOWS)->map(fn ($label, $key) => [
            'label' => $label,
            'value' => (int) ($byFlow[$key] ?? 0),
        ])->values()->all();
    }

    protected function monthlyTotals(Collection $periods): array
    {
        return $periods
            ->groupBy(fn (Period $p) => CarbonImmutable::parse($p->start_date)->format('M Y'))
            ->map->count()
            ->sortKeys()
            ->map(fn (int $count, string $month) => [
                'x' => $month,
                'y' => $count,
            ])
            ->values()
            ->all();
    }
}
