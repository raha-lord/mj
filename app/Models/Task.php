<?php
// app/Models/Task.php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes, HasAuditFields;

    protected $table = 'tasks_management.tasks';

    protected $fillable = [
        'name',
        'description',
        'priority',
        'size_id',
        'start_date',
        'due_date',
        'completed_date',
        'actual_hours',
        'estimated_hours',
        'status_id',
        'project_id'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'due_date' => 'datetime',
        'completed_date' => 'datetime',
        'actual_hours' => 'decimal:2',
        'estimated_hours' => 'decimal:2',
    ];

    // ==========================================
    // ОТНОШЕНИЯ (BelongsTo - единственное число)
    // ==========================================

    /**
     * Статус задачи (одна задача -> один статус)
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Проект задачи (одна задача -> один проект)
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Размер задачи (одна задача -> один размер)
     */
    public function size(): BelongsTo
    {
        return $this->belongsTo(TaskSize::class);
    }

    // ==========================================
    // ОТНОШЕНИЯ (BelongsToMany/HasMany - множественное число)
    // ==========================================

    /**
     * Пользователи назначенные на задачу (многие ко многим)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tasks_management.task_user')
            ->withPivot(['role', 'assigned_at', 'completed_at', 'notes'])
            ->whereNull('tasks_management.task_user.deleted_at')
            ->withTimestamps();
    }

    /**
     * История изменений задачи (одна задача -> много записей истории)
     */
    public function history(): HasMany
    {
        return $this->hasMany(TaskHistory::class)->orderBy('changed_at', 'desc');
    }

    // ==========================================
    // МЕТОДЫ ДЛЯ РОЛЕЙ ПОЛЬЗОВАТЕЛЕЙ
    // ==========================================

    /**
     * Исполнители задачи
     */
    public function assignees(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'assignee');
    }

    /**
     * Наблюдатели задачи
     */
    public function observers(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'observer');
    }

    /**
     * Рецензенты задачи
     */
    public function reviewers(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'reviewer');
    }

    /**
     * Менеджеры задачи
     */
    public function managers(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'manager');
    }

    // ==========================================
    // SCOPES (области запросов)
    // ==========================================

    /**
     * Фильтр по приоритету
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Фильтр по статусу (по slug статуса)
     */
    public function scopeByStatus($query, $statusSlug)
    {
        return $query->whereHas('status', function ($q) use ($statusSlug) {
            $q->where('slug', $statusSlug);
        });
    }

    /**
     * Фильтр по размеру (по коду размера)
     */
    public function scopeBySize($query, $sizeCode)
    {
        return $query->whereHas('size', function ($q) use ($sizeCode) {
            $q->where('code', $sizeCode);
        });
    }

    /**
     * Просроченные задачи
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNull('completed_date');
    }

    /**
     * Завершенные задачи
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_date');
    }

    /**
     * Задачи назначенные пользователю
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->whereHas('users', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('role', 'assignee');
        });
    }

    /**
     * Фильтр по времени (оценка)
     */
    public function scopeWithEstimatedTime($query, $minHours = null, $maxHours = null)
    {
        if ($minHours) {
            $query->where('estimated_hours', '>=', $minHours);
        }

        if ($maxHours) {
            $query->where('estimated_hours', '<=', $maxHours);
        }

        return $query;
    }

    // ==========================================
    // МЕТОДЫ БИЗНЕС-ЛОГИКИ
    // ==========================================

    /**
     * Проверка - просрочена ли задача
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date < now() && !$this->completed_date;
    }

    /**
     * Проверка - завершена ли задача
     */
    public function isCompleted(): bool
    {
        return !is_null($this->completed_date);
    }

    /**
     * Получить цвет приоритета для UI
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'green',
            'normal' => 'blue',
            'high' => 'orange',
            'urgent' => 'red',
            default => 'gray'
        };
    }

    /**
     * Получить рекомендуемый размер на основе оценки времени
     */
    public function getRecommendedSize(): ?TaskSize
    {
        if (!$this->estimated_hours) {
            return null;
        }

        return TaskSize::active()
            ->where('min_hours', '<=', $this->estimated_hours)
            ->where(function ($query) {
                $query->where('max_hours', '>=', $this->estimated_hours)
                    ->orWhereNull('max_hours');
            })
            ->ordered()
            ->first();
    }

    /**
     * Проверить, соответствует ли фактическое время размеру
     */
    public function isTimeAccurate(): ?bool
    {
        if (!$this->size || !$this->actual_hours) {
            return null;
        }

        return $this->size->matchesHours($this->actual_hours);
    }

    /**
     * Получить отклонение от оценки (в процентах)
     */
    public function getEstimationAccuracy(): ?float
    {
        if (!$this->estimated_hours || !$this->actual_hours) {
            return null;
        }

        $deviation = abs($this->actual_hours - $this->estimated_hours);
        return round((1 - ($deviation / $this->estimated_hours)) * 100, 1);
    }

    /**
     * Получить прогресс по времени (в процентах)
     */
    public function getTimeProgressAttribute(): ?float
    {
        if (!$this->estimated_hours || !$this->actual_hours) {
            return null;
        }

        return min(round(($this->actual_hours / $this->estimated_hours) * 100, 1), 100);
    }

    /**
     * Проверить, превышен ли лимит времени
     */
    public function isOverBudget(): bool
    {
        if (!$this->estimated_hours || !$this->actual_hours) {
            return false;
        }

        return $this->actual_hours > $this->estimated_hours;
    }

    /**
     * Назначить пользователя на задачу
     */
    public function assignUser(User $user, string $role = 'assignee', ?string $notes = null)
    {
        return $this->users()->attach($user->id, [
            'role' => $role,
            'assigned_at' => now(),
            'notes' => $notes,
        ]);
    }

    /**
     * Отстранить пользователя от задачи
     */
    public function unassignUser(User $user, ?string $role = null)
    {
        $query = $this->users()->where('user_id', $user->id);

        if ($role) {
            $query->wherePivot('role', $role);
        }

        return $query->detach();
    }

    /**
     * Отметить задачу как выполненную
     */
    public function markAsCompleted(User $completedBy = null)
    {
        $this->update(['completed_date' => now()]);

        if ($completedBy) {
            $this->users()->updateExistingPivot($completedBy->id, [
                'completed_at' => now()
            ]);
        }

        return $this;
    }

    /**
     * Обновить фактическое время
     */
    public function logTimeSpent(float $hours, string $description = null): void
    {
        $this->increment('actual_hours', $hours);

        // Опционально: логируем в историю
        if (auth()->check()) {
            TaskHistory::logChange(
                $this,
                auth()->user(),
                'time_logged',
                null,
                "+{$hours}ч" . ($description ? " ({$description})" : ''),
                ['hours_added' => $hours, 'description' => $description]
            );
        }
    }
}