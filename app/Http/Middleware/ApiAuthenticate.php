<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем аутентификацию
        if (!Auth::check()) {
            // Для API запросов возвращаем JSON ошибку
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated.'
                ], 401);
            }
            
            // Для обычных запросов перенаправляем на логин
            return redirect()->guest(route('login'));
        }

        return $next($request);
    }
}