<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LogTimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Проверяем, может ли пользователь логировать время для этой задачи
        $task = $this->route('task');
        return $task && (
                auth()->user()->assignedTasks()->where('tasks.id', $task->id)->exists() ||
                auth()->user()->can('manage-tasks')
            );
    }

    public function rules(): array
    {
        return [
            'hours' => 'required|numeric|min:0.25|max:24',
            'description' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'hours.min' => __('validation.min_hours_log'),
            'hours.max' => __('validation.max_hours_per_day'),
        ];
    }
}

