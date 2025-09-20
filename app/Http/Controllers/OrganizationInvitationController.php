<?php

namespace App\Http\Controllers;

use App\Models\OrganizationInvitation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrganizationInvitationController extends Controller
{
    /**
     * Получить приглашения для текущего пользователя
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $invitations = OrganizationInvitation::forUser($user->id)
            ->pending()
            ->with(['organization', 'inviter'])
            ->get();

        return response()->json([
            'data' => $invitations->map(function ($invitation) {
                return [
                    'id' => $invitation->id,
                    'organization' => [
                        'id' => $invitation->organization->id,
                        'name' => $invitation->organization->name,
                        'description' => $invitation->organization->description,
                    ],
                    'role' => $invitation->role,
                    'message' => $invitation->message,
                    'inviter' => [
                        'name' => $invitation->inviter->name ?? 'System',
                        'email' => $invitation->inviter->email ?? null,
                    ],
                    'created_at' => $invitation->created_at,
                    'expires_at' => $invitation->expires_at,
                ];
            })
        ]);
    }

    /**
     * Принять приглашение
     */
    public function accept(Request $request, OrganizationInvitation $invitation): JsonResponse
    {
        $user = $request->user();
        
        // Проверяем что приглашение для текущего пользователя
        if ($invitation->user_id !== $user->id) {
            abort(403, 'This invitation is not for you');
        }

        if (!$invitation->isValid()) {
            return response()->json([
                'message' => 'Invitation is no longer valid'
            ], 422);
        }

        try {
            if ($invitation->accept()) {
                return response()->json([
                    'message' => 'Invitation accepted successfully',
                    'organization' => [
                        'id' => $invitation->organization->id,
                        'name' => $invitation->organization->name,
                    ]
                ]);
            }

            return response()->json([
                'message' => 'Failed to accept invitation'
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error accepting invitation', [
                'invitation_id' => $invitation->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'An error occurred while accepting the invitation'
            ], 500);
        }
    }

    /**
     * Отклонить приглашение
     */
    public function decline(Request $request, OrganizationInvitation $invitation): JsonResponse
    {
        $user = $request->user();
        
        // Проверяем что приглашение для текущего пользователя
        if ($invitation->user_id !== $user->id) {
            abort(403, 'This invitation is not for you');
        }

        if (!$invitation->isValid()) {
            return response()->json([
                'message' => 'Invitation is no longer valid'
            ], 422);
        }

        if ($invitation->decline()) {
            return response()->json([
                'message' => 'Invitation declined successfully'
            ]);
        }

        return response()->json([
            'message' => 'Failed to decline invitation'
        ], 422);
    }

    /**
     * Отозвать приглашение (для админов)
     */
    public function revoke(Request $request, OrganizationInvitation $invitation): JsonResponse
    {
        $user = $request->user();
        
        // Проверяем права на отзыв приглашения
        if (!$user->isSuperUser() && 
            $invitation->organization->getUserRole($user) !== 'org_admin' &&
            $invitation->invited_by !== $user->id) {
            abort(403, 'Insufficient permissions to revoke this invitation');
        }

        if ($invitation->revoke()) {
            return response()->json([
                'message' => 'Invitation revoked successfully'
            ]);
        }

        return response()->json([
            'message' => 'Failed to revoke invitation'
        ], 422);
    }
}
