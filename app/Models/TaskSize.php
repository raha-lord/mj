<?php
// app/Models/TaskSize.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskSize extends Model
{
    use SoftDeletes;

    protected $table = 'tasks_management.task_sizes';

    protected $fillable = [
        'code',
        'name',
        'description',
        'min_hours',
        'max_hours',
        'story_points',
        'color',
        'icon',
        'sort_order',
        'is_active',
        'metadata'
    ];

    protected $casts = [
        'min_hours' => 'integer',
        'max_hours' => 'integer',
        'story_points' => 'decimal:1',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'metadata' => 'json',
    ];

    /**
     * Задачи с этим размером
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'size_id');
    }

    /**
     * Scope для активных размеров
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для сортировки
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope по коду
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Получить примеры задач для этого размера
     */
    public function getExamplesAttribute(): array
    {
        return $this->metadata['examples'] ?? [];
    }

    /**
     * Получить уровень сложности
     */
    public function getComplexityAttribute(): ?string
    {
        return $this->metadata['complexity'] ?? null;
    }

    /**
     * Нужно ли разбивать задачу на подзадачи
     */
    public function shouldBeSplit(): bool
    {
        return $this->metadata['should_be_split'] ?? false;
    }

    /**
     * Получить диапазон времени в читаемом виде
     */
    public function getTimeRangeAttribute(): string
    {
        if (!$this->min_hours) {
            return 'Не определено';
        }

        if (!$this->max_hours) {
            return $this->min_hours . '+ ч';
        }

        if ($this->min_hours == $this->max_hours) {
            return $this->min_hours . ' ч';
        }

        return $this->min_hours . '-' . $this->max_hours . ' ч';
    }

    /**
     * Получить среднее время
     */
    public function getAverageHoursAttribute(): ?float
    {
        if (!$this->min_hours) {
            return null;
        }

        if (!$this->max_hours) {
            return $this->min_hours;
        }

        return ($this->min_hours + $this->max_hours) / 2;
    }

    /**
     * Проверить, подходит ли время под этот размер
     */
    public function matchesHours(float $hours): bool
    {
        if (!$this->min_hours) {
            return false;
        }

        if ($hours < $this->min_hours) {
            return false;
        }

        if ($this->max_hours && $hours > $this->max_hours) {
            return false;
        }

        return true;
    }

    /**
     * Статистика использования размера
     */
    public function getUsageStats(): array
    {
        $total = $this->tasks()->count();
        $completed = $this->tasks()->completed()->count();
        $avgActualHours = $this->tasks()->completed()->avg('actual_hours');

        return [
            'total_tasks' => $total,
            'completed_tasks' => $completed,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            'avg_actual_hours' => $avgActualHours ? round($avgActualHours, 1) : null,
        ];
    }
}
