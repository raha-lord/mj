import './bootstrap';
import { createApp } from 'vue';

// Импортируем компоненты
import TaskManager from './Components/TaskManager.vue';
import DebugTaskManager from './Components/DebugTaskManager.vue';
import MinimalTest from './Components/MinimalTest.vue';
import TestModal from './Components/TestModal.vue';
import TestTaskModal from './Components/TestTaskModal.vue';
import SimpleTaskManager from './Components/SimpleTaskManager.vue';
import WorkingTaskManager from './Components/WorkingTaskManager.vue';
import SimpleModal from './Components/SimpleModal.vue';

// Создаем приложение Vue только если есть элемент для монтирования
document.addEventListener('DOMContentLoaded', () => {
    // TaskManager (основное приложение)
    const taskManagerAppElement = document.getElementById('task-manager-app');
    if (taskManagerAppElement) {
        console.log('Монтируем TaskManager App...');
        try {
            console.log('Создаем Vue приложение...');
            
            const app = createApp({
                components: {
                    'task-manager': TaskManager
                },
                template: '<task-manager :initial-data="initialData"></task-manager>',
                data() {
                    return {
                        initialData: window.taskManagerInitialData || {}
                    }
                }
            });
            
            // Добавляем обработчик ошибок Vue
            app.config.errorHandler = (err, instance, info) => {
                console.error('Vue ошибка:', err);
                console.error('Экземпляр:', instance);
                console.error('Информация:', info);
                
                // Показываем ошибку пользователю
                taskManagerAppElement.innerHTML = `
                    <div class="bg-red-50 border border-red-200 p-4 rounded mb-4">
                        <h3 class="font-bold text-red-800">Ошибка Vue компонента</h3>
                        <p class="text-red-700">${err.message}</p>
                        <details class="mt-2">
                            <summary class="text-red-600 cursor-pointer">Подробности</summary>
                            <pre class="text-xs bg-red-100 p-2 mt-2 rounded">${err.stack}</pre>
                        </details>
                    </div>
                `;
            };
            
            console.log('Монтируем приложение...');
            app.mount('#task-manager-app');
            console.log('TaskManager App успешно смонтирован');
        } catch (error) {
            console.error('Ошибка при монтировании TaskManager App:', error);
            taskManagerAppElement.innerHTML = `
                <div class="bg-red-50 border border-red-200 p-4 rounded mb-4">
                    <h3 class="font-bold text-red-800">Ошибка инициализации</h3>
                    <p class="text-red-700">${error.message}</p>
                    <details class="mt-2">
                        <summary class="text-red-600 cursor-pointer">Подробности</summary>
                        <pre class="text-xs bg-red-100 p-2 mt-2 rounded">${error.stack}</pre>
                    </details>
                </div>
            `;
        }
    }

    // TaskManager (legacy поддержка)
    const taskManagerElement = document.getElementById('task-manager');
    if (taskManagerElement) {
        console.log('Монтируем TaskManager (legacy)...');
        try {
            // Получаем данные из атрибутов элемента
            const initialDataAttr = taskManagerElement.getAttribute('data-initial');
            const initialData = initialDataAttr ? JSON.parse(initialDataAttr) : {};
            
            console.log('Создаем Vue приложение...');
            
            // Используем полную версию TaskManager
            const app = createApp(TaskManager, {
                initialData
            });
            
            // Добавляем обработчик ошибок Vue
            app.config.errorHandler = (err, instance, info) => {
                console.error('Vue ошибка:', err);
                console.error('Экземпляр:', instance);
                console.error('Информация:', info);
                
                // Показываем ошибку пользователю
                taskManagerElement.innerHTML = `
                    <div class="bg-red-50 border border-red-200 p-4 rounded mb-4">
                        <h3 class="font-bold text-red-800">Ошибка Vue компонента</h3>
                        <p class="text-red-700">${err.message}</p>
                        <details class="mt-2">
                            <summary class="text-red-600 cursor-pointer">Подробности</summary>
                            <pre class="text-xs bg-red-100 p-2 mt-2 rounded">${err.stack}</pre>
                        </details>
                    </div>
                `;
            };
            
            console.log('Монтируем приложение...');
            app.mount('#task-manager');
            console.log('TaskManager успешно смонтирован');
        } catch (error) {
            console.error('Ошибка при монтировании TaskManager:', error);
            taskManagerElement.innerHTML = `
                <div class="bg-red-50 border border-red-200 p-4 rounded mb-4">
                    <h3 class="font-bold text-red-800">Ошибка инициализации</h3>
                    <p class="text-red-700">${error.message}</p>
                    <details class="mt-2">
                        <summary class="text-red-600 cursor-pointer">Подробности</summary>
                        <pre class="text-xs bg-red-100 p-2 mt-2 rounded">${err.stack}</pre>
                    </details>
                </div>
            `;
        }
    }

    // SimpleModal для тестов
    const simpleModalElement = document.getElementById('simple-modal-test');
    if (simpleModalElement) {
        console.log('Монтируем SimpleModal...');
        try {
            const app = createApp(SimpleModal);
            app.mount('#simple-modal-test');
            console.log('SimpleModal смонтирован');
        } catch (error) {
            console.error('Ошибка при монтировании SimpleModal:', error);
        }
    }
});
