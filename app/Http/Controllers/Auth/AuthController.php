<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use App\Services\OrganizationContextService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    protected OrganizationContextService $contextService;

    public function __construct(OrganizationContextService $contextService)
    {
        $this->contextService = $contextService;
    }
    /**
     * Показать форму входа
     */
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Обработать попытку входа
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Проверяем, есть ли у пользователя организации
            $user = Auth::user();
            $organizations = $user->organizations()->where('is_active', true)->get();
            
            // Если нет активных организаций, перенаправляем на страницу создания организации
            if ($organizations->isEmpty()) {
                return redirect()->route('organizations.select')
                    ->with('message', 'Добро пожаловать! Сначала выберите или создайте организацию.');
            }

            // Если есть только одна организация, автоматически устанавливаем её как текущую
            if ($organizations->count() === 1) {
                $this->contextService->setCurrentOrganization($user, $organizations->first(), $request);
            }

            return redirect()->intended(route('dashboard', absolute: false));
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Выход из системы
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Показать форму регистрации
     */
    public function showRegister(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Обработать регистрацию пользователя
     */
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Создаем пользователя
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Показать форму восстановления пароля
     */
    public function showForgotPassword(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    /**
     * Обработать запрос на восстановление пароля
     */
    public function forgotPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // TODO: Implement password reset logic
        // For now, just return success message
        return redirect()->back()->with('status', 'Ссылка для восстановления пароля отправлена на ваш email.');
    }
}