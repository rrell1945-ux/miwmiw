<?php

namespace App\Services;

use App\Models\Period;
use App\Models\PeriodDay;
use App\Models\User;
use Carbon\CarbonImmutable;

class PeriodService
{
    /**
     * Begin a new period on the given date. Any other ongoing period is
     * closed first, so only one period is ever active at a time.
     */
    public function start(User $user, CarbonImmutable $start, array $condition = []): Period
    {
        $user->periods()
            ->where('status', Period::STATUS_ONGOING)
            ->get()
            ->each(fn (Period $period) => $this->complete($period));

        $period = new Period;
        $period->user_id = $user->id;
        $period->start_date = $start->format('Y-m-d');
        $period->end_date = $start->format('Y-m-d');
        $period->duration = 1;
        $period->status = Period::STATUS_ONGOING;
        $period->cycle_length = $this->computeCycleLength($user, $start, null);
        $period->save();

        $this->recordDay($period, $start, $condition);

        return $period->fresh();
    }

    /**
     * Create a finished period from an explicit date range (used when
     * backfilling or editing via the period form).
     */
    public function create(User $user, array $data): Period
    {
        $start = CarbonImmutable::parse($data['start_date']);
        $end = CarbonImmutable::parse($data['end_date'] ?? $data['start_date']);

        if ($end->lt($start)) {
            $end = $start;
        }

        $period = new Period;
        $period->user_id = $user->id;
        $period->start_date = $start->format('Y-m-d');
        $period->end_date = $end->format('Y-m-d');
        $period->duration = $this->computeDuration($period);
        $period->status = Period::STATUS_COMPLETED;
        $period->cycle_length = $this->computeCycleLength($user, $start, null);

        foreach (['flow', 'mood', 'symptoms', 'notes'] as $field) {
            if (array_key_exists($field, $data)) {
                $period->{$field} = $data[$field];
            }
        }

        $period->save();

        return $period->fresh();
    }

    /**
     * Extend the end date of an ongoing period (daily check-in).
     * By default extends to today; a specific date can be passed when
     * backfilling a missed day. The day is recorded as a period_day row so
     * the daily condition (flow, mood, symptoms, notes) is kept and the
     * admin can attach a recommendation to it.
     */
    public function extend(Period $period, ?CarbonImmutable $until = null, array $condition = []): Period
    {
        if (! $period->isOngoing()) {
            return $period->fresh();
        }

        $today = CarbonImmutable::today();
        $target = ($until ?? $today)->min($today);

        if ($target->gt(CarbonImmutable::parse($period->end_date))) {
            $period->end_date = $target->format('Y-m-d');
        }

        $period->duration = $this->computeDuration($period);
        $period->save();

        $this->recordDay($period, $target, $condition);

        return $period->fresh();
    }

    /**
     * Mark the period as finished. The end date is brought up to today if
     * the user confirms completion on a later day.
     */
    public function complete(Period $period): Period
    {
        if ($period->isCompleted()) {
            return $period->fresh();
        }

        $today = CarbonImmutable::today();

        if ($today->gt(CarbonImmutable::parse($period->end_date))) {
            $period->end_date = $today->format('Y-m-d');
        }

        $period->duration = $this->computeDuration($period);
        $period->status = Period::STATUS_COMPLETED;
        $period->save();

        return $period->fresh();
    }

    /**
     * Update the date range (and optional details) of an existing period.
     */
    public function update(Period $period, array $data): Period
    {
        $start = CarbonImmutable::parse($data['start_date']);
        $end = CarbonImmutable::parse($data['end_date'] ?? $data['start_date']);

        if ($end->lt($start)) {
            $end = $start;
        }

        $period->start_date = $start->format('Y-m-d');
        $period->end_date = $end->format('Y-m-d');
        $period->duration = $this->computeDuration($period);
        $period->cycle_length = $this->computeCycleLength($period->user, $start, $period->id);

        foreach (['flow', 'mood', 'symptoms', 'notes'] as $field) {
            if (array_key_exists($field, $data)) {
                $period->{$field} = $data[$field];
            }
        }

        $period->save();

        return $period->fresh();
    }

    public function delete(Period $period): void
    {
        $period->delete();
    }

    /**
     * Upsert the per-day check-in record for a period. Condition fields are
     * only overwritten when present in the incoming data, so a plain check-in
     * never wipes previously recorded details.
     */
    protected function recordDay(Period $period, CarbonImmutable $day, array $condition): void
    {
        $dayRecord = PeriodDay::firstOrNew([
            'period_id' => $period->id,
            'day_date' => $day->format('Y-m-d'),
        ]);

        foreach (['flow', 'mood', 'notes'] as $field) {
            if (array_key_exists($field, $condition)) {
                $dayRecord->{$field} = $condition[$field] ?: null;
            }
        }

        if (array_key_exists('symptoms', $condition)) {
            $dayRecord->symptoms = $condition['symptoms'] ?: [];
        }

        $dayRecord->save();
    }

    protected function computeDuration(Period $period): int
    {
        return (int) CarbonImmutable::parse($period->start_date)
            ->diffInDays(CarbonImmutable::parse($period->end_date)) + 1;
    }

    /**
     * Cycle length is the number of days between this period start and the
     * most recent previous completed period start.
     */
    protected function computeCycleLength(User $user, CarbonImmutable $start, ?int $excludeId = null): ?int
    {
        $previous = $user->periods()
            ->completed()
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->where('start_date', '<', $start->format('Y-m-d'))
            ->orderByDesc('start_date')
            ->first();

        if (! $previous) {
            return null;
        }

        return (int) CarbonImmutable::parse($previous->start_date)
            ->diffInDays($start);
    }
}
