<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('ui.task') }} #{{ $task->id }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('tasks.edit', $task) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    {{ __('ui.edit') }}
                </a>
                <a href="{{ route('tasks.index') }}"
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    {{ __('ui.back') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Сообщения -->
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Основная информация -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Детали задачи -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                        {{ $task->name }}
                                    </h1>
                                    <div class="flex items-center space-x-4">
                                        <!-- Статус -->
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                              style="background-color: {{ $task->status->color }}20; color: {{ $task->status->color }};">
                                            {{ $task->status->name }}
                                        </span>

                                        <!-- Приоритет -->
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                                     @if($task->priority == 'urgent') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                     @elseif($task->priority == 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                                     @elseif($task->priority == 'normal') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                     @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif">
                                            {{ __('ui.' . $task->priority) }}
                                        </span>

                                        <!-- Размер -->
                                        @if($task->size)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                {{ $task->size->code }} - {{ $task->size->name }}
                                            </span>
                                        @endif

                                        <!-- Просрочено -->
                                        @if($task->isOverdue())
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                {{ __('ui.overdue') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Действия -->
                                <div class="flex space-x-2">
                                    @if(!$task->isCompleted())
                                        <button onclick="markCompleted()"
                                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition-colors duration-200">
                                            {{ __('ui.mark_completed') }}
                                        </button>
                                    @else
                                        <button onclick="reopen()"
                                                class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm transition-colors duration-200">
                                            {{ __('ui.reopen') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <!-- Описание -->
                            @if($task->description)
                                <div class="mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                        {{ __('ui.description') }}
                                    </h3>
                                    <div class="prose dark:prose-invert max-w-none">
                                        {!! nl2br(e($task->description)) !!}
                                    </div>
                                </div>
                            @endif

                            <!-- Детали -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                        {{ __('ui.project') }}
                                    </h4>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                        {{ $task->project->name ?? __('ui.no_project') }}
                                    </p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                        {{ __('ui.created_by') }}
                                    </h4>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                        {{ $task->createdBy->name ?? __('ui.system') }}
                                    </p>
                                </div>

                                @if($task->start_date)
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                            {{ __('ui.start_date') }}
                                        </h4>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                            {{ $task->start_date->format('d.m.Y H:i') }}
                                        </p>
                                    </div>
                                @endif

                                @if($task->due_date)
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                            {{ __('ui.due_date') }}
                                        </h4>
                                        <p class="mt-1 text-sm {{ $task->isOverdue() ? 'text-red-600 font-medium' : 'text-gray-900 dark:text-white' }}">
                                            {{ $task->due_date->format('d.m.Y H:i') }}
                                            <span class="text-xs text-gray-500 dark:text-gray-400 block">
                                                {{ $task->due_date->diffForHumans() }}
                                            </span>
                                        </p>
                                    </div>
                                @endif

                                @if($task->completed_date)
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                            {{ __('ui.completed_date') }}
                                        </h4>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                            {{ $task->completed_date->format('d.m.Y H:i') }}
                                            <span class="text-xs text-gray-500 dark:text-gray-400 block">
                                                {{ $task->completed_date->diffForHumans() }}
                                            </span>
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Статистика времени -->
                    @if($task->estimated_hours || $task->actual_hours)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ __('ui.time_tracking') }}
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <!-- Оценка -->
                                    @if($task->estimated_hours)
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                                {{ $task->estimated_hours }}ч
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ __('ui.estimated') }}
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Фактически -->
                                    @if($task->actual_hours)
                                        <div class="text-center">
                                            <div class="text-2xl font-bold {{ $timeStats['is_over_budget'] ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                                {{ $task->actual_hours }}ч
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ __('ui.actual') }}
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Точность оценки -->
                                    @if($timeStats['estimation_accuracy'])
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                                {{ $timeStats['estimation_accuracy'] }}%
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ __('ui.accuracy') }}
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Прогресс-бар -->
                                @if($timeStats['time_progress'])
                                    <div class="mt-6">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('ui.progress') }}</span>
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $timeStats['time_progress'] }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                            <div class="h-2 rounded-full {{ $timeStats['is_over_budget'] ? 'bg-red-600' : 'bg-blue-600' }}"
                                                 style="width: {{ min($timeStats['time_progress'], 100) }}%"></div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Логирование времени -->
                                @if(!$task->isCompleted())
                                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                            {{ __('ui.log_time') }}
                                        </h4>
                                        <form action="{{ route('tasks.log-time', $task) }}" method="POST" class="flex space-x-3">
                                            @csrf
                                            <input type="number" name="hours" step="0.25" min="0.25" max="24"
                                                   placeholder="{{ __('ui.hours') }}"
                                                   class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                            <input type="text" name="description" placeholder="{{ __('ui.description_optional') }}"
                                                   class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                            <button type="submit"
                                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors duration-200">
                                                {{ __('ui.log') }}
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Рекомендации по размеру -->
                    @if($recommendedSize && $recommendedSize->id !== $task->size_id)
                        <div class="bg-yellow-50 dark:bg-yellow-900/50 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                        {{ __('ui.size_recommendation') }}
                                    </h3>
                                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                        {{ __('ui.recommended_size_hint', [
                                            'current' => $task->size->code ?? __('ui.none'),
                                            'recommended' => $recommendedSize->code
                                        ]) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Боковая панель -->
                <div class="space-y-6">
                    <!-- Исполнители -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('ui.assignees') }}
                            </h3>
                        </div>
                        <div class="p-6">
                            @forelse($task->users as $user)
                                <div class="flex items-center justify-between mb-3 last:mb-0">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ __('ui.' . $user->pivot->role) }}
                                            </p>
                                        </div>
                                    </div>
                                    @if($user->pivot->completed_at)
                                        <span class="text-green-600 dark:text-green-400 text-xs">
                                            {{ __('ui.completed') }}
                                        </span>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('ui.no_assignees') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Метаданные -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('ui.metadata') }}
                            </h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    {{ __('ui.created') }}
                                </h4>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $task->created_at->format('d.m.Y H:i') }}
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block">
                                        {{ $task->created_at->diffForHumans() }}
                                    </span>
                                </p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    {{ __('ui.updated') }}
                                </h4>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $task->updated_at->format('d.m.Y H:i') }}
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block">
                                        {{ $task->updated_at->diffForHumans() }}
                                    </span>
                                </p>
                            </div>

                            @if($task->updatedBy)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                        {{ __('ui.last_updated_by') }}
                                    </h4>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                        {{ $task->updatedBy->name }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Быстрые действия -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ __('ui.quick_actions') }}
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <a href="{{ route('tasks.edit', $task) }}"
                               class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-center block transition-colors duration-200">
                                {{ __('ui.edit_task') }}
                            </a>

                            @if($task->project)
                                <a href="{{ route('projects.show', $task->project) }}"
                                   class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-center block transition-colors duration-200">
                                    {{ __('ui.view_project') }}
                                </a>
                            @endif

                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="w-full">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('{{ __('ui.confirm_delete_task') }}')"
                                        class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                                    {{ __('ui.delete_task') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- История изменений -->
            @if($task->history && $task->history->count() > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('ui.change_history') }}
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                @foreach($task->history->take(10) as $change)
                                    <li>
                                        <div class="relative pb-8">
                                            @if(!$loop->last)
                                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                            @endif
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                                            <span class="font-medium text-gray-900 dark:text-white">{{ $change->user->name }}</span>
                                                            {{ __('ui.changed_field', ['field' => __('ui.' . $change->field)]) }}
                                                            @if($change->old_value && $change->new_value)
                                                                {{ __('ui.from') }} <span class="font-medium">{{ $change->old_value }}</span>
                                                                {{ __('ui.to') }} <span class="font-medium">{{ $change->new_value }}</span>
                                                            @elseif($change->new_value)
                                                                {{ __('ui.to') }} <span class="font-medium">{{ $change->new_value }}</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                        {{ $change->changed_at->diffForHumans() }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        @if($task->history->count() > 10)
                            <div class="mt-6 text-center">
                                <button class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                                    {{ __('ui.show_all_changes', ['count' => $task->history->count()]) }}
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            async function markCompleted() {
                if (!confirm('{{ __("ui.confirm_mark_completed") }}')) {
                    return;
                }

                try {
                    const response = await fetch('{{ route("tasks.mark-completed", $task) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    if (response.ok) {
                        location.reload();
                    } else {
                        alert('{{ __("ui.error_occurred") }}');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('{{ __("ui.error_occurred") }}');
                }
            }

            async function reopen() {
                if (!confirm('{{ __("ui.confirm_reopen") }}')) {
                    return;
                }

                try {
                    const response = await fetch('{{ route("tasks.reopen", $task) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    if (response.ok) {
                        location.reload();
                    } else {
                        alert('{{ __("ui.error_occurred") }}');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('{{ __("ui.error_occurred") }}');
                }
            }
        </script>
    @endpush
</x-app-layout>