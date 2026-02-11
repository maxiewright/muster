<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        $role = $user->role ?? \App\Enums\Role::Lead;

        return in_array('create_tasks', $role->permissions(), true);
    }

    public function update(User $user, Task $task): bool
    {
        return $task->canBeEditedBy($user);
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->isLead() || $task->created_by === $user->id;
    }

    public function restore(User $user, Task $task): bool
    {
        return $this->delete($user, $task);
    }

    public function forceDelete(User $user, Task $task): bool
    {
        return $this->delete($user, $task);
    }
}
