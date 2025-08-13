<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkEntry;
use Illuminate\Auth\Access\Response;

class WorkEntryPolicy
{
    public function before(User $user)
    {
        return in_array('time_tracking', config('enabled_features'));
    }
    
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WorkEntry $workEntry): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WorkEntry $workEntry): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WorkEntry $workEntry): bool
    {
        return $user->is_admin;
    }
}
