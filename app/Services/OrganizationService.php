<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrganizationService
{
    /**
     * Создать новую организацию
     */
    public function createOrganization(array $data, User $creator): Organization
    {
        $organization = Organization::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'settings' => $data['settings'] ?? [
                'allow_public_projects' => false,
                'require_approval' => false,
                'default_role' => 'member'
            ]
        ]);

        // Создатель автоматически становится админом
        $this->addUserToOrganization($organization, $creator, 'org_admin', 'Organization founder');

        return $organization;
    }

    /**
     * Обновить организацию
     */
    public function updateOrganization(Organization $organization, array $data): Organization
    {
        $organization->update([
            'name' => $data['name'] ?? $organization->name,
            'description' => $data['description'] ?? $organization->description,
            'is_active' => $data['is_active'] ?? $organization->is_active,
            'settings' => array_merge($organization->settings ?? [], $data['settings'] ?? [])
        ]);

        return $organization->fresh();
    }

    /**
     * Добавить пользователя в организацию
     */
    public function addUserToOrganization(
        Organization $organization, 
        User $user, 
        string $role = 'member',
        ?string $notes = null
    ): void {
        // Проверяем что пользователь еще не в организации
        if ($organization->hasUser($user)) {
            throw new \InvalidArgumentException("User {$user->email} is already in organization {$organization->name}");
        }

        $organization->users()->attach($user->id, [
            'role' => $role,
            'joined_at' => now(),
            'notes' => $notes
        ]);
    }

    /**
     * Обновить роль пользователя в организации
     */
    public function updateUserRole(Organization $organization, User $user, string $newRole): void
    {
        if (!$organization->hasUser($user)) {
            throw new ModelNotFoundException("User not found in organization");
        }

        // Проверяем что не остается без админов
        if ($organization->getUserRole($user) === 'org_admin' && $newRole !== 'org_admin') {
            $adminCount = $organization->admins()->count();
            if ($adminCount <= 1) {
                throw new \InvalidArgumentException("Cannot change role: organization must have at least one admin");
            }
        }

        $organization->users()->updateExistingPivot($user->id, [
            'role' => $newRole
        ]);
    }

    /**
     * Удалить пользователя из организации
     */
    public function removeUserFromOrganization(Organization $organization, User $user): void
    {
        if (!$organization->hasUser($user)) {
            throw new ModelNotFoundException("User not found in organization");
        }

        // Проверяем что не удаляем последнего админа
        if ($organization->getUserRole($user) === 'org_admin') {
            $adminCount = $organization->admins()->count();
            if ($adminCount <= 1) {
                throw new \InvalidArgumentException("Cannot remove last admin from organization");
            }
        }

        // Переназначаем активные задачи пользователя
        $this->reassignUserTasks($organization, $user);

        // Удаляем из организации
        $organization->users()->detach($user->id);
    }

    /**
     * Переназначить задачи пользователя при исключении
     */
    protected function reassignUserTasks(Organization $organization, User $user): void
    {
        // Находим все активные задачи пользователя в проектах организации
        $tasks = $user->assignedTasks()
            ->whereHas('project', function ($q) use ($organization) {
                $q->where('organization_id', $organization->id);
            })
            ->whereNull('completed_date')
            ->get();

        // Находим администратора для переназначения
        $admin = $organization->admins()->first();

        foreach ($tasks as $task) {
            if ($admin) {
                // Переназначаем на админа
                $task->users()->detach($user->id);
                $task->assignUser($admin, 'assignee', 'Reassigned due to user removal from organization');
            } else {
                // Просто убираем назначение
                $task->users()->detach($user->id);
            }
        }
    }

    /**
     * Деактивировать организацию (soft delete)
     */
    public function deactivateOrganization(Organization $organization): void
    {
        // Деактивируем все проекты
        $organization->projects()->update(['deleted_at' => now()]);

        // Деактивируем организацию
        $organization->delete();
    }

    /**
     * Восстановить организацию
     */
    public function reactivateOrganization(int $organizationId): Organization
    {
        $organization = Organization::withTrashed()->findOrFail($organizationId);
        
        $organization->restore();

        // Восстанавливаем проекты
        $organization->projects()->withTrashed()->restore();

        return $organization;
    }

    /**
     * Получить доступные организации для пользователя
     */
    public function getUserOrganizations(User $user): Collection
    {
        if ($user->isSuperUser()) {
            return Organization::active()->get();
        }

        return $user->organizations()->active()->get();
    }

    /**
     * Проверить права доступа пользователя к организации
     */
    public function canUserAccessOrganization(User $user, Organization $organization): bool
    {
        return $user->isSuperUser() || $organization->hasUser($user);
    }

    /**
     * Проверить может ли пользователь управлять организацией
     */
    public function canUserManageOrganization(User $user, Organization $organization): bool
    {
        if ($user->isSuperUser()) {
            return true;
        }

        return $organization->isAdmin($user);
    }

    /**
     * Получить статистику организации
     */
    public function getOrganizationStats(Organization $organization): array
    {
        return [
            'users_count' => $organization->getActiveUsersCount(),
            'projects_count' => $organization->getActiveProjectsCount(),
            'admins_count' => $organization->admins()->count(),
            'project_managers_count' => $organization->projectManagers()->count(),
            'members_count' => $organization->members()->count(),
            'active_tasks_count' => $organization->projects()
                ->withCount(['tasks' => function ($q) {
                    $q->whereNull('completed_date');
                }])
                ->get()
                ->sum('tasks_count'),
        ];
    }

    /**
     * Поиск организаций
     */
    public function searchOrganizations(string $query, User $user, int $limit = 10): Collection
    {
        $organizations = Organization::active()
            ->where('name', 'ILIKE', "%{$query}%")
            ->orWhere('description', 'ILIKE', "%{$query}%");

        // Ограничиваем доступ для не-супер пользователей
        if (!$user->isSuperUser()) {
            $organizations->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        return $organizations->limit($limit)->get();
    }

    /**
     * Получить доступные организации для пользователя
     */
    public function getAvailableOrganizations(User $user): Collection
    {
        if ($user->isSuperUser()) {
            // Супер пользователь видит все активные организации
            return Organization::active()->get();
        }

        // Обычный пользователь видит только организации, в которых он состоит
        return $user->organizations()->where('is_active', true)->get();
    }
}