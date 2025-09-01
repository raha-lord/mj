<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Status;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->get('search');
        $type = $request->get('type');
        $perPage = $request->get('per_page', 25);

        $query = Status::withCount('tasks');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                  ->orWhere('slug', 'LIKE', "%$search%");
            });
        }

        if (!empty($type)) {
            $query->where('type', $type);
        }

        $statuses = $query->latest()->paginate($perPage);

        return Inertia::render('Statuses/Index', [
            'statuses' => $statuses->items(),
            'pagination' => [
                'current_page' => $statuses->currentPage(),
                'per_page' => $statuses->perPage(),
                'total' => $statuses->total(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:statuses'],
            'type' => ['required', 'in:task,project'],
            'color' => ['nullable', 'string', 'max:50'],
            'is_final' => ['boolean']
        ]);

        Status::create($validated);

        return redirect()->route('statuses.index')
            ->with('success', 'Статус успешно создан!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Status $status): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('statuses')->ignore($status->id)],
            'type' => ['required', 'in:task,project'],
            'color' => ['nullable', 'string', 'max:50'],
            'is_final' => ['boolean']
        ]);

        $status->update($validated);

        return redirect()->route('statuses.index')
            ->with('success', 'Статус успешно обновлен!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Status $status): RedirectResponse
    {
        $status->delete();

        return redirect()->route('statuses.index')
            ->with('success', 'Статус успешно удален!');
    }
}
