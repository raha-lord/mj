<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ProjectMemberController extends Controller
{
    /**
     * Получить список участников проекта
     */
    public function index(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();

        // Проверяем доступ к проекту
        if (!$project->isAccessibleBy($user)) {
            abort(403, 'Access denied');
        }

        $members = $project->users()->get();

        return response()->json([
            'data' => $members->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'avatar' => $member->avatar ?? null,
                    'pivot' => [
                        'role' => $member->pivot->role,
                        'joined_at' => $member->pivot->joined_at,
                    ],
                    'is_super_user' => $member->isSuperUser(),
                ];
            })
        ]);
    }

    /**
     * Добавить участника к проекту
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();

        // Проверяем права управления проектом
        if (!$project->canUserManage($user)) {
            abort(403, 'Insufficient permissions to manage project members');
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['required', Rule::in(['member', 'manager'])],
        ]);

        $targetUser = User::findOrFail($validated['user_id']);

        // Проверяем что пользователь принадлежит к той же организации
        if (!$targetUser->belongsToOrganization($project->organization_id)) {
            return response()->json([
                'message' => 'User is not a member of this organization'
            ], 422);
        }

        // Проверяем что пользователь еще не является участником проекта
        if ($project->hasUser($targetUser)) {
            return response()->json([
                'message' => 'User is already a member of this project'
            ], 422);
        }

        try {
            $project->users()->attach($targetUser->id, [
                'role' => $validated['role'],
                'joined_at' => now(),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            return response()->json([
                'message' => 'User added to project successfully',
                'user' => [
                    'id' => $targetUser->id,
                    'name' => $targetUser->name,
                    'email' => $targetUser->email,
                    'role' => $validated['role'],
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error adding user to project', [
                'project_id' => $project->id,
                'user_id' => $targetUser->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'An error occurred while adding user to project'
            ], 500);
        }
    }

    /**
     * Массовое добавление участников к проекту
     */
    public function bulkStore(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();

        // Проверяем права управления проектом
        if (!$project->canUserManage($user)) {
            abort(403, 'Insufficient permissions to manage project members');
        }

        $validated = $request->validate([
            'members' => ['required', 'array', 'min:1'],
            'members.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'members.*.role' => ['required', Rule::in(['member', 'manager'])],
        ]);

        $added = 0;
        $skipped = 0;
        $errors = [];

        foreach ($validated['members'] as $memberData) {
            try {
                $targetUser = User::findOrFail($memberData['user_id']);

                // Проверяем что пользователь принадлежит к той же организации
                if (!$targetUser->belongsToOrganization($project->organization_id)) {
                    $errors[] = "User {$targetUser->email} is not a member of this organization";
                    $skipped++;
                    continue;
                }

                // Проверяем что пользователь еще не является участником проекта
                if ($project->hasUser($targetUser)) {
                    $errors[] = "User {$targetUser->email} is already a member of this project";
                    $skipped++;
                    continue;
                }

                $project->users()->attach($targetUser->id, [
                    'role' => $memberData['role'],
                    'joined_at' => now(),
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);

                $added++;

            } catch (\Exception $e) {
                $errors[] = "Error adding user ID {$memberData['user_id']}: {$e->getMessage()}";
                $skipped++;
            }
        }

        return response()->json([
            'message' => "Successfully added {$added} members" . ($skipped > 0 ? ", {$skipped} skipped" : ''),
            'added' => $added,
            'skipped' => $skipped,
            'errors' => $errors
        ]);
    }

    /**
     * Обновить роль участника проекта
     */
    public function update(Request $request, Project $project, User $member): JsonResponse
    {
        $user = $request->user();

        // Проверяем права управления проектом
        if (!$project->canUserManage($user)) {
            abort(403, 'Insufficient permissions to manage project members');
        }

        // Проверяем что пользователь является участником проекта
        if (!$project->hasUser($member)) {
            return response()->json([
                'message' => 'User is not a member of this project'
            ], 404);
        }

        $validated = $request->validate([
            'role' => ['sometimes', Rule::in(['member', 'manager'])],
        ]);

        try {
            $updateData = array_filter([
                'role' => $validated['role'] ?? null,
                'updated_by' => $user->id,
            ]);

            $project->users()->updateExistingPivot($member->id, $updateData);

            return response()->json([
                'message' => 'Member updated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating project member', [
                'project_id' => $project->id,
                'member_id' => $member->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'An error occurred while updating member'
            ], 500);
        }
    }

    /**
     * Удалить участника из проекта
     */
    public function destroy(Request $request, Project $project, User $member): JsonResponse
    {
        $user = $request->user();

        // Проверяем права управления проектом
        if (!$project->canUserManage($user)) {
            abort(403, 'Insufficient permissions to manage project members');
        }

        // Проверяем что пользователь является участником проекта
        if (!$project->hasUser($member)) {
            return response()->json([
                'message' => 'User is not a member of this project'
            ], 404);
        }

        try {
            // Используем soft delete для pivot таблицы
            $project->users()->updateExistingPivot($member->id, [
                'deleted_at' => now(),
                'deleted_by' => $user->id,
            ]);

            return response()->json([
                'message' => 'Member removed from project successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error removing member from project', [
                'project_id' => $project->id,
                'member_id' => $member->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'An error occurred while removing member'
            ], 500);
        }
    }

    /**
     * Получить список пользователей организации для добавления в проект
     */
    public function availableUsers(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();

        // Проверяем права управления проектом
        if (!$project->canUserManage($user)) {
            abort(403, 'Insufficient permissions to manage project members');
        }

        $search = $request->get('search', '');

        // Получаем пользователей организации, которые еще не являются участниками проекта
        $query = User::whereHas('organizations', function ($query) use ($project) {
                $query->where('organization_id', $project->organization_id);
            })
            ->whereDoesntHave('projects', function ($query) use ($project) {
                $query->where('project_id', $project->id)
                      ->whereNull('project_user.deleted_at');
            });

        // Если есть поиск, применяем фильтр и лимит
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->limit(20);
        }
        // Если нет поиска, возвращаем всех доступных пользователей
        
        $availableUsers = $query->get(['id', 'name', 'email']);

        return response()->json([
            'data' => $availableUsers->map(function ($availableUser) {
                return [
                    'id' => $availableUser->id,
                    'name' => $availableUser->name ?: $availableUser->email,
                    'email' => $availableUser->email,
                ];
            })
        ]);
    }
}