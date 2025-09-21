<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use App\Services\OrganizationContextService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrganizationMiddleware
{
    protected OrganizationContextService $contextService;

    public function __construct(OrganizationContextService $contextService)
    {
        $this->contextService = $contextService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $roles  Разрешенные роли через запятую (например: "org_admin,super_user")
     */
    public function handle(Request $request, Closure $next, string $roles = ''): Response
    {
        $user = $request->user();

        // Если пользователь не аутентифицирован
        if (!$user) {
            return redirect()->route('login');
        }

        // Парсим разрешенные роли
        $allowedRoles = array_filter(array_map('trim', explode(',', $roles)));

        // Если роли не указаны, проверяем только что пользователь имеет доступ к организации
        if (empty($allowedRoles)) {
            return $this->checkOrganizationAccess($request, $next, $user);
        }

        // Получаем текущую роль пользователя
        $currentRole = $this->contextService->getCurrentUserRole($user, $request);

        // Проверяем права доступа
        if (!$this->hasRequiredRole($currentRole, $allowedRoles)) {
            abort(403, 'Insufficient permissions for this organization');
        }

        // Если есть параметр organization_id в маршруте, проверяем доступ к конкретной организации
        if ($request->route('organization')) {
            return $this->checkSpecificOrganizationAccess($request, $next, $user, $allowedRoles);
        }

        return $next($request);
    }

    /**
     * Проверить базовый доступ к организации
     */
    protected function checkOrganizationAccess(Request $request, Closure $next, $user): Response
    {
        $currentOrg = $this->contextService->getCurrentOrganization($user, $request);

        if (!$currentOrg) {
            // Если у пользователя нет доступных организаций
            if ($user->isSuperUser()) {
                abort(404, 'No organizations found');
            } else {
                // Проверяем есть ли у пользователя приглашения
                $hasInvitations = \App\Models\OrganizationInvitation::where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->exists();
                
                
                if ($hasInvitations) {
                    // Перенаправляем на страницу выбора организации если есть приглашения
                    return redirect()->route('organization-selection');
                } else {
                    // Если приглашений нет, показываем ошибку доступа
                    abort(403, 'You are not a member of any organization');
                }
            }
        }

        return $next($request);
    }

    /**
     * Проверить доступ к конкретной организации
     */
    protected function checkSpecificOrganizationAccess(Request $request, Closure $next, $user, array $allowedRoles): Response
    {
        $organization = $request->route('organization');
        
        // Если это уже объект Organization (model binding), используем его
        if ($organization instanceof Organization) {
            // Все хорошо, объект уже получен
        } else {
            // Если это ID, находим организацию
            $organization = Organization::findOrFail($organization);
        }

        // Проверяем доступ к организации
        if (!$user->isSuperUser() && !$organization->hasUser($user)) {
            abort(403, 'Access denied to this organization');
        }

        // Проверяем роль в конкретной организации
        $userRole = $user->isSuperUser() ? 'super_user' : $organization->getUserRole($user);
        
        if (!$this->hasRequiredRole($userRole, $allowedRoles)) {
            abort(403, 'Insufficient permissions for this organization');
        }

        // Устанавливаем контекст организации
        $this->contextService->setCurrentOrganization($user, $organization, $request);

        return $next($request);
    }

    /**
     * Проверить есть ли у пользователя требуемая роль
     */
    protected function hasRequiredRole(?string $userRole, array $allowedRoles): bool
    {
        if (!$userRole) {
            return false;
        }

        // Супер пользователь имеет доступ ко всему
        if ($userRole === 'super_user') {
            return true;
        }

        // Проверяем точное совпадение роли
        if (in_array($userRole, $allowedRoles)) {
            return true;
        }

        // Проверяем иерархию ролей
        return $this->checkRoleHierarchy($userRole, $allowedRoles);
    }

    /**
     * Проверить иерархию ролей
     */
    protected function checkRoleHierarchy(string $userRole, array $allowedRoles): bool
    {
        $roleHierarchy = [
            'super_user' => ['super_user', 'org_admin', 'project_manager', 'member'],
            'org_admin' => ['org_admin', 'project_manager', 'member'],
            'project_manager' => ['project_manager', 'member'],
            'member' => ['member']
        ];

        if (!isset($roleHierarchy[$userRole])) {
            return false;
        }

        $userPermissions = $roleHierarchy[$userRole];

        // Проверяем есть ли пересечение между разрешениями пользователя и требуемыми ролями
        return !empty(array_intersect($userPermissions, $allowedRoles));
    }
}
