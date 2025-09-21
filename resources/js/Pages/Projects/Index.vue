<template>
  <AppLayout title="Проекты">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Проекты
        </h2>
        <a-button 
          v-if="userPermissions.can_create_projects" 
          type="primary" 
          @click="openCreateModal"
        >
          <template #icon>
            <PlusOutlined />
          </template>
          Создать проект
        </a-button>
      </div>
    </template>

    <div>
      <div>
        <!-- Filters Card -->
        <a-card class="mb-6" title="Фильтры">
          <a-row :gutter="[16, 16]">
            <a-col :xs="24" :md="8">
              <a-input
                v-model:value="filters.search"
                placeholder="Поиск проектов..."
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
                <a-select-option value="active">Активный</a-select-option>
                <a-select-option value="inactive">Неактивный</a-select-option>
                <a-select-option value="completed">Завершен</a-select-option>
              </a-select>
            </a-col>

            <a-col :xs="24" :md="6">
              <a-select
                v-model:value="filters.visibility"
                placeholder="Все типы"
                allow-clear
                style="width: 100%"
                @change="applyFilters"
              >
                <a-select-option value="public">Публичные</a-select-option>
                <a-select-option value="private">Приватные</a-select-option>
              </a-select>
            </a-col>
            
            <a-col :xs="24" :md="8">
              <a-space>
                <a-button @click="clearFilters">Очистить</a-button>
              </a-space>
            </a-col>
          </a-row>
        </a-card>

        <!-- Projects Table -->
        <a-card>
          <a-table
            :columns="columns"
            :data-source="projects"
            :loading="loading"
            :pagination="{
              current: currentPage,
              pageSize: perPage,
              total: total,
              showSizeChanger: true,
              pageSizeOptions: ['10', '25', '50', '100'],
              showTotal: (total, range) => `${range[0]}-${range[1]} из ${total} проектов`,
            }"
            row-key="id"
            @change="handleTableChange"
          >
            <!-- Custom columns -->
            <template #bodyCell="{ column, record }">
              <template v-if="column.key === 'name'">
                <div>
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-medium">{{ record.name }}</span>
                    
                    <!-- Бейдж приватности -->
                    <a-tag 
                      v-if="record.visibility === 'private'" 
                      color="orange" 
                      size="small"
                    >
                      <LockOutlined class="mr-1" />
                      Приватный
                    </a-tag>
                    
                    <!-- Бейдж роли пользователя -->
                    <a-tag 
                      v-if="record.user_role" 
                      :color="getRoleColor(record.user_role)" 
                      size="small"
                    >
                      <component :is="getRoleIcon(record.user_role)" class="mr-1" />
                      {{ getRoleLabel(record.user_role) }}
                    </a-tag>
                    
                    <!-- Бейдж для админов организации (если нет роли в проекте) -->
                    <a-tooltip 
                      v-else-if="record.can_manage && !record.user_role" 
                      title="Администратор организации"
                      placement="top"
                    >
                      <a-tag color="purple" size="small" class="cursor-help">
                        <CrownOutlined />
                      </a-tag>
                    </a-tooltip>
                  </div>
                  <div class="text-sm text-gray-500" v-if="record.description">
                    {{ record.description.substring(0, 100) }}{{ record.description.length > 100 ? '...' : '' }}
                  </div>
                </div>
              </template>
              
              <template v-else-if="column.key === 'status'">
                <a-tag :color="getStatusColor(record.status)">
                  {{ getStatusLabel(record.status) }}
                </a-tag>
              </template>
              
              <template v-else-if="column.key === 'tasks_count'">
                <a-badge :count="record.tasks_count || 0" :number-style="{backgroundColor: '#108ee9'}" />
              </template>
              
              <template v-else-if="column.key === 'actions'">
                <a-space>
                  <!-- Просмотр доступен всем кто может видеть проект -->
                  <a-tooltip title="Просмотр проекта">
                    <a-button 
                      type="text" 
                      size="small" 
                      @click="viewProject(record.id)"
                      class="flex items-center justify-center"
                    >
                      <EyeOutlined class="text-blue-500" />
                    </a-button>
                  </a-tooltip>
                  
                  <!-- Редактирование только для тех кто может управлять проектом -->
                  <a-tooltip v-if="record.can_manage" title="Редактировать проект">
                    <a-button 
                      type="text" 
                      size="small" 
                      @click="editProject(record.id)"
                      class="flex items-center justify-center"
                    >
                      <EditOutlined class="text-green-500" />
                    </a-button>
                  </a-tooltip>
                  
                  <!-- Удаление только для тех кто может управлять проектом -->
                  <a-tooltip v-if="record.can_manage" title="Удалить проект">
                    <a-button 
                      type="text" 
                      size="small" 
                      @click="deleteProject(record.id)"
                      class="flex items-center justify-center"
                    >
                      <DeleteOutlined class="text-red-500" />
                    </a-button>
                  </a-tooltip>
                </a-space>
              </template>
              
              <template v-else-if="column.key === 'created_at'">
                <div class="text-sm">
                  <div class="font-medium text-gray-900">
                    {{ formatDate(record.created_at) }}
                  </div>
                  <div class="text-gray-500">
                    {{ formatTime(record.created_at) }}
                  </div>
                </div>
              </template>
            </template>
          </a-table>
        </a-card>
      </div>
    </div>

    <!-- Project Modal -->
    <ProjectModal
      :is-open="modalState.isOpen"
      :mode="modalState.mode"
      :project="modalState.project"
      :loading="modalState.loading"
      @close="closeModal"
      @submit="handleModalSubmit"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'
