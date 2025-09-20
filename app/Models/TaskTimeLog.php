<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskTimeLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'task_time_logs';


    protected $fillable = [
        'task_id',
        'user_id',
        'hours',
        'description',
    ];

    protected $casts = [
        'hours' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}