<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Задача #{{ $task->id }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('tasks.edit', $task) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    Редактировать
                </a>
                <a href="{{ route('tasks.index') }}"
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    Назад
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
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ $task->status->name ?? 'Без статуса' }}
                                        </span>

                                        <!-- Приоритет -->
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                                     @if($task->priority == 'urgent') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                     @elseif($task->priority == 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                                     @elseif($task->priority == 'normal') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                     @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @endif">
                                            {{ ucfirst($task->priority ?? 'normal') }}
                                        </span>

                                        <!-- Размер -->
                                        @if($task->size)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                {{ $task->size->code }} - {{ $task->size->name }}
                                            </span>
                                        @endif

                                        <!-- Просрочено -->
                                        @if($task->due_date && $task->due_date->isPast() && !$task->completed_date)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                Просрочено
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Действия -->
                                <div class="flex space-x-2">
                                    @if(!$task->completed_date)
                                        <form method="POST" action="{{ url('tasks/' . $task->id . '/mark-completed') }}" style="display: inline;">
                                            @csrf
                                            <button type="submit"
                                                    onclick="return confirm('Отметить задачу как выполненную?')"
                                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition-colors duration-200">
                                                Завершить
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ url('tasks/' . $task->id . '/reopen') }}" style="display: inline;">
                                            @csrf
                                            <button type="submit"
                                                    onclick="return confirm('Переоткрыть задачу?')"
                                                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm transition-colors duration-200">
                                                Переоткрыть
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <!-- Описание -->
                            @if($task->description)
                                <div class="mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                        Описание
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
                                        Проект
                                    </h4>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                        {{ $task->project->name ?? 'Без проекта' }}
                                    </p>
                                </div>

                                @if($task->start_date)
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                            Дата начала
                                        </h4>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                            {{ $task->start_date->format('d.m.Y H:i') }}
                                        </p>
                                    </div>
                                @endif

                                @if($task->due_date)
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                            Дедлайн
                                        </h4>
                                        <p class="mt-1 text-sm {{ $task->due_date->isPast() && !$task->completed_date ? 'text-red-600 font-medium' : 'text-gray-900 dark:text-white' }}">
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
                                            Дата завершения
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

                    <!-- Учет времени -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Учет времени
                                </h3>
                                @if(isset($timeLogStats) && $timeLogStats['entries_count'] > 0)
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $timeLogStats['entries_count'] }} {{ trans_choice('записей|запись|записи', $timeLogStats['entries_count']) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="p-6">
                            <!-- Статистика -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <!-- Оценка -->
                                @if(isset($task->estimated_hours))
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                            {{ $task->estimated_hours }}ч
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            Оценка
                                        </div>
                                    </div>
                                @endif

                                <!-- Фактически -->
                                <div class="text-center">
                                    <div class="text-2xl font-bold {{ isset($task->estimated_hours) && $task->actual_hours > $task->estimated_hours ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                        {{ $task->actual_hours ?? 0 }}ч
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        Фактически
                                    </div>
                                </div>

                                <!-- Прогресс -->
                                @if(isset($task->estimated_hours) && $task->estimated_hours > 0)
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                            {{ round(($task->actual_hours / $task->estimated_hours) * 100) }}%
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            Прогресс
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Форма добавления времени -->
                            @if(!$task->completed_date)
                                <div class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                        Записать время
                                    </h4>
                                    <form action="{{ route('tasks.log-time', $task) }}" method="POST" class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                                        @csrf
                                        <input type="number" name="hours" step="0.25" min="0.25" max="24"
                                               placeholder="Часы"
                                               class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                               required>
                                        <input type="text" name="description" placeholder="Описание работы"
                                               class="flex-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <button type="submit"
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors duration-200 whitespace-nowrap">
                                            Записать время
                                        </button>
                                    </form>
                                </div>
                            @endif

                            <!-- Список записей времени -->
                            @if($task->timeLogs && $task->timeLogs->count() > 0)
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
                                        История записей времени
                                    </h4>

                                    <div class="space-y-3 max-h-96 overflow-y-auto">
                                        @foreach($task->timeLogs->sortByDesc('created_at') as $timeLog)
                                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                <div class="flex-1">
                                                    <div class="flex items-center space-x-3">
                                                        <span class="font-medium text-blue-600 dark:text-blue-400">
                                                            {{ $timeLog->hours }}ч
                                                        </span>
                                                        <span class="text-sm text-gray-600 dark:text-gray-300">
                                                            {{ $timeLog->user->name }}
                                                        </span>
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $timeLog->created_at->format('d.m.Y H:i') }}
                                                        </span>
                                                    </div>
                                                    @if($timeLog->description)
                                                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                            {{ $timeLog->description }}
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Действия для автора записи -->
                                                @if($timeLog->user_id === auth()->id() && !$task->completed_date)
                                                    <div class="flex space-x-2 ml-3">
                                                        <button onclick="editTimeLog({{ $timeLog->id }}, {{ $timeLog->hours }}, '{{ $timeLog->description }}')"
                                                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm">
                                                            Изменить
                                                        </button>
                                                        <form method="POST" action="{{ route('api.time-logs.destroy', $timeLog) }}"
                                                              style="display: inline;"
                                                              onsubmit="return deleteTimeLog(event, this)">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm">
                                                                Удалить
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Детальная статистика -->
                                    @if(isset($timeLogStats) && $timeLogStats['users_count'] > 1)
                                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                            <h5 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                                                По исполнителям
                                            </h5>
                                            <div class="grid grid-cols-2 gap-3">
                                                @foreach($timeLogStats['by_user'] as $userStat)
                                                    <div class="flex justify-between text-sm">
                                                        <span class="text-gray-600 dark:text-gray-400">{{ $userStat['user'] }}</span>
                                                        <span class="font-medium text-gray-900 dark:text-white">{{ $userStat['hours'] }}ч</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-center py-6 text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-gray-700">
                                    <p>Записей времени пока нет</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- История изменений -->
                    @if($task->history && $task->history->count() > 0)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        История изменений
                                    </h3>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $task->history->count() }} {{ trans_choice('изменений|изменение|изменения', $task->history->count()) }}
                                    </span>
                                </div>
                            </div>

                            <div class="p-6">
                                <div class="space-y-4 max-h-96 overflow-y-auto">
                                    @foreach($task->history->sortByDesc('created_at') as $historyItem)
                                        <div class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <!-- Иконка в зависимости от типа изменения -->
                                            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center
                                                @if(str_contains($historyItem->description, 'создан')) bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400
                                                @elseif(str_contains($historyItem->description, 'завершен')) bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400
                                                @elseif(str_contains($historyItem->description, 'статус')) bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-400
                                                @elseif(str_contains($historyItem->description, 'назначен')) bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-400
                                                @else bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-400 @endif">

                                                @if(str_contains($historyItem->description, 'создан'))
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                                    </svg>
                                                @elseif(str_contains($historyItem->description, 'завершен'))
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                @elseif(str_contains($historyItem->description, 'статус'))
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                                                    </svg>
                                                @elseif(str_contains($historyItem->description, 'назначен'))
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM9 16a6 6 0 016-6H5a6 6 0 016 6z" />
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                                    </svg>
                                                @endif
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between">
                                                    <p class="text-sm text-gray-900 dark:text-white">
                                                        {{ $historyItem->metadata['description'] ?? 'Изменение поля: ' . $historyItem->field }}
                                                    </p>
                                                    @if($historyItem->changed_at)
                                                        <time class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0 ml-2">
                                                            {{ $historyItem->changed_at->format('d.m.Y H:i') }}
                                                        </time>
                                                    @endif
                                                </div>

                                                @if($historyItem->user)
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        {{ $historyItem->user->name }}
                                                    </p>
                                                @endif

                                                <!-- Показываем детали изменения -->
                                                @if($historyItem->old_value && $historyItem->new_value && !in_array($historyItem->field, ['created', 'deleted', 'restored']))
                                                    <div class="mt-2 text-xs">
                                                        <div class="text-red-600 dark:text-red-400">
                                                            <span class="font-medium">Было:</span> {{ $historyItem->old_value }}
                                                        </div>
                                                        <div class="text-green-600 dark:text-green-400">
                                                            <span class="font-medium">Стало:</span> {{ $historyItem->new_value }}
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Показать еще, если записей много -->
                                @if($task->history->count() > 10)
                                    <div class="mt-4 text-center">
                                        <button onclick="toggleAllHistory()"
                                                id="toggleHistoryBtn"
                                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                                            Показать всю историю
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Рекомендации по размеру -->
                    @if(isset($recommendedSize) && isset($task->size_id) && $recommendedSize->id !== $task->size_id)
                        <div class="bg-yellow-50 dark:bg-yellow-900/50 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                        Рекомендация по размеру
                                    </h3>
                                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                        Рекомендуется изменить размер с "{{ $task->size->code ?? 'Нет' }}" на "{{ $recommendedSize->code }}"
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Боковая панель -->
                <div class="space-y-6">
                    <!-- Метаданные -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Информация
                            </h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                    Создано
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
                                    Обновлено
                                </h4>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $task->updated_at->format('d.m.Y H:i') }}
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block">
                                        {{ $task->updated_at->diffForHumans() }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Быстрые действия -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Действия
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <a href="{{ route('tasks.edit', $task) }}"
                               class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-center block transition-colors duration-200">
                                Редактировать задачу
                            </a>

                            @if($task->project)
                                <a href="{{ route('projects.show', $task->project) }}"
                                   class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-center block transition-colors duration-200">
                                    Смотреть проект
                                </a>
                            @endif

                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="w-full">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Вы уверены, что хотите удалить эту задачу?')"
                                        class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                                    Удалить задачу
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно для редактирования записи времени -->
    <div id="editTimeLogModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Редактировать запись времени
                </h3>
                <form id="editTimeLogForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Часы
                            </label>
                            <input type="number" name="hours" id="editHours" step="0.25" min="0.25" max="24"
                                   class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Описание
                            </label>
                            <input type="text" name="description" id="editDescription"
                                   class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div class="mt-6 flex space-x-3">
                        <button type="submit"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors duration-200">
                            Сохранить
                        </button>
                        <button type="button" onclick="closeEditModal()"
                                class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-colors duration-200">
                            Отмена
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editTimeLog(timeLogId, hours, description) {
            document.getElementById('editTimeLogForm').action = `/api/time-logs/${timeLogId}`;
            document.getElementById('editHours').value = hours;
            document.getElementById('editDescription').value = description || '';
            document.getElementById('editTimeLogModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editTimeLogModal').classList.add('hidden');
        }

        function deleteTimeLog(event, form) {
            event.preventDefault();

            if (!confirm('Вы уверены, что хотите удалить эту запись времени?')) {
                return false;
            }

            fetch(form.action, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Произошла ошибка');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Произошла ошибка при удалении');
                });

            return false;
        }

        // Обработка формы редактирования
        document.getElementById('editTimeLogForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Произошла ошибка');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Произошла ошибка при сохранении');
                });
        });

        // Функция для показа/скрытия полной истории
        function toggleAllHistory() {
            const historyContainer = document.querySelector('.space-y-4.max-h-96.overflow-y-auto');
            const toggleBtn = document.getElementById('toggleHistoryBtn');

            if (historyContainer.classList.contains('max-h-96')) {
                historyContainer.classList.remove('max-h-96');
                toggleBtn.textContent = 'Скрыть часть истории';
            } else {
                historyContainer.classList.add('max-h-96');
                toggleBtn.textContent = 'Показать всю историю';
            }
        }
    </script>
</x-app-layout>