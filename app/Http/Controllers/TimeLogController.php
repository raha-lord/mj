<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Http\Requests\LogTimeRequest;
use App\Models\Task;
use App\Models\TaskTimeLog;
use App\Services\TimeLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimeLogController extends Controller
{
    protected TimeLogService $timeLogService;

    public function __construct(TimeLogService $timeLogService)
    {
        $this->timeLogService = $timeLogService;
    }

    /**
     * Записать время для задачи
     */
    public function store(LogTimeRequest $request, Task $task): JsonResponse
    {
        $validated = $request->validated();

        $timeLog = $this->timeLogService->logTime(
            $task,
            $validated['hours'],
            $validated['description'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => __('ui.time_logged_successfully', ['hours' => $validated['hours']]),
            'data' => [
                'time_log' => [
                    'id' => $timeLog->id,
                    'hours' => $timeLog->hours,
                    'description' => $timeLog->description,
                    'user' => $timeLog->user->name,
                    'created_at' => $timeLog->created_at,
                ],
                'task' => [
                    'actual_hours' => $task->fresh()->actual_hours,
                ]
            ]
        ]);
    }

    /**
     * Получить записи времени для задачи
     */
    public function index(Request $request, Task $task): JsonResponse
    {
        $includeTrashed = $request->boolean('include_deleted', false);

        $timeLogs = $this->timeLogService->getTaskTimeLogs($task, $includeTrashed);
        $stats = $this->timeLogService->getTaskTimeStats($task);

        return response()->json([
            'success' => true,
            'data' => [
                'time_logs' => $timeLogs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'hours' => $log->hours,
                        'description' => $log->description,
                        'user' => [
                            'id' => $log->user->id,
                            'name' => $log->user->name,
                        ],
                        'created_at' => $log->created_at,
                        'is_deleted' => !is_null($log->deleted_at),
                        'deleted_at' => $log->deleted_at,
                        'can_edit' => $log->user_id === auth()->id(),
                    ];
                }),
                'stats' => $stats,
            ]
        ]);
    }

    /**
     * Обновить запись времени
     */
    public function update(LogTimeRequest $request, TaskTimeLog $timeLog): JsonResponse
    {
        if ($timeLog->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => __('ui.access_denied')
            ], 403);
        }

        $this->timeLogService->updateTimeLog($timeLog, $request->validated());

        return response()->json([
            'success' => true,
            'message' => __('ui.time_log_updated_successfully'),
            'data' => [
                'time_log' => [
                    'id' => $timeLog->id,
                    'hours' => $timeLog->hours,
                    'description' => $timeLog->description,
                    'updated_at' => $timeLog->updated_at,
                ]
            ]
        ]);
    }

    /**
     * Мягко удалить запись времени
     */
    public function destroy(TaskTimeLog $timeLog): JsonResponse
    {
        if ($timeLog->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => __('ui.access_denied')
            ], 403);
        }

        $this->timeLogService->deleteTimeLog($timeLog);

        return response()->json([
            'success' => true,
            'message' => __('ui.time_log_deleted_successfully'),
        ]);
    }

    /**
     * Восстановить запись времени
     */
    public function restore(int $timeLogId): JsonResponse
    {
        $timeLog = TaskTimeLog::withTrashed()->findOrFail($timeLogId);

        if ($timeLog->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => __('ui.access_denied')
            ], 403);
        }

        if (!$timeLog->trashed()) {
            return response()->json([
                'success' => false,
                'message' => __('ui.time_log_not_deleted')
            ], 422);
        }

        $this->timeLogService->restoreTimeLog($timeLogId);

        return response()->json([
            'success' => true,
            'message' => __('ui.time_log_restored_successfully'),
        ]);
    }

    /**
     * Получить удаленные записи времени
     */
    public function deleted(Task $task): JsonResponse
    {
        $deletedLogs = $this->timeLogService->getDeletedTimeLogs($task);

        return response()->json([
            'success' => true,
            'data' => [
                'deleted_time_logs' => $deletedLogs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'hours' => $log->hours,
                        'description' => $log->description,
                        'user' => $log->user->name,
                        'created_at' => $log->created_at,
                        'deleted_at' => $log->deleted_at,
                        'can_restore' => $log->user_id === auth()->id(),
                    ];
                }),
                'count' => $deletedLogs->count(),
            ]
        ]);
    }
}
