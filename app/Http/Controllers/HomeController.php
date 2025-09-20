<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Task;
use App\Models\Project;
use App\Services\OrganizationContextService;

class HomeController extends Controller
{
    protected OrganizationContextService $contextService;

    public function __construct(OrganizationContextService $contextService)
    {
        $this->contextService = $contextService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $currentOrganization = $this->contextService->getCurrentOrganization($user, $request);

        // Если нет текущей организации, показываем общую статистику
        if (!$currentOrganization) {
            return Inertia::render('Dashboard', [
                'stats' => [
                    'totalTasks' => 0,
                    'inProgress' => 0,
                    'completed' => 0,
                    'projects' => 0,
                ],
                'organization' => null,
                'message' => 'Выберите организацию для просмотра данных'
            ]);
        }

        // Получаем статистику для текущей организации
        $organizationProjects = Project::where('organization_id', $currentOrganization->id);
        $organizationTasks = Task::whereHas('project', function($query) use ($currentOrganization) {
            $query->where('organization_id', $currentOrganization->id);
        });

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalTasks' => $organizationTasks->count(),
                'inProgress' => $organizationTasks->where('status_id', function($query) {
                    $query->select('id')
                          ->from('statuses')
                          ->where('slug', 'in-progress')
                          ->limit(1);
                })->count(),
                'completed' => $organizationTasks->where('status_id', function($query) {
                    $query->select('id')
                          ->from('statuses')
                          ->where('slug', 'completed')
                          ->limit(1);
                })->count(),
                'projects' => $organizationProjects->count(),
            ],
            'organization' => [
                'id' => $currentOrganization->id,
                'name' => $currentOrganization->name,
                'description' => $currentOrganization->description,
            ],
            'contextData' => $this->contextService->getContextData($user, $request)
        ]);
    }
}
