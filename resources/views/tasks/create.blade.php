<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('ui.create_task') }}
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

            <form method="POST" action="{{ route('tasks.store') }}" class="space-y-6">
                @csrf

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
                                   value="{{ old('name') }}"
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>

                        <!-- Описание -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('ui.description') }}
                            </label>
                            <textarea name="description" id="description" rows="4"
                                      class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Проект -->
                            <div>
                                <label for="project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('ui.project') }}
                                </label>
                                <select name="project_id" id="project_id"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">{{ __('ui.select_project') }}</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}"
                                                {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Статус -->
                            <div>
                                <label for="status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('ui.status') }} <span class="text-red-500">*</span>
                                </label>
                                <select name="status_id" id="status_id" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}"
                                                {{ old('status_id') == $status->id ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Приоритет -->
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('ui.priority') }} <span class="text-red-500">*</span>
                                </label>
                                <select name="priority" id="priority" required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>{{ __('ui.low') }}</option>
                                    <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>{{ __('ui.normal') }}</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>{{ __('ui.high') }}</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>{{ __('ui.urgent') }}</option>
                                </select>
                            </div>

                            <!-- Размер -->
                            <div>
                                <label for="size_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('ui.size') }}
                                </label>
                                <select name="size_id" id="size_id"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">{{ __('ui.auto_detect') }}</option>
                                    @foreach($sizes as $size)
                                        <option value="{{ $size->id }}"
                                                {{ old('size_id') == $size->id ? 'selected' : '' }}>
                                            {{ $size->code }} - {{ $size->name }} ({{ $size->time_range }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('ui.size_auto_hint') }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Оценка времени -->
                            <div>
                                <label for="estimated_hours" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('ui.estimated_hours') }}
                                </label>
                                <input type="number" name="estimated_hours" id="estimated_hours"
                                       value="{{ old('estimated_hours') }}"
                                       step="0.25" min="0.25" max="1000"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <div id="size_recommendation" class="mt-1 text-sm text-blue-600 dark:text-blue-400" style="display: none;"></div>
                            </div>

                            <!-- Исполнители -->
                            <div>
                                <label for="assignees" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('ui.assignees') }}
                                </label>
                                <select name="assignees[]" id="assignees" multiple
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}"
                                                {{ in_array($user->id, old('assignees', [])) ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('ui.hold_ctrl_multiple') }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Дата начала -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('ui.start_date') }}
                                </label>
                                <input type="datetime-local" name="start_date" id="start_date"
                                       value="{{ old('start_date') }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Дата завершения -->
                            <div>
                                <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('ui.due_date') }}
                                </label>
                                <input type="datetime-local" name="due_date" id="due_date"
                                       value="{{ old('due_date') }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Кнопки действий -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('tasks.index') }}"
                       class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                        {{ __('ui.cancel') }}
                    </a>
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                        {{ __('ui.create_task') }}
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
                        recommendationDiv.innerHTML = `{{ __('ui.recommended_size') }}: ${data.size.code} - ${data.size.name} (${data.size.time_range})`;
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
            document.getElementById('due_date').addEventListener('change', function() {
                const startDate = document.getElementById('start_date').value;
                const dueDate = this.value;

                if (startDate && dueDate && new Date(dueDate) < new Date(startDate)) {
                    alert('{{ __("ui.due_date_after_start") }}');
                    this.value = '';
                }
            });
        </script>
    @endpush
</x-app-layout>