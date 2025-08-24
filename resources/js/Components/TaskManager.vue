<template>
  <div>
    <!-- Уведомления -->
    <div v-if="notifications.notifications.value.length > 0">
      <div
        v-for="notification in notifications.notifications.value"
        :key="notification.id"
        :class="getNotificationClasses(notification.type)"
        class="mb-4 px-4 py-3 rounded-lg relative"
      >
        <span class="block sm:inline">{{ notification.message }}</span>
        <button
          type="button"
          class="absolute top-0 bottom-0 right-0 px-4 py-3"
          @click="notifications.hideNotification(notification.id)"
        >
          <svg class="fill-current h-6 w-6" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Кнопка создания задачи -->
    <div class="flex justify-between items-center mb-6">
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Задачи
      </h2>
      <button
        @click="taskModal.openCreateModal"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Создать задачу
      </button>
    </div>

    <!-- Фильтры -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
      <div class="p-6">
        <form @submit.prevent="tasksList.applyFilters" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            
            <!-- Поиск -->
            <div>
              <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Поиск
              </label>
              <input
                id="search"
                v-model="tasksList.filters.search"
                type="text"
                placeholder="Поиск задач..."
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                @input="debouncedFilter"
              />
            </div>

            <!-- Статус -->
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Статус
              </label>
              <select
                id="status"
                v-model="tasksList.filters.status"
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                @change="tasksList.applyFilters"
              >
                <option value="">Все статусы</option>
                <option v-for="status in statuses" :key="status.slug" :value="status.slug">
                  {{ status.name }}
                </option>
              </select>
            </div>

            <!-- Приоритет -->
            <div>
              <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Приоритет
              </label>
              <select
                id="priority"
                v-model="tasksList.filters.priority"
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                @change="tasksList.applyFilters"
              >
                <option value="">Все приоритеты</option>
                <option v-for="priority in PRIORITY_OPTIONS" :key="priority.value" :value="priority.value">
                  {{ priority.label }}
                </option>
              </select>
            </div>

            <!-- Кнопки -->
            <div class="flex items-end space-x-2">
              <button
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors duration-200"
              >
                Фильтр
              </button>
              <button
                type="button"
                @click="tasksList.clearFilters"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-colors duration-200"
              >
                Очистить
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Таблица задач -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
      <!-- Индикатор загрузки -->
      <div v-if="tasksList.loading.value" class="flex justify-center items-center py-12">
        <LoadingSpinner size="md" :show-text="true" text="Загрузка задач..." />
      </div>
      
      <!-- Vue компоненты вместо HTML -->
      <TasksTable
        v-else
        :tasks="tasksList.tasks.value"
        :loading="tasksList.loading.value"
        @view="taskModal.openViewModal"
        @edit="taskModal.openEditModal"
        @delete="handleDeleteTask"
      />
    </div>

    <!-- Модальное окно задачи -->
    <TaskModal
      :is-open="taskModal.isOpen.value"
      :task="taskModal.task.value"
      :loading="taskModal.loading.value"
      :mode="taskModal.mode.value"
      :projects="projects"
      :statuses="statuses"
      :sizes="sizes"
      :users="users"
      @close="taskModal.closeModal"
      @submit="handleTaskSave"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { debounce } from '@/utils/helpers.js'
import { PRIORITY_OPTIONS, NOTIFICATION_CLASSES } from '@/utils/constants.js'
import { useTasksList } from '@/composables/features/useTasksList.js'
import { useTaskModal } from '@/composables/features/useTaskModal.js'
import { useNotifications } from '@/composables/ui/useNotifications.js'
import TaskModal from './TaskModal.vue'
import TasksTable from './tasks/TasksTable.vue'
import LoadingSpinner from './shared/LoadingSpinner.vue'

const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({})
  }
})

// Features слой - композиция простых блоков
const tasksList = useTasksList()
const taskModal = useTaskModal()
const notifications = useNotifications()

// Static data
const projects = ref(props.initialData.projects || [])
const statuses = ref(props.initialData.statuses || [])
const sizes = ref(props.initialData.sizes || [])
const users = ref(props.initialData.users || [])

// UI helpers
const getNotificationClasses = (type) => {
  return `border rounded-lg ${NOTIFICATION_CLASSES[type] || NOTIFICATION_CLASSES.success}`
}

// Debounced filter
const debouncedFilter = debounce(() => {
  tasksList.applyFilters()
}, 500)

// Event handlers - явные действия
const handleTaskSave = async (taskData) => {
  const success = await taskModal.saveTask(taskData)
  if (success) {
    // Явно обновляем список после сохранения
    await tasksList.refreshTasks()
  }
}

const handleDeleteTask = async (taskId) => {
  if (confirm('Вы уверены, что хотите удалить эту задачу?')) {
    // TODO: Реализовать удаление через API
    notifications.showNotification('Функция удаления будет реализована', 'warning')
  }
}

// Lifecycle
onMounted(async () => {
  await tasksList.loadTasks()
})

// Global access (legacy support)
window.openTaskViewModal = taskModal.openViewModal
window.openTaskEditModal = taskModal.openEditModal
</script>

<style scoped>
.priority-urgent {
  background-color: rgb(254, 226, 226);
  color: rgb(153, 27, 27);
}
.priority-high {
  background-color: rgb(255, 237, 213);
  color: rgb(154, 52, 18);
}
.priority-normal {
  background-color: rgb(219, 234, 254);
  color: rgb(30, 64, 175);
}
.priority-low {
  background-color: rgb(220, 252, 231);
  color: rgb(22, 101, 52);
}
</style>