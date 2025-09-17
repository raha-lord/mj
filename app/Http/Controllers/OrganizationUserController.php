<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Services\OrganizationService;
use App\Services\UserInvitationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class OrganizationUserController extends Controller
{
    protected OrganizationService $organizationService;
    protected UserInvitationService $invitationService;

    public function __construct(
        OrganizationService $organizationService,
        UserInvitationService $invitationService
    ) {
        $this->organizationService = $organizationService;
        $this->invitationService = $invitationService;
    }

    /**
     * Получить список участников организации
     */
    public function index(Request $request, Organization $organization): JsonResponse
    {
        $user = $request->user();

        // Проверяем доступ к организации
        if (!$this->organizationService->canUserAccessOrganization($user, $organization)) {
            abort(403, 'Access denied');
        }

        $members = $organization->users()->get();

        return response()->json([
            'members' => $members->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'role' => $member->pivot->role,
                    'joined_at' => $member->pivot->joined_at,
                    'notes' => $member->pivot->notes,
                    'has_password' => $member->hasPassword(),
                    'is_super_user' => $member->isSuperUser(),
                ];
            }),
        ]);
    }

    /**
     * Пригласить пользователя в организацию
     */
    public function store(Request $request, Organization $organization): JsonResponse
    {
        $user = $request->user();

        // Проверяем права управления пользователями
        if (!$this->organizationService->canUserManageOrganization($user, $organization) && 
            !$user->isProjectManager($organization->id)) {
            abort(403, 'Insufficient permissions');
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::in(['member', 'project_manager', 'org_admin'])],
        ]);

        // Проверяем права назначения ролей
        if ($validated['role'] === 'org_admin' && !$this->organizationService->canUserManageOrganization($user, $organization)) {
            abort(403, 'Only organization admins can assign admin role');
        }

        try {
            $result = $this->invitationService->findOrCreateUser(
                $organization,
                $validated['email'],
                $validated['name'],
                $validated['role'],
                $user
            );

            return response()->json([
                'message' => $result['message'],
                'action' => $result['action'],
                'user' => [
                    'id' => $result['user']->id,
                    'name' => $result['user']->name,
                    'email' => $result['user']->email,
                    'role' => $validated['role'],
                    'has_password' => $result['user']->hasPassword(),
                ],
            ], $result['action'] === 'created_new' ? 201 : 200);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Обновить роль участника
     */
    public function update(Request $request, Organization $organization, User $member): JsonResponse
    {
        $user = $request->user();

        // Проверяем права управления
        if (!$this->organizationService->canUserManageOrganization($user, $organization)) {
            abort(403, 'Insufficient permissions');
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in(['member', 'project_manager', 'org_admin'])],
        ]);

        try {
            $this->organizationService->updateUserRole($organization, $member, $validated['role']);

            return response()->json([
                'message' => 'User role updated successfully',
                'user' => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'role' => $validated['role'],
                ],
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Удалить участника из организации
     */
    public function destroy(Request $request, Organization $organization, User $member): JsonResponse
    {
        $user = $request->user();

        // Проверяем права управления
        if (!$this->organizationService->canUserManageOrganization($user, $organization)) {
            abort(403, 'Insufficient permissions');
        }

        // Нельзя удалить себя
        if ($user->id === $member->id) {
            return response()->json([
                'message' => 'You cannot remove yourself from the organization',
            ], 422);
        }

        try {
            $this->organizationService->removeUserFromOrganization($organization, $member);

            return response()->json([
                'message' => 'User removed from organization successfully',
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Массовое приглашение пользователей
     */
    public function bulkInvite(Request $request, Organization $organization): JsonResponse
    {
        $user = $request->user();

        // Проверяем права управления
        if (!$this->organizationService->canUserManageOrganization($user, $organization) && 
            !$user->isProjectManager($organization->id)) {
            abort(403, 'Insufficient permissions');
        }

        $validated = $request->validate([
            'invitations' => ['required', 'array', 'min:1', 'max:50'],
            'invitations.*.email' => ['required', 'email', 'max:255'],
            'invitations.*.name' => ['required', 'string', 'max:255'],
            'invitations.*.role' => ['required', Rule::in(['member', 'project_manager', 'org_admin'])],
        ]);

        // Проверяем права на назначение админов
        $hasAdminRoles = collect($validated['invitations'])->contains('role', 'org_admin');
        if ($hasAdminRoles && !$this->organizationService->canUserManageOrganization($user, $organization)) {
            abort(403, 'Only organization admins can assign admin role');
        }

        $results = $this->invitationService->bulkInviteUsers(
            $organization,
            $validated['invitations'],
            $user
        );

        $successCount = collect($results)->where('success', true)->count();
        $errorCount = collect($results)->where('success', false)->count();

        return response()->json([
            'message' => "Bulk invitation completed: {$successCount} successful, {$errorCount} failed",
            'results' => $results,
            'summary' => [
                'total' => count($results),
                'successful' => $successCount,
                'failed' => $errorCount,
            ],
        ]);
    }

    /**
     * Получить статистику приглашений
     */
    public function stats(Request $request, Organization $organization): JsonResponse
    {
        $user = $request->user();

        // Проверяем доступ
        if (!$this->organizationService->canUserAccessOrganization($user, $organization)) {
            abort(403, 'Access denied');
        }

        $stats = $this->invitationService->getInvitationStats($organization);

        return response()->json($stats);
    }

    /**
     * Отозвать приглашение
     */
    public function revokeInvitation(Request $request, Organization $organization, User $member): JsonResponse
    {
        $user = $request->user();

        // Проверяем права управления
        if (!$this->organizationService->canUserManageOrganization($user, $organization)) {
            abort(403, 'Insufficient permissions');
        }

        try {
            $wasDeleted = $this->invitationService->revokeInvitation($organization, $member);

            return response()->json([
                'message' => 'Invitation revoked successfully',
                'user_deleted' => $wasDeleted,
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
