<template>
  <AppLayout title="Статусы">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Статусы
        </h2>
        <a-button type="primary" @click="openCreateModal">
          <template #icon>
            <PlusOutlined />
          </template>
          Создать статус
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
                placeholder="Поиск статусов..."
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
                v-model:value="filters.type"
                placeholder="Все типы"
                allow-clear
                style="width: 100%"
                @change="applyFilters"
              >
                <a-select-option value="task">Задача</a-select-option>
                <a-select-option value="project">Проект</a-select-option>
              </a-select>
            </a-col>
            
            <a-col :xs="24" :md="8">
              <a-space>
                <a-button @click="clearFilters">Очистить</a-button>
              </a-space>
            </a-col>
          </a-row>
        </a-card>

        <!-- Statuses Table -->
        <a-card>
          <a-table
            :columns="columns"
            :data-source="statuses"
            :loading="loading"
            :pagination="{
              current: currentPage,
              pageSize: perPage,
              total: total,
              showSizeChanger: true,
              pageSizeOptions: ['10', '25', '50', '100'],
              showTotal: (total, range) => `${range[0]}-${range[1]} из ${total} статусов`,
            }"
            row-key="id"
            @change="handleTableChange"
          >
            <!-- Custom columns -->
            <template #bodyCell="{ column, record }">
              <template v-if="column.key === 'name'">
                <div>
                  <a-tag :color="record.color || 'default'">
                    {{ record.name }}
                  </a-tag>
                  <div class="text-sm text-gray-500 mt-1" v-if="record.slug">
                    slug: {{ record.slug }}
                  </div>
                </div>
              </template>
              
              <template v-else-if="column.key === 'type'">
                <a-tag :color="getTypeColor(record.type)">
                  {{ getTypeLabel(record.type) }}
                </a-tag>
              </template>
              
              <template v-else-if="column.key === 'is_final'">
                <a-tag :color="record.is_final ? 'green' : 'orange'">
                  {{ record.is_final ? 'Финальный' : 'Промежуточный' }}
                </a-tag>
              </template>
              
              <template v-else-if="column.key === 'tasks_count'">
                <a-badge :count="record.tasks_count || 0" :number-style="{backgroundColor: '#108ee9'}" />
              </template>
              
              <template v-else-if="column.key === 'actions'">
                <a-space>
                  <a-button type="link" size="small" @click="viewStatus(record.id)">
                    Просмотр
                  </a-button>
                  <a-button type="link" size="small" @click="editStatus(record.id)">
                    Редактировать
                  </a-button>
                  <a-button type="link" size="small" danger @click="deleteStatus(record.id)">
                    Удалить
                  </a-button>
                </a-space>
              </template>
            </template>
          </a-table>
        </a-card>
      </div>
    </div>

    <!-- Status Modal -->
    <StatusModal
      :is-open="modalState.isOpen"
      :mode="modalState.mode"
      :status="modalState.status"
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
import StatusModal from '../../Components/StatusModal.vue'
import { PlusOutlined, SearchOutlined } from '@ant-design/icons-vue'

// Props from controller
const props = defineProps({
  statuses: {
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
  type: null
})

const modalState = ref({
  isOpen: false,
  mode: 'create',
  status: null,
  loading: false
})

// Computed
const currentPage = computed(() => props.pagination.current_page || 1)
const perPage = computed(() => props.pagination.per_page || 25)
const total = computed(() => props.pagination.total || 0)

// Table columns
const columns = [
  {
    title: 'Статус',
    key: 'name',
    dataIndex: 'name',
    width: '25%'
  },
  {
    title: 'Тип',
    key: 'type',
    width: '15%'
  },
  {
    title: 'Финальный',
    key: 'is_final',
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
const getTypeColor = (type) => {
  const colors = {
    'task': 'blue',
    'project': 'green'
  }
  return colors[type] || 'default'
}

const getTypeLabel = (type) => {
  const labels = {
    'task': 'Задача',
    'project': 'Проект'
  }
  return labels[type] || type
}

// Event handlers
const applyFilters = () => {
  loading.value = true
  router.get('/statuses', filters.value, {
    preserveState: true,
    onFinish: () => {
      loading.value = false
    }
  })
}

const clearFilters = () => {
  filters.value = {
    search: '',
    type: null
  }
  applyFilters()
}

const handleTableChange = (pagination) => {
  loading.value = true
  router.get('/statuses', {
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
    status: null,
    loading: false
  }
}

const viewStatus = (id) => {
  const status = props.statuses.find(s => s.id === id)
  if (status) {
    modalState.value = {
      isOpen: true,
      mode: 'view',
      status: status,
      loading: false
    }
  }
}

const editStatus = (id) => {
  const status = props.statuses.find(s => s.id === id)
  if (status) {
    modalState.value = {
      isOpen: true,
      mode: 'edit',
      status: status,
      loading: false
    }
  }
}

const closeModal = () => {
  modalState.value = {
    isOpen: false,
    mode: 'create',
    status: null,
    loading: false
  }
}

const handleModalSubmit = (data) => {
  // Refresh page after successful submission
  router.reload({ only: ['statuses'] })
}

const deleteStatus = (id) => {
  // TODO: Показать подтверждение удаления
  console.log('Delete status', id)
}
</script>