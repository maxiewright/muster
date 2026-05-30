<?php

namespace App\Policies;

use App\Enums\UnitMembershipRole;
use App\Models\TrainingGoal;
use App\Models\User;

class TrainingGoalPolicy
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
    public function view(User $user, TrainingGoal $trainingGoal): bool
    {
        return $trainingGoal->unit_id === $user->activeUnitId();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->activeUnitId() !== null &&
               $user->hasUnitRole($user->activeUnitId(), UnitMembershipRole::Commander);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TrainingGoal $trainingGoal): bool
    {
        return $user->id === $trainingGoal->user_id ||
               $user->hasUnitRole($trainingGoal->unit_id, UnitMembershipRole::Commander);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TrainingGoal $trainingGoal): bool
    {
        return $user->id === $trainingGoal->user_id ||
               $user->hasUnitRole($trainingGoal->unit_id, UnitMembershipRole::Commander);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TrainingGoal $trainingGoal): bool
    {
        return $user->hasUnitRole($trainingGoal->unit_id, UnitMembershipRole::Commander);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TrainingGoal $trainingGoal): bool
    {
        return $user->is_platform_admin;
    }
}
