<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('ui.edit_task') }}
            </h2>
            <a href="{{ route('tasks.index') }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                {{ __('ui.back') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('flash_message'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('flash_message') }}
                </div>
            @endif

            <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Основная информация -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('ui.task_information') }}
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Название -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('ui.task_name') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name"
                                   value="{{ old('name', $task->name) }}"
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>

                        <!-- Описание -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('ui.description') }}
                            </label>
                            <textarea name="description" id="description" rows="4"
                                      class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description', $task->description) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Проект -->
                            <div>
                                <label for="project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('ui.projects') }}
                                </label>
                                <select name="project_id" id="project_id"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">{{ __('ui.select_project') }}</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}"
                                                {{ old('project_id', $task->project_id) == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Статус -->
                            <div>
                                <label for="status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('ui.statuses') }} <span class="text-red-500">*</span>
                                </label>
                                <select name="status_id" id="status_id" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}"
                                                {{ old('status_id', $task->status_id) == $status->id ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Дата начала -->
                            <div>
                                <label for="d_start" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('ui.start_date') }}
                                </label>
                                <input type="datetime-local" name="d_start" id="d_start"
                                       value="{{ old('d_start', $task->d_start?->format('Y-m-d\TH:i')) }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Дата окончания -->
                            <div>
                                <label for="d_end" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('ui.end_date') }}
                                </label>
                                <input type="datetime-local" name="d_end" id="d_end"
                                       value="{{ old('d_end', $task->d_end?->format('Y-m-d\TH:i')) }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Исполнители -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('ui.assignees') }}
                        </h3>
                    </div>
                    <div class="p-6">
                        <!-- Текущие исполнители -->
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('ui.current_assignees') }}
                            </h4>
                            <div class="space-y-2" id="current-assignees">
                                @forelse($task->users as $user)
                                    <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 p-3 rounded-lg assignee-item" data-user-id="{{ $user->id }}">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ __('ui.role') }}: {{ __('ui.' . $user->pivot->role) }}
                                                </p>
                                            </div>
                                        </div>
                                        <button type="button"
                                                onclick="removeAssignee({{ $user->id }})"
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500 dark:text-gray-400" id="no-assignees">{{ __('ui.no_assignees') }}</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Добавить исполнителя -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('ui.add_assignee') }}
                            </h4>
                            <div class="flex space-x-3">
                                <select id="new_assignee"
                                        class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">{{ __('ui.select_user') }}</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <select id="new_role"
                                        class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="assignee">{{ __('ui.assignee') }}</option>
                                    <option value="observer">{{ __('ui.observer') }}</option>
                                    <option value="reviewer">{{ __('ui.reviewer') }}</option>
                                    <option value="manager">{{ __('ui.manager') }}</option>
                                </select>
                                <button type="button"
                                        onclick="addAssignee()"
                                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors duration-200">
                                    {{ __('ui.add') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Кнопки действий -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('tasks.show', $task) }}"
                       class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                        {{ __('ui.cancel') }}
                    </a>
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                        {{ __('ui.save_changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            async function addAssignee() {
                const userId = document.getElementById('new_assignee').value;
                const role = document.getElementById('new_role').value;

                if (!userId) {
                    alert('{{ __("ui.please_select_user") }}');
                    return;
                }

                try {
                    const response = await fetch(`{{ route('tasks.assignees.store', $task) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ user_id: userId, role: role })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        location.reload();
                    } else {
                        alert(data.message || '{{ __("ui.error_adding_assignee") }}');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('{{ __("ui.error_occurred") }}');
                }
            }

            async function removeAssignee(userId) {
                if (!confirm('{{ __("ui.confirm_remove_assignee") }}')) {
                    return;
                }

                try {
                    const response = await fetch(`/tasks/{{ $task->id }}/assignees/${userId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    if (response.ok) {
                        // Удаляем элемент из DOM без перезагрузки
                        document.querySelector(`[data-user-id="${userId}"]`).remove();

                        // Показываем сообщение "нет исполнителей" если больше никого нет
                        const assigneeItems = document.querySelectorAll('.assignee-item');
                        if (assigneeItems.length === 0) {
                            document.getElementById('current-assignees').innerHTML =
                                '<p class="text-sm text-gray-500 dark:text-gray-400" id="no-assignees">{{ __("ui.no_assignees") }}</p>';
                        }
                    } else {
                        alert('{{ __("ui.error_removing_assignee") }}');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('{{ __("ui.error_occurred") }}');
                }
            }
        </script>
    @endpush
</x-app-layout>