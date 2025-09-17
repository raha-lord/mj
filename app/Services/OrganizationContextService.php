<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OrganizationContextService
{
    protected const CACHE_KEY_PREFIX = 'user_org_context_';
    protected const SESSION_KEY = 'current_organization_id';
    
    /**
     * Получить текущую организацию пользователя
     */
    public function getCurrentOrganization(User $user, Request $request = null): ?Organization
    {
        // Сначала проверяем кеш
        $cachedOrgId = $this->getCachedOrganizationId($user);
        if ($cachedOrgId) {
            $organization = Organization::find($cachedOrgId);
            if ($organization && $this->canUserAccessOrganization($user, $organization)) {
                return $organization;
            }
        }

        // Если в кеше нет или недоступна, ищем в сессии
        if ($request && $request->session()->has(self::SESSION_KEY)) {
            $sessionOrgId = $request->session()->get(self::SESSION_KEY);
            $organization = Organization::find($sessionOrgId);
            if ($organization && $this->canUserAccessOrganization($user, $organization)) {
                $this->setCachedOrganizationId($user, $organization->id);
                return $organization;
            }
        }

        // Если ничего не найдено, берем первую доступную организацию
        $defaultOrganization = $this->getDefaultOrganization($user);
        if ($defaultOrganization) {
            $this->setCurrentOrganization($user, $defaultOrganization, $request);
        }

        return $defaultOrganization;
    }

    /**
     * Установить текущую организацию пользователя
     */
    public function setCurrentOrganization(User $user, Organization $organization, Request $request = null): void
    {
        // Проверяем права доступа
        if (!$this->canUserAccessOrganization($user, $organization)) {
            throw new \InvalidArgumentException("User does not have access to this organization");
        }

        // Сохраняем в кеш
        $this->setCachedOrganizationId($user, $organization->id);

        // Сохраняем в сессию если есть request
        if ($request) {
            $request->session()->put(self::SESSION_KEY, $organization->id);
        }
    }

    /**
     * Получить все доступные организации пользователя
     */
    public function getAvailableOrganizations(User $user): \Illuminate\Support\Collection
    {
        if ($user->isSuperUser()) {
            return Organization::active()->orderBy('name')->get();
        }

        return $user->organizations()->active()->orderBy('name')->get();
    }

    /**
     * Переключиться на организацию
     */
    public function switchToOrganization(User $user, int $organizationId, Request $request = null): Organization
    {
        $organization = Organization::findOrFail($organizationId);
        
        if (!$this->canUserAccessOrganization($user, $organization)) {
            throw new \InvalidArgumentException("Access denied to organization");
        }

        $this->setCurrentOrganization($user, $organization, $request);
        
        return $organization;
    }

    /**
     * Очистить контекст организации пользователя
     */
    public function clearOrganizationContext(User $user, Request $request = null): void
    {
        // Очищаем кеш
        Cache::forget($this->getCacheKey($user));

        // Очищаем сессию
        if ($request) {
            $request->session()->forget(self::SESSION_KEY);
        }
    }

    /**
     * Получить роль пользователя в текущей организации
     */
    public function getCurrentUserRole(User $user, Request $request = null): ?string
    {
        $currentOrg = $this->getCurrentOrganization($user, $request);
        
        if (!$currentOrg) {
            return null;
        }

        if ($user->isSuperUser()) {
            return 'super_user';
        }

        return $currentOrg->getUserRole($user);
    }

    /**
     * Проверить права пользователя в текущей организации
     */
    public function hasPermission(User $user, string $permission, Request $request = null): bool
    {
        $role = $this->getCurrentUserRole($user, $request);
        
        return $this->checkRolePermission($role, $permission);
    }

    /**
     * Получить организацию по умолчанию для пользователя
     */
    protected function getDefaultOrganization(User $user): ?Organization
    {
        if ($user->isSuperUser()) {
            // Для супер пользователя берем первую активную организацию
            return Organization::active()->orderBy('created_at')->first();
        }

        // Для обычного пользователя берем первую организацию где он состоит
        return $user->organizations()->active()->orderBy('name')->first();
    }

    /**
     * Проверить может ли пользователь получить доступ к организации
     */
    protected function canUserAccessOrganization(User $user, Organization $organization): bool
    {
        if ($user->isSuperUser()) {
            return true;
        }

        return $organization->hasUser($user);
    }

    /**
     * Получить ID организации из кеша
     */
    protected function getCachedOrganizationId(User $user): ?int
    {
        return Cache::get($this->getCacheKey($user));
    }

    /**
     * Сохранить ID организации в кеш
     */
    protected function setCachedOrganizationId(User $user, int $organizationId): void
    {
        // Кешируем на 24 часа
        Cache::put($this->getCacheKey($user), $organizationId, now()->addDay());
    }

    /**
     * Получить ключ кеша для пользователя
     */
    protected function getCacheKey(User $user): string
    {
        return self::CACHE_KEY_PREFIX . $user->id;
    }

    /**
     * Проверить права роли на действие
     */
    protected function checkRolePermission(?string $role, string $permission): bool
    {
        $permissions = [
            'super_user' => ['*'], // Все права
            'org_admin' => [
                'view_organization',
                'manage_organization',
                'manage_users',
                'manage_projects',
                'manage_tasks',
                'view_reports'
            ],
            'project_manager' => [
                'view_organization',
                'manage_projects',
                'manage_tasks',
                'invite_users',
                'view_reports'
            ],
            'member' => [
                'view_organization',
                'view_projects',
                'manage_own_tasks',
                'view_reports'
            ]
        ];

        if (!$role || !isset($permissions[$role])) {
            return false;
        }

        $rolePermissions = $permissions[$role];

        // Супер пользователь имеет все права
        if (in_array('*', $rolePermissions)) {
            return true;
        }

        return in_array($permission, $rolePermissions);
    }

    /**
     * Получить контекстные данные для фронтенда
     */
    public function getContextData(User $user, Request $request = null): array
    {
        $currentOrg = $this->getCurrentOrganization($user, $request);
        $availableOrgs = $this->getAvailableOrganizations($user);
        $currentRole = $this->getCurrentUserRole($user, $request);

        return [
            'current_organization' => $currentOrg ? [
                'id' => $currentOrg->id,
                'name' => $currentOrg->name,
                'description' => $currentOrg->description,
            ] : null,
            'available_organizations' => $availableOrgs->map(function ($org) {
                return [
                    'id' => $org->id,
                    'name' => $org->name,
                    'description' => $org->description,
                ];
            }),
            'current_role' => $currentRole,
            'is_super_user' => $user->isSuperUser(),
            'permissions' => $this->getUserPermissions($currentRole),
        ];
    }

    /**
     * Получить список прав пользователя
     */
    protected function getUserPermissions(?string $role): array
    {
        $allPermissions = [
            'view_organization',
            'manage_organization', 
            'manage_users',
            'manage_projects',
            'manage_tasks',
            'manage_own_tasks',
            'invite_users',
            'view_reports'
        ];

        $permissions = [];
        foreach ($allPermissions as $permission) {
            $permissions[$permission] = $this->checkRolePermission($role, $permission);
        }

        return $permissions;
    }
}