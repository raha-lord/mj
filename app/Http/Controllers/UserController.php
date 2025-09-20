<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Поиск пользователей для приглашений в организацию
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $excludeOrganizationId = $request->input('exclude_organization');

        if (strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        $usersQuery = User::where(function ($q) use ($query) {
            $q->where('name', 'like', '%' . $query . '%')
              ->orWhere('email', 'like', '%' . $query . '%');
        });

        // Исключаем пользователей, которые уже состоят в указанной организации
        if ($excludeOrganizationId) {
            $organization = Organization::find($excludeOrganizationId);
            if ($organization) {
                // Исключаем пользователей которые уже в организации
                $existingUserIds = $organization->users()->pluck('user_id')->toArray();
                
                // Исключаем пользователей которые уже имеют активные приглашения
                $pendingInvitationUserIds = \App\Models\OrganizationInvitation::where('organization_id', $excludeOrganizationId)
                    ->where('status', 'pending')
                    ->pluck('user_id')
                    ->toArray();
                
                $excludedIds = array_merge($existingUserIds, $pendingInvitationUserIds);
                $usersQuery->whereNotIn('id', $excludedIds);
            }
        }

        $users = $usersQuery
            ->select(['id', 'name', 'email'])
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => null,
                ];
            })
        ]);
    }

    /**
     * Проверить email для приглашения
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $email = $request->input('email');
        $organizationId = $request->input('organization_id');

        if (!$email) {
            return response()->json(['exists' => false]);
        }

        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return response()->json(['exists' => false]);
        }

        $response = ['exists' => true];

        if ($organizationId) {
            $organization = Organization::find($organizationId);
            if ($organization) {
                // Проверяем является ли пользователь участником организации
                $response['in_organization'] = $organization->hasUser($user);
                
                // Проверяем есть ли активное приглашение
                $response['has_pending_invitation'] = \App\Models\OrganizationInvitation::where('organization_id', $organizationId)
                    ->where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->exists();
            }
        }

        return response()->json($response);
    }
}