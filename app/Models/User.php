<?php
// app/Models/User.php

namespace App\Models;

use App\Traits\HasAuditFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasAuditFields;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Переопределяем boot метод из HasAuditFields для User
     * чтобы избежать циклических ссылок при создании первого пользователя
     */
    protected static function bootHasAuditFields()
    {
        static::creating(function ($model) {
            // Только если есть авторизованный пользователь
            if (auth()->check() && auth()->id() !== $model->id) {
                $model->created_by = auth()->id();
                $model->updated_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check() && auth()->id() !== $model->id) {
                $model->updated_by = auth()->id();
            }
        });

        static::deleting(function ($model) {
            if (auth()->check() && $model->isSoftDeleting() && auth()->id() !== $model->id) {
                $model->deleted_by = auth()->id();
                $model->save();
            }
        });
    }

    // Отношения к задачам
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'tasks_management.task_user')
            ->withPivot(['role', 'assigned_at', 'completed_at', 'notes'])
            ->whereNull('tasks_management.task_user.deleted_at')
            ->withTimestamps();
    }

    public function assignedTasks(): BelongsToMany
    {
        return $this->tasks()->wherePivot('role', 'assignee');
    }

    public function observedTasks(): BelongsToMany
    {
        return $this->tasks()->wherePivot('role', 'observer');
    }

    public function reviewTasks(): BelongsToMany
    {
        return $this->tasks()->wherePivot('role', 'reviewer');
    }

    public function managedTasks(): BelongsToMany
    {
        return $this->tasks()->wherePivot('role', 'manager');
    }

    // Задачи созданные пользователем
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    // Проекты созданные пользователем
    public function createdProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    // История активности
    public function taskHistory(): HasMany
    {
        return $this->hasMany(TaskHistory::class);
    }

    public function userActivity(): HasMany
    {
        return $this->hasMany(UserActivity::class);
    }

    /**
     * Получить активные задачи пользователя
     */
    public function getActiveTasks()
    {
        return $this->assignedTasks()
            ->whereNull('tasks_management.tasks.deleted_at')
            ->whereNull('tasks_management.tasks.completed_date')
            ->whereHas('statuses', function ($q) {
                $q->where('is_final', false);
            });
    }

    /**
     * Получить просроченные задачи пользователя
     */
    public function getOverdueTasks()
    {
        return $this->assignedTasks()
            ->whereNull('tasks_management.tasks.deleted_at')
            ->whereNull('tasks_management.tasks.completed_date')
            ->where('tasks_management.tasks.due_date', '<', now());
    }

    /**
     * Получить статистику задач пользователя
     */
    public function getTasksStats()
    {
        return [
            'total' => $this->assignedTasks()->count(),
            'active' => $this->getActiveTasks()->count(),
            'completed' => $this->assignedTasks()
                ->whereNotNull('tasks_management.tasks.completed_date')
                ->count(),
            'overdue' => $this->getOverdueTasks()->count(),
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}