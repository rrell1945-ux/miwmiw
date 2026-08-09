<?php

namespace App\Policies;

use App\Models\Period;
use App\Models\User;

class PeriodPolicy
{
    public function view(User $user, Period $period): bool
    {
        return $user->id === $period->user_id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return ! $user->isAdmin();
    }

    public function checkIn(User $user, Period $period): bool
    {
        return $user->id === $period->user_id;
    }

    public function extend(User $user, Period $period): bool
    {
        return $user->id === $period->user_id;
    }

    public function finish(User $user, Period $period): bool
    {
        return $user->id === $period->user_id;
    }

    public function update(User $user, Period $period): bool
    {
        return $user->id === $period->user_id;
    }

    public function delete(User $user, Period $period): bool
    {
        return $user->id === $period->user_id;
    }
}
