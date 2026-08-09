<?php

namespace App\Services;

use App\Models\Period;
use App\Models\User;
use Illuminate\Support\Collection;

class BackupService
{
    /**
     * Export every user-owned record as an array (for JSON download).
     */
    public function export(User $user): array
    {
        return [
            'app' => 'mimiw',
            'version' => 1,
            'exported_at' => now()->toIso8601String(),
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'periods' => $user->periods()
                ->orderBy('start_date')
                ->get()
                ->map(fn (Period $period) => [
                    'start_date' => $period->start_date->format('Y-m-d'),
                    'end_date' => $period->end_date->format('Y-m-d'),
                    'duration' => $period->duration,
                    'status' => $period->status,
                    'cycle_length' => $period->cycle_length,
                    'flow' => $period->flow,
                    'mood' => $period->mood,
                    'symptoms' => $period->symptoms,
                    'notes' => $period->notes,
                ])
                ->all(),
            'settings' => $user->setting()->only([
                'theme',
                'notifications_enabled',
                'drink_water_reminder',
                'period_reminder',
                'cycle_reminder',
                'water_interval_minutes',
            ]),
        ];
    }

    /**
     * Restore periods from a backup payload, replacing existing data.
     *
     * @throws \InvalidArgumentException
     */
    public function restore(User $user, array $payload): int
    {
        if (!in_array($payload['app'] ?? null, ['mimiw', 'bloom'], true)) {
            throw new \InvalidArgumentException('Bukan file cadangan Mimiw yang valid.');
        }

        $periods = collect($payload['periods'] ?? []);

        $user->periods()->delete();

        foreach ($periods as $row) {
            $user->periods()->create([
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'duration' => $row['duration'] ?? $this->recomputeDuration($row),
                'status' => $row['status'] ?? 'completed',
                'cycle_length' => $row['cycle_length'] ?? null,
                'flow' => $row['flow'] ?? null,
                'mood' => $row['mood'] ?? null,
                'symptoms' => $row['symptoms'] ?? [],
                'notes' => $row['notes'] ?? null,
            ]);
        }

        $settings = $payload['settings'] ?? [];
        $user->setting()->update(collect($settings)
            ->only([
                'theme',
                'notifications_enabled',
                'drink_water_reminder',
                'period_reminder',
                'cycle_reminder',
                'water_interval_minutes',
            ])
            ->all());

        return $periods->count();
    }

    protected function recomputeDuration(array $row): int
    {
        $start = \Carbon\CarbonImmutable::parse($row['start_date']);
        $end = \Carbon\CarbonImmutable::parse($row['end_date']);

        return $start->diffInDays($end) + 1;
    }
}
