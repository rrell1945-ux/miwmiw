<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Casting;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['period_id', 'day_date', 'flow', 'mood', 'symptoms', 'notes'])]
#[Casting('symptoms', 'array')]
class PeriodDay extends Model
{
    protected function casts(): array
    {
        return [
            'day_date' => 'date',
            'symptoms' => 'array',
        ];
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function message(): HasOne
    {
        return $this->hasOne(Message::class);
    }

    public function flowLabel(): ?string
    {
        return Period::FLOWS[$this->flow] ?? null;
    }

    public function symptomsLabels(): array
    {
        return collect($this->symptoms ?? [])
            ->map(fn (string $key) => Period::SYMPTOMS[$key] ?? $key)
            ->values()
            ->all();
    }
}
