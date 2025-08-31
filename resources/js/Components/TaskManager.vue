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
      <Button
        @click="taskModal.openCreateModal()"
        variant="primary"
        size="md"
      >
        <template #icon>
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
        </template>
        Создать задачу
      </Button>
    </div>

    <!-- Ant Design Фильтры -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

          <!-- Поиск -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Поиск
            </label>
            <a-input
              v-model:value="tasksList.filters.search"
              placeholder="Поиск задач..."
              allow-clear
              @change="debouncedFilter"
            >
              <template #prefix>
                <SearchOutlined />
              </template>
            </a-input>
          </div>

          <!-- Статус -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Статус
            </label>
            <a-select
              v-model:value="tasksList.filters.status"
              placeholder="Все статусы"
              allow-clear
              style="width: 100%"
              @change="tasksList.applyFilters"
            >
              <a-select-option
                v-for="status in statuses"
                :key="status.slug"
                :value="status.slug"
              >
                {{ status.name }}
              </a-select-option>
            </a-select>
          </div>

          <!-- Приоритет -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Приоритет
            </label>
            <a-select
              v-model:value="tasksList.filters.priority"
              placeholder="Все приоритеты"
              allow-clear
              style="width: 100%"
              @change="tasksList.applyFilters"
            >
              <a-select-option
                v-for="priority in PRIORITY_OPTIONS"
                :key="priority.value"
                :value="priority.value"
              >
                {{ priority.label }}
              </a-select-option>
            </a-select>
          </div>

          <!-- Кнопки -->
          <div class="flex items-end space-x-2">
            <a-button
              type="primary"
              @click="tasksList.applyFilters"
            >
              Фильтр
            </a-button>
            <a-button
              @click="tasksList.clearFilters"
            >
              Очистить
            </a-button>
          </div>
        </div>
      </div>
    </div>

    <!-- Ant Design Table -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
      <a-table
        :columns="tableColumns"
        :data-source="tasksList.tasks.value"
        :loading="tasksList.loading.value"
        :pagination="{
          current: tasksList.filters.page,
          pageSize: tasksList.filters.per_page,
          total: tasksList.pagination.value?.total || 0,
          showSizeChanger: true,
          pageSizeOptions: ['10', '25', '50', '100'],
          showTotal: (total, range) => `${range[0]}-${range[1]} из ${total} задач`,
          onChange: tasksList.changePage,
          onShowSizeChange: tasksList.changePerPage
        }"
        row-key="id"
        size="middle"
      >
        <!-- Кастомная колонка для задачи -->
        <template #bodyCell="{ column, record, text }">
          <template v-if="column.key === 'task'">
            <div>
              <div class="font-medium text-gray-900 dark:text-white">{{ record.name }}</div>
              <div class="text-sm text-gray-500 dark:text-gray-400" v-if="record.description">
                {{ record.description.substring(0, 100) }}{{ record.description.length > 100 ? '...' : '' }}
              </div>
            </div>
          </template>

          <template v-else-if="column.key === 'project'">
            <span class="text-sm">{{ record.project?.name || '—' }}</span>
          </template>

          <template v-else-if="column.key === 'status'">
            <a-tag :color="getStatusColor(record.status?.slug)">
              {{ record.status?.name || '—' }}
            </a-tag>
          </template>

          <template v-else-if="column.key === 'priority'">
            <a-tag :color="getPriorityColor(record.priority)">
              {{ getPriorityLabel(record.priority) }}
            </a-tag>
          </template>

          <template v-else-if="column.key === 'size'">
            <span class="text-sm">{{ record.size?.code || '—' }}</span>
          </template>

          <template v-else-if="column.key === 'actions'">
            <a-space>
              <a-button
                type="link"
                size="small"
                @click="taskModal.openViewModal(record.id)"
              >
                Просмотр
              </a-button>
              <a-button
                type="link"
                size="small"
                @click="taskModal.openEditModal(record.id)"
              >
                Редактировать
              </a-button>
              <a-button
                type="link"
                size="small"
                danger
                @click="handleDeleteTask(record.id)"
              >
                Удалить
              </a-button>
            </a-space>
          </template>
        </template>
      </a-table>
    </div>

    <!-- Скелетон подготовки модалки -->
    <div v-if="taskModal.preparing.value" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all max-w-xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Загрузка данных задачи...</h3>
        </div>
        <!-- Content -->
        <div class="px-6 py-4">
          <TaskFormSkeleton />
        </div>
      </div>
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
import Button from './shared/Button.vue'
import FormSelect from './shared/FormSelect.vue'
import SearchInput from './shared/SearchInput.vue'
import TasksTableSkeleton from './shared/TasksTableSkeleton.vue'
import TaskFormSkeleton from './shared/TaskFormSkeleton.vue'
import { SearchOutlined } from '@ant-design/icons-vue'

// Регистрируем компоненты
const components = {
  SearchOutlined
}

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

// Колонки таблицы
const tableColumns = [
  {
    title: 'Задача',
    key: 'task',
    dataIndex: 'name',
    width: '40%'
  },
  {
    title: 'Проект',
    key: 'project',
    dataIndex: ['project', 'name'],
    width: '15%'
  },
  {
    title: 'Статус',
    key: 'status',
    dataIndex: ['status', 'name'],
    width: '12%'
  },
  {
    title: 'Приоритет',
    key: 'priority',
    dataIndex: 'priority',
    width: '12%'
  },
  {
    title: 'Размер',
    key: 'size',
    dataIndex: ['size', 'code'],
    width: '10%'
  },
  {
    title: 'Действия',
    key: 'actions',
    width: '11%'
  }
]

// Helper функции для цветов
const getStatusColor = (status) => {
  const colors = {
    'new': 'blue',
    'in-progress': 'orange',
    'completed': 'green',
    'cancelled': 'red'
  }
  return colors[status] || 'default'
}

const getPriorityColor = (priority) => {
  const colors = {
    'low': 'green',
    'normal': 'blue',
    'high': 'orange',
    'urgent': 'red'
  }
  return colors[priority] || 'default'
}

const getPriorityLabel = (priority) => {
  const labels = {
    'low': 'Низкий',
    'normal': 'Обычный',
    'high': 'Высокий',
    'urgent': 'Срочный'
  }
  return labels[priority] || priority
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