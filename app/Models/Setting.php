<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Casting;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'theme',
    'notifications_enabled',
    'drink_water_reminder',
    'period_reminder',
    'cycle_reminder',
    'water_interval_minutes',
])]
#[Casting('notifications_enabled', 'boolean')]
#[Casting('drink_water_reminder', 'boolean')]
#[Casting('period_reminder', 'boolean')]
#[Casting('cycle_reminder', 'boolean')]
class Setting extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
