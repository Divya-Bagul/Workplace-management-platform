<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class WorkplaceNotifier
{
    /**
     * Queue notifications for every user that has any of the given Spatie roles.
     *
     * @param  array<int, string>  $roles
     */
    public function notifyUsersWithRoles(array $roles, Notification $notification): void
    {
        $this->usersWithRoles($roles)->each(fn (User $user) => $user->notify($notification));
    }

    /**
     * @param  array<int, string>  $roles
     * @return Collection<int, User>
     */
    public function usersWithRoles(array $roles): Collection
    {
        return User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', $roles))
            ->get();
    }
}
