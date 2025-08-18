<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Редактировать задачу
            </h2>
            <a href="{{ route('tasks.index') }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                Назад
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
                @method('PATCH')

                <!-- Основная информация -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Информация о задаче
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Название -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Название задачи <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name"
                                   value="{{ old('name', $task->name) }}"
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>

                        <!-- Описание -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Описание
                            </label>
                            <textarea name="description" id="description" rows="4"
                                      class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description', $task->description) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Приоритет -->
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Приоритет <span class="text-red-500">*</span>
                                </label>
                                <select name="priority" id="priority" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>
                                        Низкий
                                    </option>
                                    <option value="normal" {{ old('priority', $task->priority) == 'normal' ? 'selected' : '' }}>
                                        Обычный
                                    </option>
                                    <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>
                                        Высокий
                                    </option>
                                    <option value="urgent" {{ old('priority', $task->priority) == 'urgent' ? 'selected' : '' }}>
                                        Срочный
                                    </option>
                                </select>
                            </div>

                            <!-- Размер задачи -->
                            <div>
                                <label for="size_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Размер задачи
                                </label>
                                <select name="size_id" id="size_id"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Выберите размер</option>
                                    @if(isset($sizes))
                                        @foreach($sizes as $size)
                                            <option value="{{ $size->id }}"
                                                    {{ old('size_id', $task->size_id) == $size->id ? 'selected' : '' }}>
                                                {{ $size->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Оценка времени -->
                            <div>
                                <label for="estimated_hours" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Оценка времени (часы)
                                </label>
                                <input type="number" name="estimated_hours" id="estimated_hours"
                                       step="0.25" min="0.25" max="1000"
                                       value="{{ old('estimated_hours', $task->estimated_hours) }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="0.25">
                            </div>

                            <!-- Фактическое время (только для чтения, так как теперь через TimeLog) -->
                            <div>
                                <label for="actual_hours_display" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Фактическое время (часы)
                                </label>
                                <input type="text" id="actual_hours_display"
                                       value="{{ $task->actual_hours ?? 0 }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-100 dark:text-gray-400 rounded-md shadow-sm"
                                       readonly
                                       title="Фактическое время рассчитывается автоматически на основе записей времени">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Рассчитывается автоматически на основе записей времени
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Проект -->
                            <div>
                                <label for="project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Проект
                                </label>
                                <select name="project_id" id="project_id"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Выберите проект</option>
                                    @if(isset($projects))
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}"
                                                    {{ old('project_id', $task->project_id) == $project->id ? 'selected' : '' }}>
                                                {{ $project->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Статус -->
                            <div>
                                <label for="status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Статус <span class="text-red-500">*</span>
                                </label>
                                <select name="status_id" id="status_id" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @if(isset($statuses))
                                        @foreach($statuses as $status)
                                            <option value="{{ $status->id }}"
                                                    {{ old('status_id', $task->status_id) == $status->id ? 'selected' : '' }}>
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Дата начала -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Дата начала
                                </label>
                                <input type="datetime-local" name="start_date" id="start_date"
                                       value="{{ old('start_date', $task->start_date ? $task->start_date->format('Y-m-d\TH:i') : '') }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Дата окончания -->
                            <div>
                                <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Дедлайн
                                </label>
                                <input type="datetime-local" name="due_date" id="due_date"
                                       value="{{ old('due_date', $task->due_date ? $task->due_date->format('Y-m-d\TH:i') : '') }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <!-- Дата завершения (только для просмотра) -->
                        @if($task->completed_date)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Дата завершения
                                </label>
                                <input type="text"
                                       value="{{ $task->completed_date->format('d.m.Y H:i') }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-100 dark:text-gray-400 rounded-md shadow-sm"
                                       readonly>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Исполнители -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Исполнители
                        </h3>
                    </div>
                    <div class="p-6">
                        <!-- Текущие исполнители -->
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Текущие исполнители
                            </h4>
                            <div class="space-y-2" id="current-assignees">
                                @if(isset($task->users) && $task->users->count() > 0)
                                    @foreach($task->users as $user)
                                        <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 p-3 rounded-lg assignee-item" data-user-id="{{ $user->id }}">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                                    {{ substr($user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        Роль: {{ $user->pivot->role ?? 'исполнитель' }}
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
                                    @endforeach
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400" id="no-assignees">Нет исполнителей</p>
                                @endif
                            </div>
                        </div>

                        <!-- Добавить исполнителя -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Добавить исполнителя
                            </h4>
                            <div class="flex space-x-3">
                                <select id="new_assignee"
                                        class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Выберите пользователя</option>
                                    @if(isset($users))
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <select id="new_role"
                                        class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="assignee">Исполнитель</option>
                                    <option value="observer">Наблюдатель</option>
                                    <option value="reviewer">Проверяющий</option>
                                    <option value="manager">Менеджер</option>
                                </select>
                                <button type="button"
                                        onclick="addAssignee()"
                                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors duration-200">
                                    Добавить
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Кнопки действий -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('tasks.show', $task) }}"
                       class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                        Отмена
                    </a>
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                        Сохранить изменения
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // Добавляем CSRF токен в мета-тег если его нет
            if (!document.querySelector('meta[name="csrf-token"]')) {
                const metaToken = document.createElement('meta');
                metaToken.name = 'csrf-token';
                metaToken.content = '{{ csrf_token() }}';
                document.head.appendChild(metaToken);
            }

            async function addAssignee() {
                const userId = document.getElementById('new_assignee').value;
                const role = document.getElementById('new_role').value;

                if (!userId) {
                    alert('Пожалуйста, выберите пользователя');
                    return;
                }

                try {
                    const response = await fetch(`/tasks/{{ $task->id }}/assignees`, {
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
                        alert(data.message || 'Ошибка при добавлении исполнителя');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Произошла ошибка');
                }
            }

            async function removeAssignee(userId) {
                if (!confirm('Удалить исполнителя?')) {
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
                                '<p class="text-sm text-gray-500 dark:text-gray-400" id="no-assignees">Нет исполнителей</p>';
                        }
                    } else {
                        alert('Ошибка при удалении исполнителя');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Произошла ошибка');
                }
            }
        </script>
    @endpush
</x-app-layout>