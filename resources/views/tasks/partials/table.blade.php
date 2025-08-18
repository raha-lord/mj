<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
        <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-16">
                #
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                {{ __('ui.name') }}
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-32">
                {{ __('ui.project') }}
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-28">
                {{ __('ui.status') }}
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-28">
                {{ __('ui.priority') }}
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-20">
                {{ __('ui.size') }}
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-32">
                {{ __('ui.due_date') }}
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-32">
                {{ __('ui.assignees') }}
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-24">
                {{ __('ui.actions') }}
            </th>
        </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
        @forelse($tasks as $task)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                    {{ $loop->iteration + ($tasks->currentPage() - 1) * $tasks->perPage() }}
                </td>
                <td class="px-6 py-4">
                    <div class="flex flex-col">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ Str::limit($task->name, 50) }}
                        </div>
                        @if($task->description)
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ Str::limit($task->description, 80) }}
                            </div>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($task->project)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ $task->project->name }}
                            </span>
                    @else
                        <span class="text-gray-400 dark:text-gray-500">—</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($task->status)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              style="background-color: {{ $task->status->color }}20; color: {{ $task->status->color }};">
                                {{ $task->status->name }}
                            </span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                     @if($task->priority == 'urgent') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                     @elseif($task->priority == 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                     @elseif($task->priority == 'normal') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                     @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif">
                            {{ __('ui.' . $task->priority) }}
                        </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($task->size)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                {{ $task->size->code }}
                            </span>
                    @else
                        <span class="text-gray-400 dark:text-gray-500">—</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                    @if($task->due_date)
                        <div class="flex flex-col">
                                <span class="{{ $task->isOverdue() ? 'text-red-600 font-medium' : 'text-gray-900 dark:text-white' }}">
                                    {{ $task->due_date->format('d.m.Y') }}
                                </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $task->due_date->diffForHumans() }}
                                </span>
                        </div>
                    @else
                        <span class="text-gray-400 dark:text-gray-500">—</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($task->assignees && $task->assignees->count() > 0)
                        <div class="flex -space-x-1 overflow-hidden">
                            @foreach($task->assignees->take(3) as $assignee)
                                <div class="inline-block h-6 w-6 rounded-full bg-blue-500 text-white text-xs flex items-center justify-center font-medium"
                                     title="{{ $assignee->name }}">
                                    {{ substr($assignee->name, 0, 1) }}
                                </div>
                            @endforeach
                            @if($task->assignees->count() > 3)
                                <div class="inline-block h-6 w-6 rounded-full bg-gray-500 text-white text-xs flex items-center justify-center font-medium">
                                    +{{ $task->assignees->count() - 3 }}
                                </div>
                            @endif
                        </div>
                    @else
                        <span class="text-gray-400 dark:text-gray-500">{{ __('ui.unassigned') }}</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                    <a href="{{ route('tasks.show', $task) }}"
                       class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 inline-block"
                       title="{{ __('ui.view') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </a>
                    <a href="{{ route('tasks.edit', $task) }}"
                       class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 inline-block"
                       title="{{ __('ui.edit') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>
                    <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 inline-block"
                                title="{{ __('ui.delete') }}"
                                onclick="return confirm('{{ __('ui.confirm_delete_task') }}')">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0716.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="px-6 py-12 text-center">
                    <div class="text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <h3 class="text-sm font-medium mb-2">{{ __('ui.no_tasks_found') }}</h3>
                        <p class="text-sm">{{ __('ui.create_first_task_hint') }}</p>
                        <button onclick="openCreateModal()"
                                class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
                            {{ __('ui.create_task') }}
                        </button>
                    </div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<!-- Пагинация -->
@if($tasks->hasPages())
    <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
        {{ $tasks->appends(request()->query())->links() }}
    </div>
@endif