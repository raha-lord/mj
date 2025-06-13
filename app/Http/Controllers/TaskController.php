<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Status;
use App\Models\Task;
use App\Models\TaskSize;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['status', 'project', 'size', 'assignees']);

        // Фильтры
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                    ->orWhere('description', 'LIKE', "%$search%")
                    ->orWhereHas('status', fn($sq) => $sq->where('name', 'LIKE', "%$search%"))
                    ->orWhereHas('project', fn($pq) => $pq->where('name', 'LIKE', "%$search%"));
            });
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        if ($request->filled('size')) {
            $query->bySize($request->size);
        }

        if ($request->filled('project')) {
            $query->where('project_id', $request->project);
        }

        if ($request->filled('assignee')) {
            $query->assignedTo($request->assignee);
        }

        // Фильтр по времени
        if ($request->filled('time_filter')) {
            switch ($request->time_filter) {
                case 'overdue':
                    $query->overdue();
                    break;
                case 'over_budget':
                    $query->whereRaw('actual_hours > estimated_hours');
                    break;
                case 'no_estimate':
                    $query->whereNull('estimated_hours');
                    break;
            }
        }

        $tasks = $query->latest()->paginate(25);

        // Данные для фильтров
        $statuses = Status::ordered()->get();
        $projects = Project::orderBy('name')->get();
        $sizes = TaskSize::active()->ordered()->get();
        $users = User::orderBy('name')->get();

        return view('tasks.index', compact('tasks', 'statuses', 'projects', 'sizes', 'users'));
    }

    public function create()
    {
        $statuses = Status::ordered()->get();
        $projects = Project::orderBy('name')->get();
        $sizes = TaskSize::active()->ordered()->get();
        $users = User::orderBy('name')->get();

        return view('tasks.create', compact('statuses', 'projects', 'sizes', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'size_id' => 'nullable|exists:task_sizes,id', // убрали префикс
            'estimated_hours' => 'nullable|numeric|min:0.25|max:1000',
            'status_id' => 'required|exists:statuses,id', // убрали префикс (и исправили на statuses)
            'project_id' => 'nullable|exists:projects,id', // убрали префикс
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:users,id',
        ]);

        // Если не указан размер, но есть оценка времени - пытаемся подобрать размер
        if (!$validated['size_id'] && $validated['estimated_hours']) {
            $recommendedSize = TaskSize::active()
                ->where('min_hours', '<=', $validated['estimated_hours'])
                ->where(function ($query) use ($validated) {
                    $query->where('max_hours', '>=', $validated['estimated_hours'])
                        ->orWhereNull('max_hours');
                })
                ->ordered()
                ->first();

            if ($recommendedSize) {
                $validated['size_id'] = $recommendedSize->id;
            }
        }

        $task = Task::create($validated);

        // Назначаем исполнителей
        if (!empty($validated['assignees'])) {
            foreach ($validated['assignees'] as $userId) {
                $task->assignUser(User::find($userId));
            }
        }

        return redirect()->route('tasks.index')
            ->with('success', __('ui.task_created_successfully'));
    }

    public function show(Task $task)
    {
        $task->load([
            'status', 'project', 'size', 'users',
            'history.user', 'createdBy', 'updatedBy'
        ]);

        // Получаем рекомендации по размеру
        $recommendedSize = $task->getRecommendedSize();

        // Статистика времени
        $timeStats = [
            'estimation_accuracy' => $task->getEstimationAccuracy(),
            'is_over_budget' => $task->isOverBudget(),
            'time_progress' => $task->time_progress,
            'is_time_accurate' => $task->isTimeAccurate(),
        ];

        return view('tasks.show', compact('task', 'recommendedSize', 'timeStats'));
    }

    public function edit(Task $task)
    {
        $task->load('users');
        $statuses = Status::ordered()->get();
        $projects = Project::orderBy('name')->get();
        $sizes = TaskSize::active()->ordered()->get();
        $users = User::orderBy('name')->get();

        return view('tasks.edit', compact('task', 'statuses', 'projects', 'sizes', 'users'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'size_id' => 'nullable|exists:task_sizes,id', // убрали префикс
            'estimated_hours' => 'nullable|numeric|min:0.25|max:1000',
            'actual_hours' => 'nullable|numeric|min:0|max:1000',
            'status_id' => 'required|exists:statuses,id', // убрали префикс (и исправили на statuses)
            'project_id' => 'nullable|exists:projects,id', // убрали префикс
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $task->update($validated);

        return redirect()->route('tasks.index')
            ->with('success', __('ui.task_updated_successfully'));
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', __('ui.task_deleted_successfully'));
    }

    /**
     * Логирование времени
     */
    public function logTime(Request $request, Task $task)
    {
        $validated = $request->validate([
            'hours' => 'required|numeric|min:0.25|max:24',
            'description' => 'nullable|string|max:255',
        ]);

        $task->logTimeSpent($validated['hours'], $validated['description']);

        return redirect()->back()
            ->with('success', __('ui.time_logged_successfully', [
                'hours' => $validated['hours']
            ]));
    }

    /**
     * Получить рекомендации по размеру на основе оценки
     */
    public function getSizeRecommendation(Request $request)
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
     * Статистика по размерам задач
     */
    public function sizeStats()
    {
        $sizes = TaskSize::active()->ordered()->get();

        $stats = $sizes->map(function ($size) {
            return [
                'size' => $size,
                'stats' => $size->getUsageStats(),
            ];
        });

        return view('tasks.size-stats', compact('stats'));
    }

    public function addAssignee(Request $request, Task $task)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:assignee,observer,reviewer,manager'
        ]);

        // Проверяем дублирование
        $exists = $task->users()
            ->where('user_id', $validated['user_id'])
            ->where('role', $validated['role'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => __('ui.user_already_assigned')
            ], 422);
        }

        $task->assignUser(
            User::find($validated['user_id']),
            $validated['role']
        );

        return response()->json([
            'success' => true,
            'message' => __('ui.assignee_added_successfully')
        ]);
    }

    public function removeAssignee(Task $task, User $user)
    {
        $task->unassignUser($user);

        return response()->json([
            'success' => true,
            'message' => __('ui.assignee_removed_successfully')
        ]);
    }

    /**
     * Отметить задачу как выполненную
     */
    public function markCompleted(Task $task)
    {
        $task->markAsCompleted(auth()->user());

        return redirect()->back()
            ->with('success', __('ui.task_marked_completed'));
    }

    /**
     * Переоткрыть задачу
     */
    public function reopen(Task $task)
    {
        $task->update(['completed_date' => null]);

        return redirect()->back()
            ->with('success', __('ui.task_reopened'));
    }
}