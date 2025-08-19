<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Демо: Сравнение модальных окон
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Описание -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Сравнение реализаций модальных окон
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">1. Старая версия (Alpine.js + Blade)</h4>
                            <p class="text-gray-600 dark:text-gray-400">
                                Оригинальная страница задач с JavaScript модальными окнами и Alpine.js
                            </p>
                            <a href="{{ route('tasks.index') }}" 
                               class="inline-flex items-center px-4 py-2 mt-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                Перейти к старой версии
                            </a>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">2. Новая версия (Vue 3 + Headless UI)</h4>
                            <p class="text-gray-600 dark:text-gray-400">
                                Новая реализация с Vue 3, Headless UI Dialog компонентами и современной архитектурой
                            </p>
                            <a href="{{ route('tasks.vue') }}" 
                               class="inline-flex items-center px-4 py-2 mt-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                Перейти к новой версии (Vue)
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Преимущества новой версии -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Преимущества новой версии
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white mb-2">🎨 Headless UI Dialog</h4>
                            <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                <li>• Accessibility из коробки</li>
                                <li>• Фокус-менеджмент</li>
                                <li>• Клавиатурная навигация</li>
                                <li>• Правильная ARIA разметка</li>
                            </ul>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white mb-2">⚡ Vue 3 Composables</h4>
                            <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                <li>• Переиспользуемая логика</li>
                                <li>• Reactive состояние</li>
                                <li>• Лучшая типизация</li>
                                <li>• Современная архитектура</li>
                            </ul>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white mb-2">🔧 Универсальность</h4>
                            <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                <li>• Настраиваемые размеры</li>
                                <li>• Гибкие слоты</li>
                                <li>• Различные типы модалок</li>
                                <li>• Легко расширяемый</li>
                            </ul>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white mb-2">🚀 Производительность</h4>
                            <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                <li>• Virtual DOM</li>
                                <li>• Оптимизированные перерисовки</li>
                                <li>• Lazy loading компонентов</li>
                                <li>• Меньше DOM манипуляций</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Технические особенности -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Технические особенности реализации
                    </h3>
                    <div class="space-y-4">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 dark:text-white mb-2">Структура компонентов:</h4>
                            <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400 font-mono">
                                <li>📁 /resources/js/Components/</li>
                                <li>&nbsp;&nbsp;├── Modal.vue (универсальный Dialog)</li>
                                <li>&nbsp;&nbsp;├── TaskModal.vue (модалка задачи)</li>
                                <li>&nbsp;&nbsp;└── TaskManager.vue (главный компонент)</li>
                                <li>📁 /resources/js/composables/</li>
                                <li>&nbsp;&nbsp;└── useTasks.js (API логика)</li>
                            </ul>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 dark:text-white mb-2">Возможности модального компонента:</h4>
                            <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                <li>• Размеры: xs, sm, md, lg, xl, 2xl, 3xl, full</li>
                                <li>• Настраиваемые отступы: none, sm, default, lg, xl</li>
                                <li>• Типы кнопок: primary, danger, success, warning</li>
                                <li>• Скрытие/показ: header, footer, кнопок</li>
                                <li>• Слоты: title, default, footer</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>