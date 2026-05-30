<?php

namespace App\Policies;

use App\Enums\UnitMembershipRole;
use App\Models\Muster;
use App\Models\User;

class MusterPolicy
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
    public function view(User $user, Muster $muster): bool
    {
        return $muster->unit_id === $user->activeUnitId();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->activeUnitId() !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Muster $muster): bool
    {
        return $user->id === $muster->created_by ||
               $user->hasUnitRole($muster->unit_id, [UnitMembershipRole::Commander, UnitMembershipRole::Lead]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Muster $muster): bool
    {
        return $user->id === $muster->created_by ||
               $user->hasUnitRole($muster->unit_id, UnitMembershipRole::Commander);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Muster $muster): bool
    {
        return $user->hasUnitRole($muster->unit_id, UnitMembershipRole::Commander);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Muster $muster): bool
    {
        return $user->is_platform_admin;
    }
}
