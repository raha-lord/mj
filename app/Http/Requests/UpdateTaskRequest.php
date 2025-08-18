<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'size_id' => 'nullable|exists:task_sizes,id',
            'estimated_hours' => 'nullable|numeric|min:0.25|max:1000',
            'actual_hours' => 'nullable|numeric|min:0|max:1000',
            'status_id' => 'required|exists:statuses,id',
            'project_id' => 'nullable|exists:projects,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:d_start',
        ];
    }
}
