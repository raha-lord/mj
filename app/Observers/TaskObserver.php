<?php
// app/Observers/TaskObserver.php

namespace App\Observers;

use App\Models\Task;
use App\Models\TaskHistory;

class TaskObserver
{
    /**
     * Отслеживаемые поля для логирования
     */
    protected array $trackedFields = [
        'name',                // Название задачи
        'description',         // Описание задачи
        'priority',           // Приоритет (low, normal, high, urgent)
        'status_id',          // ID статуса
        'project_id',         // ID проекта
        'size_id',            // ID размера задачи
        'estimated_hours',    // Оценка времени
        'start_date',         // Дата начала
        'due_date',           // Дедлайн
        'completed_date',     // Дата завершения
    ];

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        if (!auth()->check()) {
            return;
        }

        $changes = $task->getChanges();
        $original = $task->getOriginal();

        foreach ($this->trackedFields as $field) {
            if (array_key_exists($field, $changes)) {
                $oldValue = $this->formatValue($field, $original[$field] ?? null);
                $newValue = $this->formatValue($field, $changes[$field]);

                // Используем статический метод из модели
                TaskHistory::logChange(
                    $task,
                    auth()->user(),
                    $field,
                    $oldValue,
                    $newValue,
                    [
                        'description' => $this->getChangeDescription($field, $original[$field] ?? null, $changes[$field]),
                        'raw_old_value' => $original[$field] ?? null,
                        'raw_new_value' => $changes[$field],
                    ]
                );
            }
        }
    }

    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        if (!auth()->check()) {
            return;
        }

        TaskHistory::logChange(
            $task,
            auth()->user(),
            'created',
            null,
            'Задача создана',
            [
                'description' => 'Задача создана',
                'task_name' => $task->name,
                'priority' => $task->priority,
                'status_id' => $task->status_id,
            ]
        );
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        if (!auth()->check()) {
            return;
        }

        TaskHistory::logChange(
            $task,
            auth()->user(),
            'deleted',
            'активна',
            'удалена',
            [
                'description' => 'Задача удалена',
                'task_name' => $task->name,
            ]
        );
    }

    /**
     * Handle the Task "restored" event.
     */
    public function restored(Task $task): void
    {
        if (!auth()->check()) {
            return;
        }

        TaskHistory::logChange(
            $task,
            auth()->user(),
            'restored',
            'удалена',
            'восстановлена',
            [
                'description' => 'Задача восстановлена',
                'task_name' => $task->name,
            ]
        );
    }

    /**
     * Получить описание изменения
     */
    protected function getChangeDescription(string $field, $oldValue, $newValue): string
    {
        return match($field) {
            'name' => 'Изменено название задачи',
            'description' => 'Изменено описание задачи',
            'priority' => 'Изменен приоритет',
            'status_id' => 'Изменен статус',
            'project_id' => 'Изменен проект',
            'size_id' => 'Изменен размер задачи',
            'estimated_hours' => 'Изменена оценка времени',
            'start_date' => 'Изменена дата начала',
            'due_date' => 'Изменен дедлайн',
            'completed_date' => $newValue ? 'Задача завершена' : 'Задача переоткрыта',
            default => "Изменено поле: {$field}",
        };
    }

    /**
     * Форматирование значений для читаемости
     */
    protected function formatValue(string $field, $value): ?string
    {
        if (is_null($value)) {
            return 'Не указано';
        }

        return match($field) {
            'status_id' => $this->getStatusName($value),
            'project_id' => $this->getProjectName($value),
            'size_id' => $this->getSizeName($value),
            'priority' => $this->getPriorityName($value),
            'start_date', 'due_date', 'completed_date' =>
            \Carbon\Carbon::parse($value)->format('d.m.Y H:i'),
            'estimated_hours' => $value . ' ч',
            default => (string) $value,
        };
    }

    /**
     * Получить название статуса по ID
     */
    protected function getStatusName($statusId): string
    {
        if (!$statusId) return 'Не указан';

        $status = \App\Models\Status::find($statusId);
        return $status ? $status->name : "ID: {$statusId}";
    }

    /**
     * Получить название проекта по ID
     */
    protected function getProjectName($projectId): string
    {
        if (!$projectId) return 'Не указан';

        $project = \App\Models\Project::find($projectId);
        return $project ? $project->name : "ID: {$projectId}";
    }

    /**
     * Получить название размера по ID
     */
    protected function getSizeName($sizeId): string
    {
        if (!$sizeId) return 'Не указан';

        $size = \App\Models\TaskSize::find($sizeId);
        return $size ? $size->name : "ID: {$sizeId}";
    }

    /**
     * Получить название приоритета
     */
    protected function getPriorityName($priority): string
    {
        return match($priority) {
            'low' => 'Низкий',
            'normal' => 'Обычный',
            'high' => 'Высокий',
            'urgent' => 'Срочный',
            default => ucfirst($priority),
        };
    }
}