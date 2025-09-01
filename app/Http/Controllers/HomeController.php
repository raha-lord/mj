<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Task;
use App\Models\Project;

class HomeController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'totalTasks' => Task::count(),
                'inProgress' => Task::where('status_id', function($query) {
                    $query->select('id')
                          ->from('statuses')
                          ->where('slug', 'in-progress')
                          ->limit(1);
                })->count(),
                'completed' => Task::where('status_id', function($query) {
                    $query->select('id')
                          ->from('statuses')
                          ->where('slug', 'completed')
                          ->limit(1);
                })->count(),
                'projects' => Project::count(),
            ]
        ]);
    }
}
