<?php

namespace App\Http\Controllers;

use App\Services\UserInvitationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class SetPasswordController extends Controller
{
    protected UserInvitationService $invitationService;

    public function __construct(UserInvitationService $invitationService)
    {
        $this->invitationService = $invitationService;
    }

    /**
     * Показать форму установки пароля
     */
    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        // Если пароль уже установлен, редиректим на главную
        if ($user && $user->hasPassword()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/SetPassword', [
            'user' => $user ? [
                'name' => $user->name,
                'email' => $user->email,
            ] : null,
        ]);
    }

    /**
     * Установить пароль
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Проверяем что пользователь авторизован
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        // Проверяем что пароль еще не установлен
        if ($user->hasPassword()) {
            return response()->json([
                'message' => 'Password already set',
                'redirect' => route('dashboard'),
            ], 422);
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        try {
            $this->invitationService->setFirstTimePassword($user, $validated['password']);

            return response()->json([
                'message' => 'Password set successfully',
                'redirect' => route('dashboard'),
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Проверить статус пароля (API endpoint)
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'has_password' => false,
                'needs_setup' => true,
            ]);
        }

        return response()->json([
            'has_password' => $user->hasPassword(),
            'needs_setup' => !$user->hasPassword(),
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}
