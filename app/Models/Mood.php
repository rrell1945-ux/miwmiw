<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'label', 'emoji', 'color', 'sort_order'])]
class Mood extends Model
{
    public static function emojiFor(?string $key): ?string
    {
        if (! $key) {
            return null;
        }

        return static::query()->where('key', $key)->value('emoji');
    }

    public static function labelFor(?string $key): ?string
    {
        if (! $key) {
            return null;
        }

        return static::query()->where('key', $key)->value('label');
    }
}
