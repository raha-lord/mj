<template>
  <div>
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
        Задачи
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

    <!-- Фильтры -->
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
                v-model="filters.status"
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                @change="applyFilters"
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
                v-model="filters.priority"
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                @change="applyFilters"
              >
                <option value="">Все приоритеты</option>
                <option value="low">Низкий</option>
                <option value="normal">Обычный</option>
                <option value="high">Высокий</option>
                <option value="urgent">Срочный</option>
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
      
      <div v-else id="tasksTable" v-html="tasksHtml"></div>
    </div>

    <!-- Модальные окна -->
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

    <!-- Единое модальное окно просмотра/редактирования задачи -->
    <Modal
      :is-open="modals.view.isOpen"
      :title="viewMode === 'edit' ? 'Редактирование задачи' : 'Просмотр задачи'"
      size="2xl"
      max-height="80vh"
      :show-footer="true"
      :show-cancel-button="false"
      :show-confirm-button="false"
      @close="closeViewModal"
    >
      <!-- Loading state -->
      <div v-if="viewTaskLoading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <span class="ml-2 text-gray-600 dark:text-gray-300">Загрузка задачи...</span>
      </div>

      <!-- Task content -->
      <div v-else-if="modals.view.data" class="space-y-6">
        <!-- Mode switcher -->
        <div class="flex space-x-2 mb-4">
          <button
            @click="handleViewModeSwitch('view')"
            :class="viewMode === 'view' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-300'"
            class="px-4 py-2 rounded-md transition-colors duration-200"
          >
            Просмотр
          </button>
          <button
            @click="handleViewModeSwitch('edit')"
            :class="viewMode === 'edit' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-300'"
            class="px-4 py-2 rounded-md transition-colors duration-200"
          >
            Редактирование
          </button>
        </div>

        <!-- View mode content -->
        <TaskViewContent
          v-if="viewMode === 'view'"
          :task="modals.view.data"
        />

        <!-- Edit mode content -->
        <TaskEditContent
          v-else
          :task="modals.view.data"
          :projects="projects"
          :statuses="statuses"
          :sizes="sizes"
          :users="users"
          @close="handleEditModalClose"
          @submit="handleTaskSubmit"
          ref="editTaskModalRef"
        />
      </div>

      <template #footer>
        <div class="flex justify-between w-full">
          <button
            v-if="modals.view.data && viewMode === 'view'"
            @click="handleViewModeSwitch('edit')"
            class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          >
            Редактировать
          </button>
          <div v-else></div>
          
          <button
            @click="closeViewModal"
            class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          >
            Закрыть
          </button>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue'
import Modal from './Modal.vue'
import TaskModal from './TaskModal.vue'
import TaskViewModal from './TaskViewModal.vue'
import TaskViewContent from './TaskViewContent.vue'
import TaskEditContent from './TaskEditContent.vue'
import { useTasks } from '../composables/useTasks-fixed.js'

const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({})
  }
})

// Composables
const { loading: tasksLoading, createTask, updateTask, getTask, getTasks } = useTasks()

// Reactive data
const filters = reactive({
  search: '',
  status: '',
  priority: ''
})

