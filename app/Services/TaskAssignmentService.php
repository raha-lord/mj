<?php
// app/Services/TaskAssignmentService.php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Exception;

class TaskAssignmentService
{
    /**
     * Добавить исполнителя к задаче
     */
    public function addAssignee(Task $task, int $userId, string $role = 'assignee'): void
    {
        // Проверяем дублирование
        $exists = $task->users()
            ->where('user_id', $userId)
            ->where('role', $role)
            ->exists();

        if ($exists) {
            throw new Exception(__('ui.user_already_assigned'));
        }

        $user = User::findOrFail($userId);
        $task->assignUser($user, $role);
    }

    /**
     * Удалить исполнителя из задачи
     */
    public function removeAssignee(Task $task, User $user, ?string $role = null): void
    {
        $task->unassignUser($user, $role);
    }

    /**
     * Массовое назначение исполнителей
     */
    public function assignMultipleUsers(Task $task, array $assignments): void
    {
        foreach ($assignments as $assignment) {
            $this->addAssignee(
                $task,
                $assignment['user_id'],
                $assignment['role'] ?? 'assignee'
            );
        }
    }

    /**
     * Изменить роль пользователя в задаче
     */
    public function changeUserRole(Task $task, int $userId, string $newRole): void
    {
        $pivot = $task->users()->where('user_id', $userId)->first();

        if (!$pivot) {
            throw new Exception(__('ui.user_not_assigned'));
        }

        $task->users()->updateExistingPivot($userId, ['role' => $newRole]);
    }

    /**
     * Получить список доступных ролей
     */
    public function getAvailableRoles(): array
    {
        return [
            'assignee' => __('ui.roles.assignee'),
            'observer' => __('ui.roles.observer'),
            'reviewer' => __('ui.roles.reviewer'),
            'manager' => __('ui.roles.manager'),
        ];
    }

    /**
     * Проверить может ли пользователь быть назначен на задачу
     */
    public function canAssignUser(Task $task, User $user, string $role): bool
    {
        // Проверяем максимальное количество задач для пользователя
        if ($role === 'assignee') {
            $activeTasksCount = $user->assignedTasks()
                ->whereNull('completed_date')
                ->count();

            // Лимит можно сделать настраиваемым
            $maxActiveTasks = config('tasks.max_active_tasks_per_user', 10);

            if ($activeTasksCount >= $maxActiveTasks) {
                return false;
            }
        }

        // Можно добавить другие проверки (права доступа к проекту и т.д.)

        return true;
    }

    /**
     * Автоматическое назначение на основе загруженности
     */
    public function autoAssign(Task $task, ?int $projectId = null): ?User
    {
        $query = User::whereHas('assignedTasks', function($q) {
            $q->whereNull('completed_date');
        }, '<', config('tasks.max_active_tasks_per_user', 10));

        // Если есть проект, приоритет пользователям этого проекта
        if ($projectId) {
            $query->whereHas('tasks', function($q) use ($projectId) {
                $q->where('project_id', $projectId);
            });
        }

        $user = $query->withCount(['assignedTasks as active_tasks_count' => function($q) {
            $q->whereNull('completed_date');
        }])
            ->orderBy('active_tasks_count')
            ->first();

        if ($user) {
            $this->addAssignee($task, $user->id);
        }

        return $user;
    }
}