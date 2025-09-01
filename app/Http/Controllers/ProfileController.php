<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile information.
     */
    public function show(): Response
    {
        $user = Auth::user();
        
        // Get user statistics
        $stats = [
            'total_tasks' => $user->tasks()->count(),
            'active_tasks' => $user->tasks()->whereHas('status', function($q) {
                $q->where('is_final', false);
            })->count(),
            'completed_tasks' => $user->tasks()->whereHas('status', function($q) {
                $q->where('is_final', true);
            })->count(),
        ];

        // Get recent activity (last 10 activities)
        $recentActivity = $user->tasks()
            ->with(['status', 'project'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function($task) {
                return [
                    'id' => $task->id,
                    'type' => 'task_updated',
                    'description' => "Задача \"{$task->name}\" в проекте \"{$task->project->name}\"",
                    'created_at' => $task->updated_at,
                ];
            });

        return Inertia::render('Profile/Show', [
            'user' => $user,
            'stats' => $stats,
            'recentActivity' => $recentActivity
        ]);
    }

    /**
     * Show the form for editing the user's profile.
     */
    public function edit(): Response
    {
        return Inertia::render('Profile/Edit', [
            'user' => Auth::user(),
            'mustVerifyEmail' => Auth::user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->back()->with('success', 'Профиль успешно обновлен!');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->back()->with('success', 'Пароль успешно изменен!');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Аккаунт успешно удален.');
    }
}