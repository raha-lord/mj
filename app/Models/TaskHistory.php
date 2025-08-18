<?php
// app/Models/TaskHistory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskHistory extends Model
{
    protected $table = 'audit.task_history';

    public $timestamps = false;

    protected $fillable = [
        'task_id',
        'user_id',
        'field',
        'old_value',
        'new_value',
        'metadata',
        'changed_at'
    ];

    protected $casts = [
        'changed_at' => 'datetime',
        'metadata' => 'json',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function logChange(Task $task, User $user, string $field, $oldValue, $newValue, ?array $metadata = null)
    {
        return static::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'field' => $field,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'metadata' => $metadata,
            'changed_at' => now(),
        ]);
    }
}