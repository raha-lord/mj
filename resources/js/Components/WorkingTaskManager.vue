<template>
  <div>
    <!-- Debug info -->
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded">
      <h3 class="font-bold text-green-800">✅ Рабочий TaskManager</h3>
      <p class="text-green-700">TaskModal состояние: {{ modals.task.isOpen ? 'открыто' : 'закрыто' }}</p>
      <p class="text-green-700">Загрузка: {{ tasksLoading ? 'да' : 'нет' }}</p>
    </div>

    <!-- Уведомления -->
    <div v-if="notification.show" :class="notificationClasses" class="mb-4 px-4 py-3 rounded-lg relative">
      <span class="block sm:inline">{{ notification.message }}</span>
      <button
        type="button"
        class="absolute top-0 bottom-0 right-0 px-4 py-3"
        @click="hideNotification"
      >
        <svg class="fill-current h-6 w-6" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
          <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
        </svg>
      </button>
    </div>

    <!-- Кнопка создания задачи -->
    <div class="flex justify-between items-center mb-6">
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Задачи (Рабочая версия)
      </h2>
      <button
        @click="openCreateModal"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Создать задачу
      </button>
    </div>

    <!-- Простые фильтры -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
      <div class="p-6">
        <form @submit.prevent="applyFilters" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Поиск -->
            <div>
              <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Поиск
              </label>
              <input
                id="search"
                v-model="filters.search"
                type="text"
                placeholder="Поиск задач..."
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              />
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
                @click="clearFilters"
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
      <div v-if="tasksLoading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <span class="ml-2 text-gray-600 dark:text-gray-300">Загрузка...</span>
      </div>
      
      <div v-else-if="tasksHtml" v-html="tasksHtml"></div>
      
      <div v-else class="p-6 text-center text-gray-500">
        Нажмите "Фильтр" чтобы загрузить задачи
      </div>
    </div>

    <!-- TaskModal -->
    <TaskModal
      :is-open="modals.task.isOpen"
      :task="modals.task.data"
      :projects="projects"
      :statuses="statuses"
      :sizes="sizes"
      :users="users"
      @close="closeTaskModal"
      @submit="handleTaskSubmit"
      ref="taskModalRef"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import TaskModal from './TaskModal.vue'
import { useTasks } from '../composables/useTasks-fixed.js'

console.log('WorkingTaskManager загружается...')

const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({})
  }
})

// Composables
const { loading: tasksLoading, createTask, updateTask, getTasks } = useTasks()

// Reactive data
const filters = reactive({
  search: ''
})

const modals = reactive({
  task: {
    isOpen: false,
    data: null
  }
})

const notification = reactive({
  show: false,
  message: '',
  type: 'success'
})

// Refs
const tasksHtml = ref('')
const taskModalRef = ref(null)

// Static data (получаем из пропсов)
const projects = ref(props.initialData.projects || [])
const statuses = ref(props.initialData.statuses || [])
const sizes = ref(props.initialData.sizes || [])
const users = ref(props.initialData.users || [])

// Computed
const notificationClasses = computed(() => {
  const baseClasses = 'border rounded-lg'
  const typeClasses = {
    success: 'bg-green-50 border-green-200 text-green-700',
    error: 'bg-red-50 border-red-200 text-red-700',
    warning: 'bg-yellow-50 border-yellow-200 text-yellow-700'
  }
  return `${baseClasses} ${typeClasses[notification.type] || typeClasses.success}`
})

// Methods
const showNotification = (message, type = 'success') => {
  notification.message = message
  notification.type = type
  notification.show = true
  
  setTimeout(() => {
    notification.show = false
  }, 5000)
}

const hideNotification = () => {
  notification.show = false
}

const loadTasks = async () => {
  try {
    console.log('Загрузка задач с фильтрами:', filters)
    const result = await getTasks(filters)
    tasksHtml.value = result.html
  } catch (error) {
    console.error('Ошибка загрузки задач:', error)
    showNotification('Ошибка при загрузке задач: ' + (error.message || 'Неизвестная ошибка'), 'error')
  }
}

const applyFilters = () => {
  console.log('Применение фильтров...')
  loadTasks()
}

const clearFilters = () => {
  console.log('Очистка фильтров...')
  filters.search = ''
  loadTasks()
}

const openCreateModal = () => {
  console.log('Открытие модалки создания задачи')
  modals.task.data = null
  modals.task.isOpen = true
}

const closeTaskModal = () => {
  console.log('Закрытие модалки задачи')
  modals.task.isOpen = false
  modals.task.data = null
}

const handleTaskSubmit = async ({ data, isEdit, taskId }) => {
  console.log('Обработка отправки задачи:', { data, isEdit, taskId })
  
  try {
    let result
    const modalRef = taskModalRef.value
    
    if (modalRef) {
      modalRef.setLoading(true)
    }
    
    if (isEdit) {
      result = await updateTask(taskId, data)
    } else {
      result = await createTask(data)
    }
    
    showNotification(result.message || (isEdit ? 'Задача обновлена' : 'Задача создана'))
    closeTaskModal()
    
    // Обновляем список задач если он загружен
    if (tasksHtml.value) {
      await loadTasks()
    }
    
  } catch (error) {
    console.error('Ошибка сохранения задачи:', error)
    
    const modalRef = taskModalRef.value
    if (modalRef && error.errors) {
      modalRef.setErrors(error.errors)
    }
    
    const message = error.message || 'Ошибка при сохранении задачи'
    showNotification(message, 'error')
  } finally {
    const modalRef = taskModalRef.value
    if (modalRef) {
      modalRef.setLoading(false)
    }
  }
}

console.log('WorkingTaskManager готов')
console.log('Данные из props:', props.initialData)
</script>