<?php
// app/Http/Controllers/Api/TaskApiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogTimeRequest;
use App\Http\Requests\AddAssigneeRequest;
use App\Models\Task;
use App\Models\TaskSize;
use App\Models\User;
use App\Services\TaskAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskApiController extends Controller
{
    protected TaskAssignmentService $assignmentService;

    public function __construct(TaskAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Логирование времени
     */
    public function logTime(LogTimeRequest $request, Task $task): JsonResponse
    {
        $validated = $request->validated();

        $task->logTimeSpent($validated['hours'], $validated['description'] ?? null);

        return response()->json([
            'success' => true,
            'message' => __('ui.time_logged_successfully', ['hours' => $validated['hours']]),
            'data' => [
                'actual_hours' => $task->fresh()->actual_hours,
                'time_progress' => $task->time_progress,
            ]
        ]);
    }

    /**
     * Получить рекомендации по размеру на основе оценки
     */
    public function getSizeRecommendation(Request $request): JsonResponse
    {
        $estimatedHours = $request->input('estimated_hours');

        if (!$estimatedHours) {
            return response()->json(['size' => null]);
        }

        $recommendedSize = TaskSize::active()
            ->where('min_hours', '<=', $estimatedHours)
            ->where(function ($query) use ($estimatedHours) {
                $query->where('max_hours', '>=', $estimatedHours)
                    ->orWhereNull('max_hours');
            })
            ->ordered()
            ->first();

        return response()->json([
            'size' => $recommendedSize ? [
                'id' => $recommendedSize->id,
                'code' => $recommendedSize->code,
                'name' => $recommendedSize->name,
                'time_range' => $recommendedSize->time_range,
            ] : null
        ]);
    }

    /**
     * Добавить исполнителя
     */
    public function addAssignee(AddAssigneeRequest $request, Task $task): JsonResponse
    {
        $validated = $request->validated();

        try {
            $this->assignmentService->addAssignee($task, $validated['user_id'], $validated['role']);

            return response()->json([
                'success' => true,
                'message' => __('ui.assignee_added_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Удалить исполнителя
     */
    public function removeAssignee(Task $task, User $user): JsonResponse
    {
        $this->assignmentService->removeAssignee($task, $user);

        return response()->json([
            'success' => true,
            'message' => __('ui.assignee_removed_successfully')
        ]);
    }

    /**
     * Изменить роль исполнителя
     */
    public function changeAssigneeRole(Request $request, Task $task, User $user): JsonResponse
    {
        $request->validate([
            'role' => 'required|in:assignee,observer,reviewer,manager'
        ]);

        try {
            $this->assignmentService->changeUserRole($task, $user->id, $request->role);

            return response()->json([
                'success' => true,
                'message' => __('ui.assignee_role_changed_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Отметить задачу как выполненную
     */
    public function markCompleted(Task $task): JsonResponse
    {
        $task->markAsCompleted(auth()->user());

        return response()->json([
            'success' => true,
            'message' => __('ui.task_marked_completed'),
            'data' => [
                'completed_date' => $task->fresh()->completed_date,
                'is_completed' => true,
            ]
        ]);
    }

    /**
     * Переоткрыть задачу
     */
    public function reopen(Task $task): JsonResponse
    {
        $task->update(['completed_date' => null]);

        return response()->json([
            'success' => true,
            'message' => __('ui.task_reopened'),
            'data' => [
                'completed_date' => null,
                'is_completed' => false,
            ]
        ]);
    }

    /**
     * Получить статистику по размерам задач
     */
    public function sizeStats(): JsonResponse
    {
        $sizes = TaskSize::active()->ordered()->get();

        $stats = $sizes->map(function ($size) {
            return [
                'size' => $size->only(['id', 'code', 'name', 'time_range']),
                'stats' => $size->getUsageStats(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Автоматическое назначение исполнителя
     */
    public function autoAssign(Task $task): JsonResponse
    {
        $user = $this->assignmentService->autoAssign($task, $task->project_id);

        if ($user) {
            return response()->json([
                'success' => true,
                'message' => __('ui.auto_assigned_successfully'),
                'data' => [
                    'assigned_user' => $user->only(['id', 'name', 'email'])
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('ui.auto_assign_failed')
        ], 422);
    }
}