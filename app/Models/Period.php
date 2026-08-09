<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Casting;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'start_date',
    'end_date',
    'duration',
    'status',
    'cycle_length',
    'flow',
    'mood',
    'symptoms',
    'notes',
])]
#[Casting('symptoms', 'array')]
class Period extends Model
{
    public const STATUS_ONGOING = 'ongoing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        self::STATUS_ONGOING => 'Berlangsung',
        self::STATUS_COMPLETED => 'Selesai',
    ];

    public const FLOWS = [
        'light' => 'Ringan',
        'normal' => 'Normal',
        'heavy' => 'Banyak',
    ];

    public const SYMPTOMS = [
        'cramps' => 'Kram',
        'headache' => 'Sakit kepala',
        'back_pain' => 'Nyeri punggung',
        'bloating' => 'Perut kembung',
        'acne' => 'Jerawat',
        'nausea' => 'Mual',
        'fatigue' => 'Lelah',
        'stress' => 'Stres',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'symptoms' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function periodDays(): HasMany
    {
        return $this->hasMany(PeriodDay::class)->orderBy('day_date');
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', self::STATUS_ONGOING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function isOngoing(): bool
    {
        return $this->status === self::STATUS_ONGOING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function dayCount(): int
    {
        return $this->periodDays()->count();
    }

    public function moodEmoji(): ?string
    {
        return Mood::emojiFor($this->mood);
    }

    public function flowLabel(): ?string
    {
        return self::FLOWS[$this->flow] ?? null;
    }

    public function symptomsLabels(): array
    {
        return collect($this->symptoms ?? [])
            ->map(fn (string $key) => self::SYMPTOMS[$key] ?? $key)
            ->values()
            ->all();
    }
}
