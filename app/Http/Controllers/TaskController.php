<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogTimeRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskAssignmentService;
use App\Services\TaskService;
use App\Services\TimeLogService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    protected TaskService $taskService;
    protected TaskAssignmentService $assignmentService;

    protected TimeLogService $timeLogService;
    public function __construct(TaskService $taskService, TaskAssignmentService $assignmentService,     TimeLogService $timeLogService)
    {
        $this->taskService = $taskService;
        $this->assignmentService = $assignmentService;
        $this->timeLogService = $timeLogService;
    }

    public function index(Request $request)
    {
        $tasks = $this->taskService->getFilteredTasks($request, 25);
        $filterData = $this->taskService->getFilterData();

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks->items(),
            'pagination' => [
                'current_page' => $tasks->currentPage(),
                'per_page' => $tasks->perPage(), 
                'total' => $tasks->total(),
                'last_page' => $tasks->lastPage()
            ],
            'statuses' => $filterData['statuses'],
            'projects' => $filterData['projects'],
            'users' => $filterData['users'],
        ]);
    }

    public function indexVue(Request $request)
    {
        $filterData = $this->taskService->getFilterData();

        return view('tasks.index-vue', $filterData);
    }

    public function create()
    {
        $filterData = $this->taskService->getFilterData();

        return view('tasks.create', $filterData);
    }

    public function store(StoreTaskRequest $request)
    {
        $task = $this->taskService->createTask($request->validated());

        return redirect()->route('tasks.index')
            ->with('success', __('ui.task_created_successfully'));
    }

    public function show(Task $task)
    {
        $task->load([
            'status', 'project', 'size', 'users',
            'history.user', 'createdBy', 'updatedBy', 'timeLogs.user'
        ]);

        $timeStats = $this->taskService->getTaskStats($task);
        $timeLogStats = $this->timeLogService->getTaskTimeStats($task);
        $recommendedSize = $task->getRecommendedSize();

        return view('tasks.show', compact('task', 'timeStats', 'timeLogStats', 'recommendedSize'));
    }

    public function edit(Task $task)
    {
        $task->load('users');
        $filterData = $this->taskService->getFilterData();

        return view('tasks.edit', array_merge(compact('task'), $filterData));
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->taskService->updateTask($task, $request->validated());

        return redirect()->route('tasks.index')
            ->with('success', __('ui.task_updated_successfully'));
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', __('ui.task_deleted_successfully'));
    }

    // API методы перенесены в TaskApiController
    public function logTime(LogTimeRequest $request, Task $task)
    {
        $validated = $request->validated();

        $this->timeLogService->logTime(
            $task,
            $validated['hours'],
            $validated['description'] ?? null
        );

        return redirect()->back()
            ->with('success', __('ui.time_logged_successfully', [
                'hours' => $validated['hours']
            ]));
    }
    public function addAssignee(Request $request, Task $task)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:assignee,observer,reviewer,manager'
        ]);

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

    public function removeAssignee(Task $task, User $user)
    {
        $this->assignmentService->removeAssignee($task, $user);

        return response()->json([
            'success' => true,
            'message' => __('ui.assignee_removed_successfully')
        ]);
    }

    public function markCompleted(Task $task)
    {
        $task->markAsCompleted(auth()->user());

        return redirect()->back()
            ->with('success', __('ui.task_marked_completed'));
    }

    public function reopen(Task $task)
    {
        $task->update(['completed_date' => null]);

        return redirect()->back()
            ->with('success', __('ui.task_reopened'));
    }
}