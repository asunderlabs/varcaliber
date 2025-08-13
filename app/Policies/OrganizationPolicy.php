<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Context;

class OrganizationPolicy
{
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
    public function view(User $user, Organization $organization): bool
    {
        return $user->is_admin || Context::getHidden('organizations')->contains('id', $organization->id);
    }

    /**
     * Determine whether the user can view the organization stats.
     */
    public function viewStats(User $user, Organization $organization): bool
    {
        if (!in_array('organization_stats', config('enabled_features'))) {
            return false;
        }
        
        return $this->view($user, $organization);
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
    public function update(User $user, Organization $organization): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Organization $organization): bool
    {
        return $user->is_admin;
    }

}
