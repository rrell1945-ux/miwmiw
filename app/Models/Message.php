<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Casting;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sender_id', 'recipient_id', 'period_day_id', 'body', 'read_at'])]
#[Casting('read_at', 'datetime')]
class Message extends Model
{
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function periodDay(): BelongsTo
    {
        return $this->belongsTo(PeriodDay::class);
    }
}