import ProjectModal from '../../Components/ProjectModal.vue'
import { 
  PlusOutlined, 
  SearchOutlined, 
  LockOutlined, 
  CrownOutlined,
  UserOutlined,
  SettingOutlined,
  EyeOutlined,
  EditOutlined,
  DeleteOutlined
} from '@ant-design/icons-vue'

// Props from controller
const props = defineProps({
  projects: {
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
  },
  userPermissions: {
    type: Object,
    default: () => ({
      can_create_projects: false
    })
  }
})

// Reactive state
const loading = ref(false)
const filters = ref({
  search: '',
  status: null,
  visibility: null
})

const modalState = ref({
  isOpen: false,
  mode: 'create',
  project: null,
  loading: false
})

// Computed
const currentPage = computed(() => props.pagination.current_page || 1)
const perPage = computed(() => props.pagination.per_page || 25)
const total = computed(() => props.pagination.total || 0)

// Table columns
const columns = [
  {
    title: 'Проект',
    key: 'name',
    dataIndex: 'name',
    width: '40%'
  },
  {
    title: 'Статус',
    key: 'status',
    width: '15%'
  },
  {
    title: 'Задач',
    key: 'tasks_count',
    width: '10%'
  },
  {
    title: 'Создан',
    key: 'created_at',
    dataIndex: 'created_at',
    width: '15%'
  },
  {
    title: 'Действия',
    key: 'actions',
    width: '20%'
  }
]

// Helper functions
const getStatusColor = (status) => {
  const colors = {
    'active': 'green',
    'inactive': 'orange',
    'completed': 'blue'
  }
  return colors[status] || 'default'
}

const getStatusLabel = (status) => {
  const labels = {
    'active': 'Активный',
    'inactive': 'Неактивный',
    'completed': 'Завершен'
  }
  return labels[status] || status
}

// Event handlers
const applyFilters = () => {
  loading.value = true
  router.get('/projects', filters.value, {
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
    visibility: null
  }
  applyFilters()
}

const handleTableChange = (pagination) => {
  loading.value = true
  router.get('/projects', {
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
    project: null,
    loading: false
  }
}

const viewProject = (id) => {
  const project = props.projects.find(p => p.id === id)
  if (project) {
    modalState.value = {
      isOpen: true,
      mode: 'view',
      project: project,
      loading: false
    }
  }
}

const editProject = (id) => {
  const project = props.projects.find(p => p.id === id)
  if (project) {
    modalState.value = {
      isOpen: true,
      mode: 'edit',
      project: project,
      loading: false
    }
  }
}

const closeModal = () => {
  modalState.value = {
    isOpen: false,
    mode: 'create',
    project: null,
    loading: false
  }
}

const handleModalSubmit = (data) => {
  // Refresh page after successful submission
  router.reload({ only: ['projects'] })
}

const deleteProject = (id) => {
  // TODO: Показать подтверждение удаления
  console.log('Delete project', id)
}

// Вспомогательные методы для ролей
const getRoleLabel = (role) => {
  const labels = {
    member: 'Участник',
    manager: 'Менеджер'
  }
  return labels[role] || role
}

const getRoleColor = (role) => {
  const colors = {
    member: 'blue',
    manager: 'green'
  }
  return colors[role] || 'default'
}

const getRoleIcon = (role) => {
  const icons = {
    member: UserOutlined,
    manager: SettingOutlined
  }
  return icons[role] || UserOutlined
}

// Методы для форматирования даты
const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU', {
    day: '2-digit',
    month: '2-digit', 
    year: 'numeric'
  })
}

const formatTime = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleTimeString('ru-RU', {
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>