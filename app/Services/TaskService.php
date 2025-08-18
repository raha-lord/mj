<?php
// app/Services/TaskService.php

namespace App\Services;

use App\Http\Filters\TaskFilter;
use App\Models\Task;
use App\Models\TaskSize;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService
{
    public function getFilteredTasks(Request $request, int $perPage = 25): LengthAwarePaginator
    {
        $query = Task::with(['status', 'project', 'size', 'assignees']);

        $filter = new TaskFilter($request);
        $query = $filter->apply($query);

        return $query->latest()->paginate($perPage);
    }

    public function createTask(array $data): Task
    {
        // Автоматический подбор размера если не указан
        if (!isset($data['size_id']) && isset($data['estimated_hours'])) {
            $data['size_id'] = $this->findRecommendedSize($data['estimated_hours'])?->id;
        }

        $task = Task::create($data);

        // Назначение исполнителей
        if (!empty($data['assignees'])) {
            $this->assignUsers($task, $data['assignees']);
        }

        return $task;
    }

    public function updateTask(Task $task, array $data): bool
    {
        // Автоматический подбор размера если не указан
        if (!isset($data['size_id']) && isset($data['estimated_hours'])) {
            $data['size_id'] = $this->findRecommendedSize($data['estimated_hours'])?->id;
        }

        // Сохраняем исполнителей отдельно
        $assignees = $data['assignees'] ?? null;
        unset($data['assignees']);

        $updated = $task->update($data);

        // Обновляем исполнителей
        if ($assignees !== null) {
            if (empty($assignees)) {
                // Убираем всех исполнителей
                $task->assignees()->detach();
            } else {
                // Синхронизируем исполнителей
                $task->assignees()->sync($assignees);
            }
        }

        return $updated;
    }

    protected function findRecommendedSize(float $estimatedHours): ?TaskSize
    {
        return TaskSize::active()
            ->where('min_hours', '<=', $estimatedHours)
            ->where(function ($query) use ($estimatedHours) {
                $query->where('max_hours', '>=', $estimatedHours)
                    ->orWhereNull('max_hours');
            })
            ->ordered()
            ->first();
    }

    protected function assignUsers(Task $task, array $userIds): void
    {
        foreach ($userIds as $userId) {
            $task->assignUser(\App\Models\User::find($userId));
        }
    }

    public function getTaskStats(Task $task): array
    {
        return [
            'estimation_accuracy' => $task->getEstimationAccuracy(),
            'is_over_budget' => $task->isOverBudget(),
            'time_progress' => $task->time_progress,
            'is_time_accurate' => $task->isTimeAccurate(),
            'recommended_size' => $task->getRecommendedSize(),
        ];
    }

    /**
     * Получить данные для фильтров
     */
    public function getFilterData(): array
    {
        return [
            'statuses' => \App\Models\Status::ordered()->get(['id', 'slug', 'name']),
            'projects' => \App\Models\Project::orderBy('name')->get(['id', 'name']),
            'sizes' => \App\Models\TaskSize::active()->ordered()->get(['id', 'code', 'name']),
            'users' => \App\Models\User::orderBy('name')->get(['id', 'name']),
            'priorities' => [
                'low' => __('ui.priority.low'),
                'normal' => __('ui.priority.normal'),
                'high' => __('ui.priority.high'),
                'urgent' => __('ui.priority.urgent'),
            ],
            'time_filters' => [
                'overdue' => __('ui.time_filter.overdue'),
                'over_budget' => __('ui.time_filter.over_budget'),
                'no_estimate' => __('ui.time_filter.no_estimate'),
                'completed_today' => __('ui.time_filter.completed_today'),
                'due_this_week' => __('ui.time_filter.due_this_week'),
                'long_running' => __('ui.time_filter.long_running'),
            ]
        ];
    }

    /**
     * Получить статистику по задачам
     */
    public function getDashboardStats(): array
    {
        return [
            'total' => Task::count(),
            'active' => Task::whereNull('completed_date')->count(),
            'completed_today' => Task::whereDate('completed_date', today())->count(),
            'overdue' => Task::overdue()->count(),
            'over_budget' => Task::whereRaw('actual_hours > estimated_hours')->count(),
            'no_estimate' => Task::whereNull('estimated_hours')->whereNull('completed_date')->count(),
        ];
    }

    /**
     * Экспорт задач
     */
    public function exportTasks(Request $request): \Illuminate\Support\Collection
    {
        $query = Task::with(['status', 'project', 'size', 'assignees']);

        $filter = new \App\Http\Filters\TaskFilter($request);
        $query = $filter->apply($query);

        return $query->get()->map(function ($task) {
            return [
                'ID' => $task->id,
                'Название' => $task->name,
                'Описание' => $task->description,
                'Приоритет' => $task->priority,
                'Статус' => $task->status?->name,
                'Проект' => $task->project?->name,
                'Размер' => $task->size?->name,
                'Оценка (ч)' => $task->estimated_hours,
                'Факт (ч)' => $task->actual_hours,
                'Исполнители' => $task->assignees->pluck('name')->join(', '),
                'Дата создания' => $task->created_at?->format('d.m.Y'),
                'Дата начала' => $task->start_date?->format('d.m.Y'),
                'Дедлайн' => $task->due_date?->format('d.m.Y'),
                'Дата завершения' => $task->completed_date?->format('d.m.Y'),
            ];
        });
    }

    /**
     * Клонировать задачу
     */
    public function cloneTask(Task $originalTask, array $overrides = []): Task
    {
        $data = array_merge(
            $originalTask->only([
                'name', 'description', 'priority', 'size_id',
                'estimated_hours', 'status_id', 'project_id'
            ]),
            $overrides
        );

        // Добавляем префикс к названию
        if (!isset($overrides['name'])) {
            $data['name'] = '[Копия] ' . $data['name'];
        }

        $newTask = Task::create($data);

        // Копируем назначения (если не переопределены)
        if (!isset($overrides['skip_assignees'])) {
            foreach ($originalTask->users as $user) {
                $newTask->assignUser($user, $user->pivot->role);
            }
        }

        return $newTask;
    }
}