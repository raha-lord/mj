<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'task.projects';
    protected $fillable = [
        'slug', 'name', 'description', // и другие поля, которые у тебя есть в таблице
    ];
    use HasFactory;
}