const modals = reactive({
  task: {
    isOpen: false,
    data: null
  },
  view: {
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
const taskViewHtml = ref('')
const viewTaskLoading = ref(false)
const viewMode = ref('view')
const taskModalRef = ref(null)
const editTaskModalRef = ref(null)

// Static data (получаем из пропсов или API)
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

// Debounced filter function
let filterTimeout = null
const debouncedFilter = () => {
  clearTimeout(filterTimeout)
  filterTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

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
    console.log('📝 ОБНОВЛЕНИЕ СПИСКА ЗАДАЧ - это должно происходить ТОЛЬКО при сохранении или фильтрации!')
    const result = await getTasks(filters)
    tasksHtml.value = result.html

    // Обновляем URL
    const url = new URL(window.location.href)
    Object.entries(filters).forEach(([key, value]) => {
      if (value) {
        url.searchParams.set(key, value)
      } else {
        url.searchParams.delete(key)
      }
    })
    window.history.pushState({}, '', url)
  } catch (error) {
    console.error('Ошибка загрузки задач:', error)
    showNotification('Ошибка при загрузке задач', 'error')
  }
}

const applyFilters = () => {
  loadTasks()
}

const clearFilters = () => {
  Object.keys(filters).forEach(key => {
    filters[key] = ''
  })
  loadTasks()
}

const openCreateModal = () => {
  modals.task.data = null
  modals.task.isOpen = true
}

const openEditModal = (task) => {
  modals.task.data = task
  modals.task.isOpen = true
}

const closeTaskModal = () => {
  modals.task.isOpen = false
  modals.task.data = null
}

const openViewModal = async (taskId) => {
  console.log('🔍 Открытие модального окна просмотра задачи (БЕЗ обновления списка):', taskId)
  modals.view.isOpen = true
  viewTaskLoading.value = true
  viewMode.value = 'view'

  try {
    const task = await getTask(taskId)
    modals.view.data = task
    console.log('✅ Задача загружена для просмотра (список задач НЕ обновлялся)')
  } catch (error) {
    console.error('Ошибка загрузки задачи:', error)
    showNotification('Ошибка при загрузке задачи', 'error')
    closeViewModal()
  } finally {
    viewTaskLoading.value = false
  }
}

const closeViewModal = () => {
  modals.view.isOpen = false
  modals.view.data = null
  taskViewHtml.value = ''
}

const switchToEditMode = () => {
  viewMode.value = 'edit'
}

const handleViewModeSwitch = (mode) => {
  viewMode.value = mode
}

const handleEditModalClose = () => {
  viewMode.value = 'view'
}

const renderTaskView = (task) => {
  // Генерируем HTML для просмотра задачи
  taskViewHtml.value = `
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg space-y-6">
      <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">${task.name}</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">ID: #${task.id}</p>
      </div>
      
      ${task.description ? `
        <div>
          <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Описание</h3>
          <p class="text-gray-700 dark:text-gray-300">${task.description}</p>
        </div>
      ` : ''}
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
          <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Проект</span>
            <p class="text-gray-900 dark:text-white">${task.project ? task.project.name : '—'}</p>
          </div>
          
          <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Статус</span>
            <div class="mt-1">
              ${task.status ? `
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                      style="background-color: ${task.status.color}20; color: ${task.status.color};">
                  ${task.status.name}
                </span>
              ` : '—'}
            </div>
          </div>
          
          <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Приоритет</span>
            <div class="mt-1">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium priority-${task.priority}">
                ${task.priority}
              </span>
            </div>
          </div>
          
          ${task.size ? `
            <div>
              <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Размер</span>
              <p class="text-gray-900 dark:text-white">${task.size.code} - ${task.size.name}</p>
            </div>
          ` : ''}
        </div>
        
        <div class="space-y-4">
          ${task.estimated_hours ? `
            <div>
              <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Оценка времени</span>
              <p class="text-gray-900 dark:text-white">${task.estimated_hours} часов</p>
            </div>
          ` : ''}
          
          ${task.due_date ? `
            <div>
              <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Срок выполнения</span>
              <p class="text-gray-900 dark:text-white">${new Date(task.due_date).toLocaleDateString('ru-RU')}</p>
            </div>
          ` : ''}
          
          <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Создана</span>
            <p class="text-gray-900 dark:text-white">${new Date(task.created_at).toLocaleDateString('ru-RU')}</p>
          </div>
          
          <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Автор</span>
            <p class="text-gray-900 dark:text-white">${task.created_by ? task.created_by.name : '—'}</p>
          </div>
        </div>
      </div>
      
      ${task.assignees && task.assignees.length > 0 ? `
        <div>
          <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Исполнители</span>
          <div class="mt-2 flex flex-wrap gap-2">
            ${task.assignees.map(assignee => `
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                ${assignee.name}
              </span>
            `).join('')}
          </div>
        </div>
      ` : ''}
    </div>
  `
}

const handleTaskSubmit = async ({ data, isEdit, taskId }) => {
  try {
    let result
    const modalRef = isEdit ? editTaskModalRef.value : taskModalRef.value

    if (modalRef) {
      modalRef.setLoading(true)
    }

    if (isEdit) {
      result = await updateTask(taskId, data)
      // Обновляем данные задачи в модальном окне просмотра
      if (result.success && result.data) {
        modals.view.data = result.data
      }
    } else {
      result = await createTask(data)
    }

    showNotification(result.message || (isEdit ? 'Задача обновлена' : 'Задача создана'))

    // Закрываем модальные окна
    if (!isEdit) {
      closeTaskModal()
      // Обновляем список задач только при создании новой задачи
      if (tasksHtml.value) {
        await loadTasks()
      }
    } else {
      // При редактировании возвращаемся к просмотру
      viewMode.value = 'view'
      // Обновляем список задач только при сохранении изменений
      if (tasksHtml.value) {
        await loadTasks()
      }
    }

  } catch (error) {
    console.error('Ошибка сохранения задачи:', error)

    const modalRef = isEdit ? editTaskModalRef.value : taskModalRef.value
    if (modalRef && error.errors) {
      modalRef.setErrors(error.errors)
    }

    const message = error.message || 'Ошибка при сохранении задачи'
    showNotification(message, 'error')
  } finally {
    const modalRef = isEdit ? editTaskModalRef.value : taskModalRef.value
    if (modalRef) {
      modalRef.setLoading(false)
    }
  }
}

// Подключение обработчиков кликов для таблицы
let tableClickHandlerAttached = false

const attachTableEventHandlers = () => {
  if (tableClickHandlerAttached) {
    console.log('Table click handlers already attached')
    return
  }
  
  // Добавляем обработчик к документу только один раз
  document.addEventListener('click', handleTableClicks)
  tableClickHandlerAttached = true
  console.log('Table click handlers attached')
}

const handleTableClicks = (e) => {
  // Обработчики для кнопок просмотра
  if (e.target.matches('[data-task-view]') || e.target.closest('[data-task-view]')) {
    e.preventDefault()
    e.stopPropagation()
    const button = e.target.matches('[data-task-view]') ? e.target : e.target.closest('[data-task-view]')
    const taskId = button.getAttribute('data-task-view')
    console.log('Открытие просмотра задачи:', taskId)
    openViewModal(taskId)
  }
  
  // Обработчики для кнопок редактирования
  if (e.target.matches('[data-task-edit]') || e.target.closest('[data-task-edit]')) {
    e.preventDefault()
    e.stopPropagation()
    const button = e.target.matches('[data-task-edit]') ? e.target : e.target.closest('[data-task-edit]')
    const taskId = button.getAttribute('data-task-edit')
    console.log('Открытие редактирования задачи:', taskId)
    openViewModal(taskId).then(() => {
      setTimeout(() => switchToEditMode(), 100)
    })
  }

  // Обработчик для клика по строке таблицы (просмотр)
  const taskRow = e.target.closest('[data-task-id]')
  if (taskRow && !e.target.closest('button') && !e.target.closest('a')) {
    e.stopPropagation()
    const taskId = taskRow.getAttribute('data-task-id')
    console.log('Клик по строке задачи:', taskId)
    openViewModal(taskId)
  }
}

// Lifecycle
onMounted(async () => {
  attachTableEventHandlers()
  // Автоматически загружаем задачи при открытии страницы
  await loadTasks()
})

// Expose methods for global access
window.openTaskViewModal = openViewModal
window.openTaskEditModal = (taskId) => {
  openViewModal(taskId).then(() => {
    setTimeout(() => switchToEditMode(), 100)
  })
}
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