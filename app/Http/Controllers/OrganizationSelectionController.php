<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class OrganizationSelectionController extends Controller
{
    /**
     * Показать страницу выбора организации для пользователей без организации
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Проверяем есть ли у пользователя организации
        $userOrganizations = $user->organizations()->count();
        
        if ($userOrganizations > 0) {
            // Если есть организации, перенаправляем на главную
            return redirect()->route('dashboard');
        }

        return Inertia::render('OrganizationSelection/Index');
    }
}
