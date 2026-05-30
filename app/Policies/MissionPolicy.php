<?php

namespace App\Policies;

use App\Enums\UnitMembershipRole;
use App\Models\Mission;
use App\Models\User;

class MissionPolicy
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
    public function view(User $user, Mission $mission): bool
    {
        return $mission->unit_id === $user->activeUnitId();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->activeUnitId() !== null &&
               $user->hasUnitRole($user->activeUnitId(), [UnitMembershipRole::Commander, UnitMembershipRole::Lead]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Mission $mission): bool
    {
        return $user->id === $mission->created_by ||
               $user->hasUnitRole($mission->unit_id, [UnitMembershipRole::Commander, UnitMembershipRole::Lead]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Mission $mission): bool
    {
        return $user->id === $mission->created_by ||
               $user->hasUnitRole($mission->unit_id, UnitMembershipRole::Commander);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Mission $mission): bool
    {
        return $user->hasUnitRole($mission->unit_id, UnitMembershipRole::Commander);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Mission $mission): bool
    {
        return $user->is_platform_admin;
    }
}
