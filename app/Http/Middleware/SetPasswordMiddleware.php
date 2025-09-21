<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPasswordMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Проверяет установлен ли пароль у пользователя.
     * Если нет - редиректит на страницу установки пароля.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Если пользователь не аутентифицирован, пропускаем
        if (!$user) {
            return $next($request);
        }

        // Если пароль уже установлен, пропускаем
        if ($user->hasPassword()) {
            return $next($request);
        }

        // Список маршрутов которые можно посещать без установленного пароля
        $allowedRoutes = [
            'set-password',
            'set-password.store',
            'logout',
            'password.*'
        ];

        $currentRoute = $request->route()->getName();

        // Если текущий маршрут в списке разрешенных, пропускаем
        foreach ($allowedRoutes as $pattern) {
            if ($this->matchesPattern($currentRoute, $pattern)) {
                return $next($request);
            }
        }

        // Если это API запрос, возвращаем JSON ошибку
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Password setup required',
                'redirect' => route('set-password'),
                'code' => 'PASSWORD_SETUP_REQUIRED'
            ], 403);
        }

        // Редиректим на страницу установки пароля
        return redirect()->route('set-password')
            ->with('info', 'Пожалуйста, установите пароль для вашего аккаунта');
    }

    /**
     * Проверить соответствует ли имя маршрута паттерну
     */
    protected function matchesPattern(?string $routeName, string $pattern): bool
    {
        if (!$routeName) {
            return false;
        }

        // Простая проверка на точное совпадение
        if ($routeName === $pattern) {
            return true;
        }

        // Проверка на wildcard паттерн (например: "password.*")
        if (str_ends_with($pattern, '*')) {
            $prefix = rtrim($pattern, '*');
            return str_starts_with($routeName, $prefix);
        }

        return false;
    }
}
