<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskTimeLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TimeLogService
{
    /**
     * Записать время для задачи
     */
    public function logTime(Task $task, float $hours, ?string $description = null, ?User $user = null): TaskTimeLog
    {
        $user = $user ?? auth()->user();

        $timeLog = TaskTimeLog::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'hours' => $hours,
            'description' => $description,
        ]);

        $this->updateTaskActualHours($task);

        return $timeLog;
    }

    /**
     * Получить записи времени для задачи
     */
    public function getTaskTimeLogs(Task $task, bool $includeTrashed = false): Collection
    {
        $query = $task->timeLogs()->with('user')->orderBy('created_at', 'desc');

        if ($includeTrashed) {
            $query->withTrashed();
        }

        return $query->get();
    }

    /**
     * Мягко удалить запись времени
     */
    public function deleteTimeLog(TaskTimeLog $timeLog): bool
    {
        $task = $timeLog->task;
        $deleted = $timeLog->delete();

        if ($deleted) {
            $this->updateTaskActualHours($task);
        }

        return $deleted;
    }

    /**
     * Восстановить запись времени
     */
    public function restoreTimeLog(int $timeLogId): bool
    {
        $timeLog = TaskTimeLog::withTrashed()->findOrFail($timeLogId);
        $restored = $timeLog->restore();

        if ($restored) {
            $this->updateTaskActualHours($timeLog->task);
        }

        return $restored;
    }

    /**
     * Обновить запись времени
     */
    public function updateTimeLog(TaskTimeLog $timeLog, array $data): bool
    {
        $updated = $timeLog->update($data);

        if ($updated && isset($data['hours'])) {
            $this->updateTaskActualHours($timeLog->task);
        }

        return $updated;
    }

    /**
     * Получить статистику времени по задаче
     */
    public function getTaskTimeStats(Task $task): array
    {
        $timeLogs = $task->timeLogs; // Только активные

        return [
            'total_hours' => $timeLogs->sum('hours'),
            'entries_count' => $timeLogs->count(),
            'users_count' => $timeLogs->pluck('user_id')->unique()->count(),
            'last_logged_at' => $timeLogs->max('created_at'),
            'by_user' => $timeLogs->groupBy('user_id')->map(function ($logs) {
                return [
                    'user' => $logs->first()->user->name,
                    'hours' => $logs->sum('hours'),
                    'entries' => $logs->count(),
                ];
            })->values(),
        ];
    }

    /**
     * Получить удаленные записи времени
     */
    public function getDeletedTimeLogs(Task $task): Collection
    {
        return $task->timeLogs()->onlyTrashed()->with('user')->orderBy('deleted_at', 'desc')->get();
    }

    /**
     * Пересчитать общее время задачи
     */
    protected function updateTaskActualHours(Task $task): void
    {
        $totalHours = $task->timeLogs()->sum('hours');
        $task->update(['actual_hours' => $totalHours]);
    }
}