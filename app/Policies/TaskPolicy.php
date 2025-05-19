<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $user->isAdmin() || $task->user_id === $user->id;
    }

    public function update(User $user, Task $task): bool
    {
        \Log::info('Checking update permission', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'task_user_id' => $task->user_id
        ]);
        return $user->isAdmin() || $task->user_id === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        \Log::info('Checking delete permission', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'task_user_id' => $task->user_id
        ]);
        return $user->isAdmin() || $task->user_id === $user->id;
    }
} 