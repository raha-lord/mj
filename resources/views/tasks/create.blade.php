<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Создать задачу
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

            <form method="POST" action="{{ route('tasks.store') }}" class="space-y-6">
                @csrf

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
                                   value="{{ old('name') }}"
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>

                        <!-- Описание -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Описание
                            </label>
                            <textarea name="description" id="description" rows="4"
                                      class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
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
                                                    {{ old('project_id') == $project->id ? 'selected' : '' }}>
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
                                                    {{ old('status_id') == $status->id ? 'selected' : '' }}>
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Приоритет -->
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Приоритет <span class="text-red-500">*</span>
                                </label>
                                <select name="priority" id="priority" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Низкий</option>
                                    <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>Обычный</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Высокий</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Срочный</option>
                                </select>
                            </div>

                            <!-- Размер -->
                            <div>
                                <label for="size_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Размер
                                </label>
                                <select name="size_id" id="size_id"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Автоопределение</option>
                                    @if(isset($sizes))
                                        @foreach($sizes as $size)
                                            <option value="{{ $size->id }}"
                                                    {{ old('size_id') == $size->id ? 'selected' : '' }}>
                                                {{ $size->code }} - {{ $size->name }} ({{ $size->time_range ?? 'без ограничений' }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Размер будет определен автоматически на основе оценки времени
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Оценка времени -->
                            <div>
                                <label for="estimated_hours" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Оценка времени (часы)
                                </label>
                                <input type="number" name="estimated_hours" id="estimated_hours"
                                       value="{{ old('estimated_hours') }}"
                                       step="0.25" min="0.25" max="1000"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="0.25">
                                <div id="size_recommendation" class="mt-1 text-sm text-blue-600 dark:text-blue-400" style="display: none;"></div>
                            </div>

                            <!-- Исполнители -->
                            <div>
                                <label for="assignees" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Исполнители
                                </label>
                                <select name="assignees[]" id="assignees" multiple
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @if(isset($users))
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}"
                                                    {{ in_array($user->id, old('assignees', [])) ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Удерживайте Ctrl для выбора нескольких исполнителей
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Дата начала -->
                            <div>
                                <label for="d_start" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Дата начала
                                </label>
                                <input type="datetime-local" name="d_start" id="d_start"
                                       value="{{ old('d_start') }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Дата завершения -->
                            <div>
                                <label for="d_end" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Дата завершения
                                </label>
                                <input type="datetime-local" name="d_end" id="d_end"
                                       value="{{ old('d_end') }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Кнопки действий -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('tasks.index') }}"
                       class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                        Отмена
                    </a>
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                        Создать задачу
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // Автоматическая рекомендация размера на основе оценки времени
            document.getElementById('estimated_hours').addEventListener('input', async function() {
                const hours = this.value;
                const recommendationDiv = document.getElementById('size_recommendation');

                if (!hours || hours < 0.25) {
                    recommendationDiv.style.display = 'none';
                    return;
                }

                try {
                    const response = await fetch('/tasks/size-recommendation?' + new URLSearchParams({
                        estimated_hours: hours
                    }));

                    const data = await response.json();

                    if (data.size) {
                        recommendationDiv.innerHTML = `Рекомендуемый размер: ${data.size.code} - ${data.size.name} (${data.size.time_range})`;
                        recommendationDiv.style.display = 'block';

                        // Автоматически выбираем рекомендованный размер
                        document.getElementById('size_id').value = data.size.id;
                    } else {
                        recommendationDiv.style.display = 'none';
                    }
                } catch (error) {
                    console.error('Error getting size recommendation:', error);
                    recommendationDiv.style.display = 'none';
                }
            });

            // Валидация дат
            document.getElementById('d_end').addEventListener('change', function() {
                const startDate = document.getElementById('d_start').value;
                const dueDate = this.value;

                if (startDate && dueDate && new Date(dueDate) < new Date(startDate)) {
                    alert('Дата завершения должна быть позже даты начала');
                    this.value = '';
                }
            });
        </script>
    @endpush
</x-app-layout>