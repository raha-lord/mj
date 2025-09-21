<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\OrganizationContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    protected OrganizationContextService $contextService;

    public function __construct(OrganizationContextService $contextService)
    {
        $this->contextService = $contextService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $currentOrganization = $this->contextService->getCurrentOrganization($user, $request);
        
        // Если нет текущей организации, редиректим на выбор организации
        if (!$currentOrganization) {
            return Inertia::render('Projects/Index', [
                'projects' => [],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => 25,
                    'total' => 0,
                ],
                'message' => 'Выберите организацию для просмотра проектов',
                'needsOrganization' => true,
            ]);
        }

        $search = $request->get('search');
        $status = $request->get('status');
        $visibility = $request->get('visibility');
        $perPage = $request->get('per_page', 25);

        $query = Project::withCount('tasks')
            ->forOrganization($currentOrganization->id)
            ->accessibleBy($user);

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                  ->orWhere('description', 'LIKE', "%$search%");
            });
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($visibility)) {
            $query->where('visibility', $visibility);
        }

        $projects = $query->latest()->paginate($perPage);

        // Добавляем роль пользователя в каждом проекте
        $projectsWithRoles = $projects->getCollection()->map(function ($project) use ($user) {
            $projectArray = $project->toArray();
            $projectArray['user_role'] = $project->getUserRole($user);
            $projectArray['can_manage'] = $project->canUserManage($user);
            return $projectArray;
        });

        return Inertia::render('Projects/Index', [
            'projects' => $projectsWithRoles,
            'pagination' => [
                'current_page' => $projects->currentPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
            ],
            'currentOrganization' => [
                'id' => $currentOrganization->id,
                'name' => $currentOrganization->name,
            ],
            'userPermissions' => [
                'can_create_projects' => $user->isSuperUser() || 
                    in_array($user->getOrganizationRole($currentOrganization->id), ['org_admin', 'project_manager'])
            ],
            'needsOrganization' => false,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $currentOrganization = $this->contextService->getCurrentOrganization($user, $request);
        
        // Проверяем наличие организации
        if (!$currentOrganization) {
            return redirect()->route('organizations.index')
                ->with('error', 'Сначала выберите организацию');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive,completed'],
            'visibility' => ['required', 'in:public,private'],
            'access_description' => ['nullable', 'string', 'max:1000'],
        ]);

        // Автоматически добавляем organization_id
        $validated['organization_id'] = $currentOrganization->id;

        Project::create($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Проект успешно создан в организации "' . $currentOrganization->name . '"!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();
        
        // Проверяем доступ к проекту
        if (!$project->isAccessibleBy($user)) {
            abort(403, 'У вас нет доступа к этому проекту');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive,completed'],
            'visibility' => ['sometimes', 'in:public,private'],
            'access_description' => ['nullable', 'string', 'max:1000'],
        ]);

        $project->update($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Проект успешно обновлен!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $user = request()->user();
        
        // Проверяем доступ к проекту
        if (!$project->isAccessibleBy($user)) {
            abort(403, 'У вас нет доступа к этому проекту');
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Проект успешно удален!');
    }
}
