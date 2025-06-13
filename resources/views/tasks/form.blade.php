<!-- Название задачи -->
<div>
    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ __('ui.task_name') }} <span class="text-red-500">*</span>
    </label>
    <input type="text" name="name" id="name"
           value="{{ old('name', isset($task) ? $task->name : '') }}"
           class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
           required>
    @error('name')
    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<!-- Описание -->
<div>
    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ __('ui.description') }}
    </label>
    <textarea name="description" id="description" rows="4"
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description', isset($task) ? $task->description : '') }}</textarea>
    @error('description')
    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
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
                        {{ old('project_id', isset($task) ? $task->project_id : '') == $project->id ? 'selected' : '' }}>
                    {{ $project->name }}
                </option>
            @endforeach
        </select>
        @error('project_id')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
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
                        {{ old('status_id', isset($task) ? $task->status_id : '') == $status->id ? 'selected' : '' }}>
                    {{ $status->name }}
                </option>
            @endforeach
        </select>
        @error('status_id')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
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
            <option value="low" {{ old('priority', isset($task) ? $task->priority : 'normal') == 'low' ? 'selected' : '' }}>
                {{ __('ui.low') }}
            </option>
            <option value="normal" {{ old('priority', isset($task) ? $task->priority : 'normal') == 'normal' ? 'selected' : '' }}>
                {{ __('ui.normal') }}
            </option>
            <option value="high" {{ old('priority', isset($task) ? $task->priority : 'normal') == 'high' ? 'selected' : '' }}>
                {{ __('ui.high') }}
            </option>
            <option value="urgent" {{ old('priority', isset($task) ? $task->priority : 'normal') == 'urgent' ? 'selected' : '' }}>
                {{ __('ui.urgent') }}
            </option>
        </select>
        @error('priority')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Размер -->
    <div>
        <label for="size_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ __('ui.size') }}
        </label>
        <select name="size_id" id="size_id"
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="">{{ $formMode === 'create' ? __('ui.auto_detect') : __('ui.no_size') }}</option>
            @foreach($sizes as $size)
                <option value="{{ $size->id }}"
                        {{ old('size_id', isset($task) ? $task->size_id : '') == $size->id ? 'selected' : '' }}>
                    {{ $size->code }} - {{ $size->name }} ({{ $size->time_range }})
                </option>
            @endforeach
        </select>
        @if($formMode === 'create')
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('ui.size_auto_hint') }}
            </p>
        @endif
        @error('size_id')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Оценка времени -->
    <div>
        <label for="estimated_hours" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ __('ui.estimated_hours') }}
        </label>
        <input type="number" name="estimated_hours" id="estimated_hours"
               value="{{ old('estimated_hours', isset($task) ? $task->estimated_hours : '') }}"
               step="0.25" min="0.25" max="1000"
               class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        <div id="size_recommendation" class="mt-1 text-sm text-blue-600 dark:text-blue-400" style="display: none;"></div>
        @error('estimated_hours')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Фактическое время (только при редактировании) -->
    @if($formMode === 'edit')
        <div>
            <label for="actual_hours" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('ui.actual_hours') }}
            </label>
            <input type="number" name="actual_hours" id="actual_hours"
                   value="{{ old('actual_hours', $task->actual_hours) }}"
                   step="0.25" min="0" max="1000"
                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('actual_hours')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    @endif
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- Дата начала -->
    <div>
        <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ __('ui.start_date') }}
        </label>
        <input type="datetime-local" name="start_date" id="start_date"
               value="{{ old('start_date', isset($task) && $task->start_date ? $task->start_date->format('Y-m-d\TH:i') : '') }}"
               class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        @error('start_date')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <!-- Дата завершения -->
    <div>
        <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ __('ui.due_date') }}
        </label>
        <input type="datetime-local" name="due_date" id="due_date"
               value="{{ old('due_date', isset($task) && $task->due_date ? $task->due_date->format('Y-m-d\TH:i') : '') }}"
               class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        @error('due_date')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>

<!-- Исполнители (только при создании) -->
@if($formMode === 'create')
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
        @error('assignees')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
        @error('assignees.*')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
@endif

<!-- Кнопки -->
<div class="flex items-center justify-end gap-4 pt-6">
    <a href="{{ route('tasks.index') }}"
       class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors duration-200">
        {{ __('ui.cancel') }}
    </a>
    <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
        {{ $formMode === 'edit' ? __('ui.save_changes') : __('ui.create_task') }}
    </button>
</div>

@push('scripts')
    <script>
        // Автоматическая рекомендация размера на основе оценки времени (только для создания)
        @if($formMode === 'create')
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
        @endif

        // Валидация дат
        document.getElementById('due_date').addEventListener('change', function() {
            const startDate = document.getElementById('start_date').value;
            const dueDate = this.value;

            if (startDate && dueDate && new Date(dueDate) < new Date(startDate)) {
                alert('{{ __("ui.due_date_after_start") }}');
                this.value = '';
            }
        });

        document.getElementById('start_date').addEventListener('change', function() {
            const startDate = this.value;
            const dueDate = document.getElementById('due_date').value;

            if (startDate && dueDate && new Date(dueDate) < new Date(startDate)) {
                alert('{{ __("ui.start_date_before_due") }}');
                this.value = '';
            }
        });
    </script>
@endpush