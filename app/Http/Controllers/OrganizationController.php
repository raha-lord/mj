<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Services\OrganizationService;
use App\Services\OrganizationContextService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    protected OrganizationService $organizationService;
    protected OrganizationContextService $contextService;

    public function __construct(
        OrganizationService $organizationService,
        OrganizationContextService $contextService
    ) {
        $this->organizationService = $organizationService;
        $this->contextService = $contextService;
    }

    /**
     * Получить список организаций
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $organizations = $this->organizationService->getUserOrganizations($user);

        return response()->json([
            'organizations' => $organizations->map(function ($org) use ($user) {
                return [
                    'id' => $org->id,
                    'name' => $org->name,
                    'description' => $org->description,
                    'is_active' => $org->is_active,
                    'user_role' => $user->isSuperUser() ? 'super_user' : $org->getUserRole($user),
                    'users_count' => $org->getActiveUsersCount(),
                    'projects_count' => $org->getActiveProjectsCount(),
                    'created_at' => $org->created_at,
                ];
            }),
        ]);
    }

    /**
     * Получить детали организации
     */
    public function show(Request $request, Organization $organization): JsonResponse
    {
        $user = $request->user();

        // Проверяем доступ
        if (!$this->organizationService->canUserAccessOrganization($user, $organization)) {
            abort(403, 'Access denied');
        }

        $stats = $this->organizationService->getOrganizationStats($organization);

        return response()->json([
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'description' => $organization->description,
                'is_active' => $organization->is_active,
                'settings' => $organization->settings,
                'user_role' => $user->isSuperUser() ? 'super_user' : $organization->getUserRole($user),
                'created_at' => $organization->created_at,
                'updated_at' => $organization->updated_at,
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Создать новую организацию
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:tasks_management.organizations,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'settings' => ['nullable', 'array'],
            'settings.allow_public_projects' => ['boolean'],
            'settings.require_approval' => ['boolean'],
            'settings.default_role' => [Rule::in(['member', 'project_manager'])],
        ]);

        $user = $request->user();
        $organization = $this->organizationService->createOrganization($validated, $user);

        // Устанавливаем новую организацию как текущую
        $this->contextService->setCurrentOrganization($user, $organization, $request);

        return response()->json([
            'message' => 'Organization created successfully',
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'description' => $organization->description,
                'user_role' => 'org_admin',
            ],
        ], 201);
    }

    /**
     * Обновить организацию
     */
    public function update(Request $request, Organization $organization): JsonResponse
    {
        $user = $request->user();

        // Проверяем права управления
        if (!$this->organizationService->canUserManageOrganization($user, $organization)) {
            abort(403, 'Insufficient permissions');
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('tasks_management.organizations', 'name')->ignore($organization->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
            'settings' => ['nullable', 'array'],
            'settings.allow_public_projects' => ['boolean'],
            'settings.require_approval' => ['boolean'],
            'settings.default_role' => [Rule::in(['member', 'project_manager'])],
        ]);

        $organization = $this->organizationService->updateOrganization($organization, $validated);

        return response()->json([
            'message' => 'Organization updated successfully',
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'description' => $organization->description,
                'is_active' => $organization->is_active,
                'settings' => $organization->settings,
            ],
        ]);
    }

    /**
     * Удалить организацию (только SuperUser)
     */
    public function destroy(Request $request, Organization $organization): JsonResponse
    {
        $user = $request->user();

        // Только супер пользователь может удалять организации
        if (!$user->isSuperUser()) {
            abort(403, 'Only super users can delete organizations');
        }

        $this->organizationService->deactivateOrganization($organization);

        return response()->json([
            'message' => 'Organization deleted successfully',
        ]);
    }

    /**
     * Переключиться на организацию
     */
    public function switch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'integer', 'exists:tasks_management.organizations,id'],
        ]);

        $user = $request->user();
        $organization = $this->contextService->switchToOrganization(
            $user, 
            $validated['organization_id'], 
            $request
        );

        return response()->json([
            'message' => 'Organization switched successfully',
            'current_organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
                'description' => $organization->description,
                'user_role' => $user->isSuperUser() ? 'super_user' : $organization->getUserRole($user),
            ],
        ]);
    }

    /**
     * Получить контекстные данные организации
     */
    public function context(Request $request): JsonResponse
    {
        $user = $request->user();
        $contextData = $this->contextService->getContextData($user, $request);

        return response()->json($contextData);
    }

    /**
     * Поиск организаций
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:2', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $user = $request->user();
        $organizations = $this->organizationService->searchOrganizations(
            $validated['query'],
            $user,
            $validated['limit'] ?? 10
        );

        return response()->json([
            'organizations' => $organizations->map(function ($org) use ($user) {
                return [
                    'id' => $org->id,
                    'name' => $org->name,
                    'description' => $org->description,
                    'user_role' => $user->isSuperUser() ? 'super_user' : $org->getUserRole($user),
                ];
            }),
        ]);
    }
}
