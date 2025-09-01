<template>
  <AppLayout title="Проекты">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Проекты
        </h2>
        <a-button type="primary" @click="openCreateModal">
          <template #icon>
            <PlusOutlined />
          </template>
          Создать проект
        </a-button>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
            
            <a-col :xs="24" :md="8">
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
                  <div class="font-medium">{{ record.name }}</div>
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
                  <a-button type="link" size="small" @click="viewProject(record.id)">
                    Просмотр
                  </a-button>
                  <a-button type="link" size="small" @click="editProject(record.id)">
                    Редактировать
                  </a-button>
                  <a-button type="link" size="small" danger @click="deleteProject(record.id)">
                    Удалить
                  </a-button>
                </a-space>
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
import { PlusOutlined, SearchOutlined } from '@ant-design/icons-vue'

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
  }
})

// Reactive state
const loading = ref(false)
const filters = ref({
  search: '',
  status: null
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
    status: null
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
</script>