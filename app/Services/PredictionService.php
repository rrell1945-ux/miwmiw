<?php

namespace App\Services;

use App\Models\Period;
use App\Models\User;
use Carbon\CarbonImmutable;

class PredictionService
{
    public function __construct(protected User $user, protected CarbonImmutable $today = new CarbonImmutable)
    {
    }

    /**
     * Translate an internal phase key into an Indonesian display label.
     */
    public static function phaseLabel(string $phase): string
    {
        return match ($phase) {
            'Menstruation' => 'Menstruasi',
            'Fertile Window' => 'Masa Subur',
            'Ovulation' => 'Ovulasi',
            'Luteal Phase' => 'Fase Luteal',
            'Follicular Phase' => 'Fase Folikular',
            default => 'Belum ada data',
        };
    }

    /**
     * Build the full prediction payload used by the dashboard & calendar.
     *
     * Predictions are only produced once the user has finished at least one
     * period, so ongoing (unfinished) periods never contribute to forecasts.
     *
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $periods = $this->user->periods()
            ->completed()
            ->orderByDesc('start_date')
            ->get();

        $last = $periods->first();

        if (! $last) {
            return $this->empty();
        }

        $averageCycle = $this->averageCycleLength($periods);
        $averageDuration = $this->averageDuration($periods);
        $nextStart = $averageCycle
            ? CarbonImmutable::parse($last->start_date)->addDays($averageCycle)
            : CarbonImmutable::parse($last->end_date)->addDays(28);

        $ovulation = $nextStart->subDays(14);
        $fertileStart = $ovulation->subDays(5);
        $fertileEnd = $ovulation->addDay();
        $periodEnd = $this->predictedPeriodEnd($nextStart, $averageDuration);

        return [
            'has_data' => true,
            'last_start' => CarbonImmutable::parse($last->start_date)->format('Y-m-d'),
            'last_end' => CarbonImmutable::parse($last->end_date)->format('Y-m-d'),
            'next_start' => $nextStart->format('Y-m-d'),
            'next_end' => $periodEnd->format('Y-m-d'),
            'days_until_next' => max(0, (int) $this->today->diffInDays($nextStart)),
            'days_late' => max(0, (int) $nextStart->diffInDays($this->today)),
            'is_late' => $this->today->gt($nextStart),
            'average_cycle' => $averageCycle,
            'average_duration' => $averageDuration,
            'longest_cycle' => $this->longestCycle($periods),
            'shortest_cycle' => $this->shortestCycle($periods),
            'cycle_count' => $periods->count(),
            'cycle_lengths' => $this->cycleLengths($periods),
            'durations' => $this->durations($periods),
            'ovulation' => $ovulation->format('Y-m-d'),
            'fertile_start' => $fertileStart->format('Y-m-d'),
            'fertile_end' => $fertileEnd->format('Y-m-d'),
            'phase' => $this->currentPhase($periods, $nextStart, $fertileStart, $fertileEnd),
            'last_mood' => $last->mood,
            'last_flow' => $last->flow,
        ];
    }

    protected function empty(): array
    {
        return [
            'has_data' => false,
            'last_start' => null,
            'last_end' => null,
            'next_start' => null,
            'next_end' => null,
            'days_until_next' => null,
            'days_late' => 0,
            'is_late' => false,
            'average_cycle' => null,
            'average_duration' => null,
            'longest_cycle' => null,
            'shortest_cycle' => null,
            'cycle_count' => 0,
            'cycle_lengths' => [],
            'durations' => [],
            'ovulation' => null,
            'fertile_start' => null,
            'fertile_end' => null,
            'phase' => 'Belum ada data',
            'last_mood' => null,
            'last_flow' => null,
        ];
    }

    public function averageCycleLength($periods): ?int
    {
        $lengths = $this->cycleLengths($periods);
        if (! $lengths) {
            return null;
        }

        $median = $this->median($lengths);

        return (int) round($median);
    }

    public function averageDuration($periods): ?int
    {
        $durations = $this->durations($periods);
        if (! $durations) {
            return null;
        }

        return (int) round($this->median($durations));
    }

    protected function cycleLengths($periods): array
    {
        return $periods
            ->filter(fn (Period $p) => $p->cycle_length !== null)
            ->sortByDesc('start_date')
            ->take(6)
            ->map(fn (Period $p) => (int) $p->cycle_length)
            ->values()
            ->all();
    }

    protected function durations($periods): array
    {
        return $periods
            ->sortByDesc('start_date')
            ->take(12)
            ->map(fn (Period $p) => (int) $p->duration)
            ->values()
            ->all();
    }

    protected function longestCycle($periods): ?int
    {
        $lengths = $this->cycleLengths($periods);

        return $lengths ? max($lengths) : null;
    }

    protected function shortestCycle($periods): ?int
    {
        $lengths = $this->cycleLengths($periods);

        return $lengths ? min($lengths) : null;
    }

    protected function median(array $values): float
    {
        sort($values);
        $count = count($values);
        $mid = intdiv($count, 2);

        if ($count % 2 === 0) {
            return ($values[$mid - 1] + $values[$mid]) / 2;
        }

        return $values[$mid];
    }

    protected function predictedPeriodEnd(CarbonImmutable $nextStart, ?int $duration): CarbonImmutable
    {
        $days = $duration ?: 5;

        return $nextStart->addDays(max(1, $days - 1));
    }

    /**
     * Determine which phase of the cycle today falls into.
     */
    protected function currentPhase(
        $periods,
        CarbonImmutable $nextStart,
        CarbonImmutable $fertileStart,
        CarbonImmutable $fertileEnd
    ): string {
        $today = $this->today->startOfDay();

        $active = $periods->first(fn (Period $p) => $today->between(
            CarbonImmutable::parse($p->start_date),
            CarbonImmutable::parse($p->end_date)
        ));

        if ($active) {
            return 'Menstruation';
        }

        if ($today->between($fertileStart, $fertileEnd)) {
            return 'Fertile Window';
        }

        if ($today->between($fertileEnd->addDay(), $nextStart)) {
            return 'Luteal Phase';
        }

        $last = $periods->first();
        $cycleDay = (int) CarbonImmutable::parse($last->start_date)->diffInDays($today) + 1;

        return $cycleDay <= ($this->averageDuration($periods) ?? 5)
            ? 'Menstruation'
            : 'Follicular Phase';
    }
}
