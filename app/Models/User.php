<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function periods(): HasMany
    {
        return $this->hasMany(Period::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(Setting::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    public function setting(): Setting
    {
        return $this->settings()->firstOrCreate(
            ['user_id' => $this->id],
            ['theme' => 'light']
        );
    }

    public function displayName(): string
    {
        return $this->name ?: 'Sayang';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * The account whose cycle is being tracked. For the admin this is the
     * primary user account, otherwise the authenticated user themselves.
     */
    public function cycleUser(): self
    {
        if ($this->isAdmin()) {
            return static::query()->where('role', 'user')->first() ?? $this;
        }

        return $this;
    }

    /**
     * The other party in the two-way conversation: the admin for a user, the
     * primary user for an admin.
     */
    public function counterpart(): self
    {
        return static::query()
            ->where('role', $this->isAdmin() ? 'user' : 'admin')
            ->first() ?? $this;
    }

    public static function single(): ?self
    {
        return static::query()->first();
    }
}
