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
        'name',
        'description',
        'status_id',
        'project_id',
        'd_start',
        'd_end',
        'd_close'
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

                TaskHistory::create([
                    'task_id' => $task->id,
                    'user_id' => auth()->id(),
                    'field' => $field,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'changed_at' => now(),
                ]);
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

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'field' => 'created',
            'old_value' => null,
            'new_value' => 'Задача создана',
            'changed_at' => now(),
        ]);
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        if (!auth()->check()) {
            return;
        }

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'field' => 'deleted',
            'old_value' => 'активна',
            'new_value' => 'удалена',
            'changed_at' => now(),
        ]);
    }

    /**
     * Handle the Task "restored" event.
     */
    public function restored(Task $task): void
    {
        if (!auth()->check()) {
            return;
        }

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'field' => 'restored',
            'old_value' => 'удалена',
            'new_value' => 'восстановлена',
            'changed_at' => now(),
        ]);
    }

    /**
     * Форматирование значений для читаемости
     */
    protected function formatValue(string $field, $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        switch ($field) {
            case 'status_id':
                $status = \App\Models\Status::find($value);
                return $status ? $status->name : "ID: {$value}";

            case 'project_id':
                $project = \App\Models\Project::find($value);
                return $project ? $project->name : "ID: {$value}";

            case 'd_start':
            case 'd_end':
            case 'd_close':
                return $value ? \Carbon\Carbon::parse($value)->format('d.m.Y H:i') : null;

            default:
                return (string) $value;
        }
    }
}