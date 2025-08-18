<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('ui.tasks') }}
            </h2>
            <button onclick="openCreateModal()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                {{ __('ui.create_task') }}
            </button>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Сообщения -->
            @if (session('flash_message') || session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg relative">
                    <span class="block sm:inline">
                        {{ session('flash_message') ?? session('success') }}
                    </span>
                    <button type="button"
                            class="absolute top-0 bottom-0 right-0 px-4 py-3"
                            onclick="this.parentElement.style.display='none'">
                        <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                        </svg>
                    </button>
                </div>
            @endif

            <!-- Фильтры и поиск -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form id="filterForm" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Поиск -->
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('ui.search') }}
                                </label>
                                <input type="text"
                                       name="search"
                                       id="search"
                                       value="{{ request('search') }}"
                                       placeholder="{{ __('ui.search_tasks') }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Статус -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('ui.status') }}
                                </label>
                                <select name="status" id="status"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">{{ __('ui.all_statuses') }}</option>
                                    @foreach($statuses ?? [] as $status)
                                        <option value="{{ $status->slug }}"
                                                {{ request('status') == $status->slug ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Приоритет -->
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('ui.priority') }}
                                </label>
                                <select name="priority" id="priority"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">{{ __('ui.all_priorities') }}</option>
                                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>{{ __('ui.low') }}</option>
                                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>{{ __('ui.normal') }}</option>
                                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>{{ __('ui.high') }}</option>
                                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>{{ __('ui.urgent') }}</option>
                                </select>
                            </div>

                            <!-- Кнопки -->
                            <div class="flex items-end space-x-2">
                                <button type="submit" id="filterButton"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors duration-200">
                                    {{ __('ui.filter') }}
                                </button>
                                <button type="button" onclick="clearFilters()"
                                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-colors duration-200">
                                    {{ __('ui.clear') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Таблица задач -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <!-- Индикатор загрузки -->
                <div id="loadingIndicator" class="hidden">
                    <div class="flex justify-center items-center py-12">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <span class="ml-2 text-gray-600 dark:text-gray-300">Загрузка...</span>
                    </div>
                </div>
                
                <div id="tasksTable">
                    @include('tasks.partials.table', compact('tasks'))
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно создания задачи -->
    <div id="createTaskModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
                
                <div class="flex justify-between items-center mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="modal-title">
                        {{ __('ui.create_task') }}
                    </h3>
                    <button onclick="closeCreateModal()" type="button" 
                            class="rounded-md bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <span class="sr-only">Закрыть</span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

            <form id="createTaskForm" class="space-y-4">
                @csrf
                
                <!-- Название -->
                <div>
                    <label for="modal_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('ui.name') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="modal_name" required
                           class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Описание -->
                <div>
                    <label for="modal_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('ui.description') }}
                    </label>
                    <textarea name="description" id="modal_description" rows="3"
                              class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Проект -->
                    <div>
                        <label for="modal_project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('ui.project') }}
                        </label>
                        <select name="project_id" id="modal_project_id"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">{{ __('ui.select_project') }}</option>
                            @if(isset($projects))
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Статус -->
                    <div>
                        <label for="modal_status_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('ui.status') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="status_id" id="modal_status_id" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @if(isset($statuses))
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Приоритет -->
                    <div>
                        <label for="modal_priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('ui.priority') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="priority" id="modal_priority" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="low">{{ __('ui.low') }}</option>
                            <option value="normal" selected>{{ __('ui.normal') }}</option>
                            <option value="high">{{ __('ui.high') }}</option>
                            <option value="urgent">{{ __('ui.urgent') }}</option>
                        </select>
                    </div>

                    <!-- Размер -->
                    <div>
                        <label for="modal_size_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('ui.size') }}
                        </label>
                        <select name="size_id" id="modal_size_id"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">{{ __('ui.auto_detect') }}</option>
                            @if(isset($sizes))
                                @foreach($sizes as $size)
                                    <option value="{{ $size->id }}">{{ $size->code }} - {{ $size->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Оценка времени -->
                    <div>
                        <label for="modal_estimated_hours" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('ui.estimated_hours') }}
                        </label>
                        <input type="number" name="estimated_hours" id="modal_estimated_hours"
                               step="0.25" min="0.25" max="1000"
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               placeholder="0.25">
                    </div>

                    <!-- Дата завершения -->
                    <div>
                        <label for="modal_d_end" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('ui.due_date') }}
                        </label>
                        <input type="datetime-local" name="d_end" id="modal_d_end"
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Исполнители -->
                <div>
                    <label for="modal_assignees" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('ui.assignees') }}
                    </label>
                    <select name="assignees[]" id="modal_assignees" multiple
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @if(isset($users))
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('ui.multiple_select_hint') }}
                    </p>
                </div>

                <!-- Кнопки -->
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeCreateModal()"
                            class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {{ __('ui.cancel') }}
                    </button>
                    <button type="submit"
                            class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {{ __('ui.create_task') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('createTaskModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeCreateModal() {
            document.getElementById('createTaskModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            // Очистить форму
            document.getElementById('createTaskModal').querySelector('form').reset();
        }

        // Закрытие модального окна по клику на фон
        document.getElementById('createTaskModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCreateModal();
            }
        });

        // Закрытие по Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('createTaskModal').style.display === 'block') {
                closeCreateModal();
            }
        });

        // Получаем CSRF токен
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // API фильтрация
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            console.log('API фильтрация запущена'); // Отладка
            
            // Показываем индикатор загрузки
            document.getElementById('loadingIndicator').classList.remove('hidden');
            document.getElementById('tasksTable').style.opacity = '0.5';
            
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);
            
            fetch('/api/tasks/table-html?' + params.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('API ответ получен:', response.status); // Отладка
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('API данные получены'); // Отладка
                
                // Скрываем индикатор загрузки
                document.getElementById('loadingIndicator').classList.add('hidden');
                document.getElementById('tasksTable').style.opacity = '1';
                
                if (data.success) {
                    // Обновляем таблицу
                    document.getElementById('tasksTable').innerHTML = data.html;
                    
                    // Обновляем URL без перезагрузки страницы
                    const url = new URL(window.location.href);
                    for (const [key, value] of params) {
                        if (value) {
                            url.searchParams.set(key, value);
                        } else {
                            url.searchParams.delete(key);
                        }
                    }
                    window.history.pushState({}, '', url);
                } else {
                    throw new Error(data.message || 'API Error');
                }
            })
            .catch(error => {
                console.error('Ошибка API фильтрации:', error);
                
                // Скрываем индикатор в случае ошибки
                document.getElementById('loadingIndicator').classList.add('hidden');
                document.getElementById('tasksTable').style.opacity = '1';
                
                alert('Произошла ошибка при фильтрации. Попробуйте еще раз.');
            });
        });

        // Очистка фильтров
        function clearFilters() {
            document.getElementById('filterForm').reset();
            
            fetch('/api/tasks/table-html', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('tasksTable').innerHTML = data.html;
                    
                    // Очищаем URL
                    window.history.pushState({}, '', '{{ route("tasks.index") }}');
                }
            })
            .catch(error => {
                console.error('Ошибка очистки фильтров:', error);
            });
        }

        // Автофильтрация при изменении полей
        const filterInputs = document.querySelectorAll('#filterForm input, #filterForm select');
        filterInputs.forEach(input => {
            input.addEventListener('change', function(e) {
                console.log('Поле изменено:', this.name, this.value); // Отладка
                if (this.type !== 'text') { // Для select сразу фильтруем
                    e.preventDefault();
                    const submitEvent = new Event('submit', { cancelable: true });
                    document.getElementById('filterForm').dispatchEvent(submitEvent);
                }
            });
            
            // Для текстовых полей добавляем задержку
            if (input.type === 'text') {
                let timeout;
                input.addEventListener('input', function(e) {
                    console.log('Текст вводится:', this.value); // Отладка
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        const submitEvent = new Event('submit', { cancelable: true });
                        document.getElementById('filterForm').dispatchEvent(submitEvent);
                    }, 500); // Задержка 500мс
                });
            }
        });

        // API создание задачи
        document.getElementById('createTaskForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            console.log('API создание задачи запущено'); // Отладка
            
            const form = this;
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            // Блокируем кнопку и показываем загрузку
            submitButton.disabled = true;
            submitButton.textContent = 'Создание...';
            
            // Собираем данные для API
            const taskData = {
                name: formData.get('name'),
                description: formData.get('description'),
                project_id: formData.get('project_id') || null,
                status_id: formData.get('status_id'),
                priority: formData.get('priority'),
                size_id: formData.get('size_id') || null,
                estimated_hours: formData.get('estimated_hours') || null,
                d_end: formData.get('d_end') || null,
                assignees: formData.getAll('assignees[]')
            };
            
            fetch('/api/tasks/', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify(taskData)
            })
            .then(response => {
                console.log('API ответ создания задачи:', response.status); // Отладка
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                console.log('Задача создана:', data); // Отладка
                
                if (data.success) {
                    // Закрываем модальное окно
                    closeCreateModal();
                    
                    // Обновляем список задач
                    refreshTaskTable();
                    
                    // Показываем сообщение об успехе
                    showSuccessMessage(data.message || 'Задача успешно создана');
                } else {
                    throw new Error(data.message || 'Ошибка при создании задачи');
                }
            })
            .catch(error => {
                console.error('Ошибка API создания задачи:', error);
                
                let errorMessage = 'Произошла ошибка при создании задачи.';
                if (error.errors) {
                    // Обработка ошибок валидации
                    const firstError = Object.values(error.errors)[0];
                    if (Array.isArray(firstError)) {
                        errorMessage = firstError[0];
                    }
                } else if (error.message) {
                    errorMessage = error.message;
                }
                
                alert(errorMessage);
            })
            .finally(() => {
                // Восстанавливаем кнопку
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            });
        });
        
        // Функция обновления таблицы задач
        function refreshTaskTable() {
            console.log('Обновление таблицы задач'); // Отладка
            
            // Получаем текущие параметры фильтрации
            const filterForm = document.getElementById('filterForm');
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);
            
            fetch('/api/tasks/table-html?' + params.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('tasksTable').innerHTML = data.html;
                }
            })
            .catch(error => {
                console.error('Ошибка обновления таблицы:', error);
            });
        }
        
        // Функция показа сообщения об успехе
        function showSuccessMessage(message) {
            // Удаляем существующие сообщения
            const existingMessages = document.querySelectorAll('.success-message');
            existingMessages.forEach(msg => msg.remove());
            
            // Создаем новое сообщение
            const messageDiv = document.createElement('div');
            messageDiv.className = 'success-message mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg relative';
            messageDiv.innerHTML = `
                <span class="block sm:inline">${message}</span>
                <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </button>
            `;
            
            // Вставляем сообщение в начало контента
            const container = document.querySelector('.max-w-7xl.mx-auto.sm\\:px-6.lg\\:px-8');
            container.insertBefore(messageDiv, container.firstChild);
            
            // Автоматически скрываем через 5 секунд
            setTimeout(() => {
                if (messageDiv.parentNode) {
                    messageDiv.remove();
                }
            }, 5000);
        }

        // Дополнительная проверка на загрузку страницы
        window.addEventListener('beforeunload', function(e) {
            console.log('Страница перезагружается!'); // Отладка
        });
    </script>
</x-app-layout>