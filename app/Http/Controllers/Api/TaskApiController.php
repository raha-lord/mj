<?php
// app/Http/Controllers/Api/TaskApiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogTimeRequest;
use App\Http\Requests\AddAssigneeRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Models\Task;
use App\Models\TaskSize;
use App\Models\User;
use App\Services\TaskAssignmentService;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskApiController extends Controller
{
    protected TaskAssignmentService $assignmentService;
    protected TaskService $taskService;

    public function __construct(TaskAssignmentService $assignmentService, TaskService $taskService)
    {
        $this->assignmentService = $assignmentService;
        $this->taskService = $taskService;
    }

    /**
     * Получить отфильтрованные задачи
     */
    public function index(Request $request): JsonResponse
    {
        $tasks = $this->taskService->getFilteredTasks($request, 25);
        
        return response()->json([
            'success' => true,
            'data' => [
                'tasks' => $tasks->items(),
                'pagination' => [
                    'current_page' => $tasks->currentPage(),
                    'last_page' => $tasks->lastPage(),
                    'per_page' => $tasks->perPage(),
                    'total' => $tasks->total(),
                    'from' => $tasks->firstItem(),
                    'to' => $tasks->lastItem()
                ]
            ]
        ]);
    }

    /**
     * Создать новую задачу
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        try {
            $task = $this->taskService->createTask($request->validated());
            
            // Загружаем связи для полного ответа
            $task->load(['status', 'project', 'size', 'assignees', 'createdBy']);
            
            return response()->json([
                'success' => true,
                'message' => __('ui.task_created_successfully'),
                'data' => $task
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании задачи: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Получить HTML таблицы задач для AJAX
     */
    public function tableHtml(Request $request): JsonResponse
    {
        $tasks = $this->taskService->getFilteredTasks($request, 25);
        
        $html = view('tasks.partials.table', compact('tasks'))->render();
        
        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Получить данные задачи
     */
    public function show(Task $task): JsonResponse
    {
        // Загружаем все необходимые связи
        $task->load([
            'status', 
            'project', 
            'size', 
            'assignees', 
            'createdBy', 
            'updatedBy'
        ]);
        
        return response()->json([
            'success' => true,
            'data' => $task
        ]);
    }

    /**
     * Обновить задачу
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
            'status_id' => 'required|exists:statuses,id',
            'priority' => 'required|in:low,normal,high,urgent',
            'size_id' => 'nullable|exists:task_sizes,id',
            'estimated_hours' => 'nullable|numeric|min:0.25|max:1000',
            'd_end' => 'nullable|date',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:users,id'
        ]);

        try {
            $this->taskService->updateTask($task, $validated);
            
            // Перезагружаем задачу с связями
            $task->load([
                'status', 
                'project', 
                'size', 
                'assignees', 
                'createdBy', 
                'updatedBy'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => __('ui.task_updated_successfully'),
                'data' => $task
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении задачи: ' . $e->getMessage()
            ], 422);
        }
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