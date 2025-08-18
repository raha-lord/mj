<?php
// app/Http/Requests/StoreTaskRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // или проверка прав доступа
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'size_id' => 'nullable|exists:task_sizes,id',
            'estimated_hours' => 'nullable|numeric|min:0.25|max:1000',
            'status_id' => 'required|exists:statuses,id',
            'project_id' => 'nullable|exists:projects,id',
            'd_start' => 'nullable|date',
            'd_end' => 'nullable|date|after_or_equal:d_start',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('validation.task_name_required'),
            'd_end.after_or_equal' => __('validation.end_date_after_start'),
            'estimated_hours.min' => __('validation.min_hours'),
        ];
    }
}
