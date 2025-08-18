<?php

namespace App\Http\Requests;

use App\Services\TaskAssignmentService;
use Illuminate\Foundation\Http\FormRequest;

class AddAssigneeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:assignee,observer,reviewer,manager'
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $task = $this->route('task');
            $userId = $this->input('user_id');
            $role = $this->input('role');

            if ($task && $userId && $role) {
                $user = \App\Models\User::find($userId);
                $assignmentService = app(TaskAssignmentService::class);

                if (!$assignmentService->canAssignUser($task, $user, $role)) {
                    $validator->errors()->add('user_id', __('validation.cannot_assign_user'));
                }
            }
        });
    }
}