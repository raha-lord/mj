<template>
  <AppLayout title="Задачи">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Задачи
        </h2>
        <a-button type="primary" @click="openCreateModal">
          <template #icon>
            <PlusOutlined />
          </template>
          Создать задачу
        </a-button>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Filters Card -->
        <a-card class="mb-6" title="Фильтры">
          <a-row :gutter="[16, 16]">
            <a-col :xs="24" :md="6">
              <a-input
                v-model:value="filters.search"
                placeholder="Поиск задач..."
                allow-clear
                @change="applyFilters"
              >
                <template #prefix>
                  <SearchOutlined />
                </template>
              </a-input>
            </a-col>
            
            <a-col :xs="24" :md="6">
              <a-select
                v-model:value="filters.status"
                placeholder="Все статусы"
                allow-clear
                style="width: 100%"
                @change="applyFilters"
              >
                <a-select-option
                  v-for="status in statuses"
                  :key="status.id"
                  :value="status.id"
                >
                  {{ status.name }}
                </a-select-option>
              </a-select>
            </a-col>
            
            <a-col :xs="24" :md="6">
              <a-select
                v-model:value="filters.priority"
                placeholder="Все приоритеты"
                allow-clear
                style="width: 100%"
                @change="applyFilters"
              >
                <a-select-option value="low">Низкий</a-select-option>
                <a-select-option value="normal">Обычный</a-select-option>
                <a-select-option value="high">Высокий</a-select-option>
                <a-select-option value="urgent">Срочный</a-select-option>
              </a-select>
            </a-col>
            
            <a-col :xs="24" :md="6">
              <a-space>
                <a-button @click="clearFilters">Очистить</a-button>
              </a-space>
            </a-col>
          </a-row>
        </a-card>

        <!-- Tasks Table -->
        <a-card>
          <a-table
            :columns="columns"
            :data-source="tasks"
            :loading="loading"
            :pagination="{
              current: currentPage,
              pageSize: perPage,
              total: total,
              showSizeChanger: true,
              pageSizeOptions: ['10', '25', '50', '100'],
              showTotal: (total, range) => `${range[0]}-${range[1]} из ${total} задач`,
            }"
            row-key="id"
            @change="handleTableChange"
          >
            <!-- Custom columns -->
            <template #bodyCell="{ column, record }">
              <template v-if="column.key === 'name'">
                <div>
                  <div class="font-medium">{{ record.name }}</div>
                  <div class="text-sm text-gray-500" v-if="record.description">
                    {{ record.description.substring(0, 100) }}{{ record.description.length > 100 ? '...' : '' }}
                  </div>
                </div>
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
              
              <template v-else-if="column.key === 'actions'">
                <a-space>
                  <a-button type="link" size="small" @click="viewTask(record.id)">
                    Просмотр
                  </a-button>
                  <a-button type="link" size="small" @click="editTask(record.id)">
                    Редактировать
                  </a-button>
                  <a-button type="link" size="small" danger @click="deleteTask(record.id)">
                    Удалить
                  </a-button>
                </a-space>
              </template>
            </template>
          </a-table>
        </a-card>
      </div>
    </div>
    <!-- Task Modal -->
    <TaskModal
      :is-open="modalState.isOpen"
      :mode="modalState.mode"
      :task="modalState.task"
      :loading="modalState.loading"
      :projects="projects"
      :statuses="statuses"
      :sizes="[]"
      :users="users"
      @close="closeModal"
      @submit="handleModalSubmit"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'
import TaskModal from '../../Components/TaskModal.vue'
import { PlusOutlined, SearchOutlined } from '@ant-design/icons-vue'

// Props from controller
const props = defineProps({
  tasks: {
    type: Array,
    default: () => []
  },
  statuses: {
    type: Array,
    default: () => []
  },
  projects: {
    type: Array,
    default: () => []
  },
  users: {
    type: Array,
    default: () => []
  },
  pagination: {
    type: Object,
    default: () => ({
      current_page: 1,
      per_page: 25,
      total: 0
    })
  }
})

// Reactive state
const loading = ref(false)
const filters = ref({
  search: '',
  status: null,
  priority: null
})

const modalState = ref({
  isOpen: false,
  mode: 'create',
  task: null,
  loading: false
})

// Computed
const currentPage = computed(() => props.pagination.current_page || 1)
const perPage = computed(() => props.pagination.per_page || 25)
const total = computed(() => props.pagination.total || 0)

// Table columns
const columns = [
  {
    title: 'Задача',
    key: 'name',
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
    width: '12%'
  },
  {
    title: 'Приоритет',
    key: 'priority',
    width: '12%'
  },
  {
    title: 'Действия',
    key: 'actions',
    width: '21%'
  }
]

// Helper functions
const getStatusColor = (status) => {
  const colors = {
    'new': 'cyan',
    'in-progress': 'geekblue',
    'completed': 'green',
    'cancelled': 'red'
  }
  return colors[status] || 'default'
}

const getPriorityColor = (priority) => {
  const colors = {
    'low': 'green',
    'normal': 'geekblue', 
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

// Event handlers
const applyFilters = () => {
  loading.value = true
  router.get('/tasks', filters.value, {
    preserveState: true,
    onFinish: () => {
      loading.value = false
    }
  })
}

const clearFilters = () => {
  filters.value = {
    search: '',
    status: null,
    priority: null
  }
  applyFilters()
}

const handleTableChange = (pagination) => {
  loading.value = true
  router.get('/tasks', {
    ...filters.value,
    page: pagination.current,
    per_page: pagination.pageSize
  }, {
    preserveState: true,
    onFinish: () => {
      loading.value = false
    }
  })
}

const openCreateModal = () => {
  modalState.value = {
    isOpen: true,
    mode: 'create',
    task: null,
    loading: false
  }
}

const viewTask = (id) => {
  const task = props.tasks.find(t => t.id === id)
  if (task) {
    modalState.value = {
      isOpen: true,
      mode: 'view',
      task: task,
      loading: false
    }
  }
}

const editTask = (id) => {
  const task = props.tasks.find(t => t.id === id)
  if (task) {
    modalState.value = {
      isOpen: true,
      mode: 'edit',
      task: task,
      loading: false
    }
  }
}

const closeModal = () => {
  modalState.value = {
    isOpen: false,
    mode: 'create',
    task: null,
    loading: false
  }
}

const handleModalSubmit = (data) => {
  // Refresh page after successful submission
  router.reload({ only: ['tasks'] })
}

const deleteTask = (id) => {
  // TODO: Показать подтверждение удаления
  console.log('Delete task', id)
}
</script>