<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'task.tasks';

    protected $fillable = [
        'name',
        'description',
        'd_start',
        'd_end',
        'd_close',
        'status_id',
        'project_id' // и другие поля, которые у тебя есть в таблице
    ];

    use HasFactory;
}
