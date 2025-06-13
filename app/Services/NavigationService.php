<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;

class NavigationService
{
    /**
     * Получить список навигационных роутов
     */
    public static function getNavigationRoutes(): array
    {
        $routes = [];
        $routeCollection = Route::getRoutes();

        // Роуты которые нужно показывать в меню
        $allowedRoutes = [
            'dashboard' => 'Главная',
            'project.index' => 'Проекты',
            'status.index' => 'Статусы',
            'task.index' => 'Задачи',
        ];

        foreach ($allowedRoutes as $routeName => $title) {
            if ($routeCollection->hasNamedRoute($routeName)) {
                $routes[] = [
                    'name' => $routeName,
                    'title' => $title,
                    'url' => route($routeName),
                ];
            }
        }

        return $routes;
    }

    /**
     * Проверить активен ли роут
     */
    public static function isActiveRoute(string $routeName): bool
    {
        // Для index роутов также проверяем другие действия (create, edit, show)
        $currentRoute = request()->route()->getName();

        if ($currentRoute === $routeName) {
            return true;
        }

        // Проверяем по префиксу для CRUD роутов
        $routePrefix = str_replace('.index', '', $routeName);
        return str_starts_with($currentRoute, $routePrefix . '.');
    }
}