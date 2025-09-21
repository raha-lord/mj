<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserHasOrganization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем только для аутентифицированных пользователей
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Исключаем некоторые маршруты
        $excludedRoutes = [
            'organization-selection',
            'set-password',
            'password.*',
            'profile.*',
            'user.*',
            'logout',
            'login',
            'register',
            'verification.*',
            'password.*',
            'invitations.*', // API маршруты для приглашений
        ];

        // Проверяем текущий маршрут
        $currentRoute = $request->route()?->getName();
        $currentPath = $request->path();

        // Если это исключенный маршрут, пропускаем
        foreach ($excludedRoutes as $pattern) {
            if ($currentRoute && fnmatch($pattern, $currentRoute)) {
                return $next($request);
            }
            if (fnmatch($pattern, $currentPath)) {
                return $next($request);
            }
        }

        // Исключаем API маршруты для приглашений
        if ($request->is('api/invitations/*')) {
            return $next($request);
        }
        
        // Исключаем другие API маршруты которые не требуют организации
        if ($request->is('api/*') && (
            $request->is('api/organizations*') || 
            $request->is('api/users/search') || 
            $request->is('api/users/check-email') ||
            $request->is('api/set-password*') ||
            $request->is('api/password-status')
        )) {
            return $next($request);
        }

        // Проверяем нужно ли установить пароль
        if (!$user->hasPassword()) {
            if (!$request->is('set-password') && $currentRoute !== 'set-password') {
                return redirect()->route('set-password');
            }
            return $next($request);
        }

        // Проверяем есть ли у пользователя организации
        if ($user->organizations()->count() === 0) {
            // Если это не страница выбора организации, перенаправляем туда
            if ($currentRoute !== 'organization-selection' && !$request->is('organization-selection')) {
                return redirect()->route('organization-selection');
            }
        }

        return $next($request);
    }
}
