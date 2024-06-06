<?php

namespace App\Policies;

use App\Models\Source;
use App\Models\User;

class SourcePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Source $source): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Source $source): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Source $source): bool
    {
        $team = $source->collection->team;

        return $user->isAdmin() || $user->belongsToTeam($team);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Source $source): bool
    {
        $team = $source->collection->team;

        return $user->isAdmin() || $user->belongsToTeam($team);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Source $source): bool
    {
        $team = $source->collection->team;

        return $user->isAdmin() || $user->belongsToTeam($team);
    }
}
