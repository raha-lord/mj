<template>
  <AppLayout title="Организации">
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-900">
            Управление организациями
          </h2>
          <p class="text-gray-600 text-sm mt-1">
            Просматривайте и управляйте всеми организациями системы
          </p>
        </div>
        <a-button
          v-if="canCreateOrganization"
          type="primary"
          @click="handleCreateOrganization"
          :loading="isCreating"
        >
          <PlusOutlined />
          Создать организацию
        </a-button>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Фильтры и поиск -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
          <div class="flex flex-col md:flex-row gap-4">
            <a-input-search
              v-model:value="filters.search"
              placeholder="Поиск по названию организации..."
              class="md:max-w-md"
              allow-clear
              @search="applyFilters"
            />
            <a-select
              v-model:value="filters.status"
              placeholder="Статус"
              class="w-32"
              allow-clear
              @change="applyFilters"
            >
              <a-select-option value="active">Активные</a-select-option>
              <a-select-option value="inactive">Неактивные</a-select-option>
            </a-select>
            <a-select
              v-model:value="filters.sort"
              placeholder="Сортировка"
              class="w-40"
              @change="applyFilters"
            >
              <a-select-option value="name_asc">По названию (А-Я)</a-select-option>
              <a-select-option value="name_desc">По названию (Я-А)</a-select-option>
              <a-select-option value="created_desc">Новые первые</a-select-option>
              <a-select-option value="created_asc">Старые первые</a-select-option>
              <a-select-option value="members_desc">Больше участников</a-select-option>
              <a-select-option value="members_asc">Меньше участников</a-select-option>
            </a-select>
          </div>
        </div>

        <!-- Статистика -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
          <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <TeamOutlined class="text-2xl" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Всего организаций</p>
                <p class="text-2xl font-semibold text-gray-900">{{ stats.total }}</p>
              </div>
            </div>
          </div>
          <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-green-100 text-green-600">
                <CheckCircleOutlined class="text-2xl" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Активные</p>
                <p class="text-2xl font-semibold text-gray-900">{{ stats.active }}</p>
              </div>
            </div>
          </div>
          <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                <UserOutlined class="text-2xl" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Всего участников</p>
                <p class="text-2xl font-semibold text-gray-900">{{ stats.total_members }}</p>
              </div>
            </div>
          </div>
          <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <ProjectOutlined class="text-2xl" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Всего проектов</p>
                <p class="text-2xl font-semibold text-gray-900">{{ stats.total_projects }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Список организаций -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
              Организации
              <span class="text-sm text-gray-500 font-normal">({{ filteredOrganizations.length }})</span>
            </h3>
          </div>

          <div v-if="isLoading" class="p-6">
            <div class="space-y-4">
              <a-skeleton v-for="i in 5" :key="i" :loading="true" active />
            </div>
          </div>

          <div v-else-if="filteredOrganizations.length === 0" class="p-12 text-center">
            <TeamOutlined class="text-6xl text-gray-300 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">
              {{ hasFilters ? 'Организации не найдены' : 'Нет организаций' }}
            </h3>
            <p class="text-gray-500 mb-6">
              {{ hasFilters 
                ? 'Попробуйте изменить параметры поиска'
                : 'Создайте первую организацию для начала работы'
              }}
            </p>
            <a-button
              v-if="canCreateOrganization && !hasFilters"
              type="primary"
              @click="handleCreateOrganization"
              :loading="isCreating"
            >
              <PlusOutlined />
              Создать первую организацию
            </a-button>
          </div>

          <div v-else>
            <div class="divide-y divide-gray-200">
              <div
                v-for="organization in paginatedOrganizations"
                :key="organization.id"
                class="p-6 hover:bg-gray-50 transition-colors duration-200"
              >
                <div class="flex items-center justify-between">
                  <!-- Информация об организации -->
                  <div class="flex items-center space-x-4">
                    <a-avatar 
                      :size="48" 
                      :style="{ backgroundColor: getOrganizationColor(organization.id) }"
                    >
                      <TeamOutlined />
                    </a-avatar>
                    
                    <div>
                      <div class="flex items-center space-x-3">
                        <h4 class="text-lg font-medium text-gray-900">
                          {{ organization.name }}
                        </h4>
                        <a-tag 
                          :color="organization.is_active ? 'green' : 'red'" 
                          size="small"
                        >
                          {{ organization.is_active ? 'Активна' : 'Неактивна' }}
                        </a-tag>
                        <a-tag 
                          v-if="organization.id === currentOrganization?.id" 
                          color="blue" 
                          size="small"
                        >
                          Текущая
                        </a-tag>
                      </div>
                      
                      <p v-if="organization.description" class="text-gray-600 mt-1">
                        {{ organization.description }}
                      </p>
                      
                      <div class="flex items-center space-x-6 mt-2 text-sm text-gray-500">
                        <span class="flex items-center">
                          <UserOutlined class="mr-1" />
                          {{ organization.users_count }} участников
                        </span>
                        <span class="flex items-center">
                          <ProjectOutlined class="mr-1" />
                          {{ organization.projects_count }} проектов
                        </span>
                        <span>
                          Создана: {{ formatDate(organization.created_at) }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Действия -->
                  <div class="flex items-center space-x-2">
                    <a-tooltip title="Переключиться на эту организацию">
                      <a-button
                        type="text"
                        :disabled="organization.id === currentOrganization?.id || !organization.is_active"
                        @click="handleSwitchOrganization(organization.id)"
                      >
                        <SwapOutlined />
                      </a-button>
                    </a-tooltip>
                    
                    <a-tooltip title="Участники организации">
                      <a-button
                        type="text"
                        @click="handleViewMembers(organization)"
                      >
                        <UserOutlined />
                      </a-button>
                    </a-tooltip>
                    
                    <a-tooltip title="Настройки организации">
                      <a-button
                        type="text"
                        @click="handleViewSettings(organization)"
                        :disabled="!canManageOrganization(organization)"
                      >
                        <SettingOutlined />
                      </a-button>
                    </a-tooltip>

                    <a-dropdown 
                      :trigger="['click']"
                      placement="bottomRight"
                    >
                      <a-button type="text">
                        <MoreOutlined />
                      </a-button>
                      
                      <template #overlay>
                        <a-menu>
                          <a-menu-item @click="handleEditOrganization(organization)">
                            <EditOutlined class="mr-2" />
                            Редактировать
                          </a-menu-item>
                          <a-menu-item 
                            v-if="organization.is_active"
                            @click="handleDeactivateOrganization(organization)"
                          >
                            <StopOutlined class="mr-2" />
                            Деактивировать
                          </a-menu-item>
                          <a-menu-item 
                            v-else
                            @click="handleActivateOrganization(organization)"
                          >
                            <CheckOutlined class="mr-2" />
                            Активировать
                          </a-menu-item>
                          <a-menu-divider />
                          <a-menu-item 
                            @click="handleDeleteOrganization(organization)"
                            class="text-red-600"
                            :disabled="!canDeleteOrganization(organization)"
                          >
                            <DeleteOutlined class="mr-2" />
                            Удалить
                          </a-menu-item>
                        </a-menu>
                      </template>
                    </a-dropdown>
                  </div>
                </div>
              </div>
            </div>

            <!-- Пагинация -->
            <div v-if="totalPages > 1" class="px-6 py-4 border-t border-gray-200">
              <a-pagination
                v-model:current="currentPage"
                :total="filteredOrganizations.length"
                :page-size="pageSize"
                :show-size-changer="false"
                :show-quick-jumper="true"
                :show-total="(total, range) => `${range[0]}-${range[1]} из ${total} организаций`"
                @change="handlePageChange"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Модал создания/редактирования организации -->
    <OrganizationForm
      v-model:open="formModalOpen"
      :organization="selectedOrganization"
      :loading="isCreating"
      @submit="handleFormSubmit"
      @cancel="handleFormCancel"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  PlusOutlined,
  TeamOutlined,
  UserOutlined,
  ProjectOutlined,
  CheckCircleOutlined,
  SwapOutlined,
  SettingOutlined,
  MoreOutlined,
  EditOutlined,
  StopOutlined,
  CheckOutlined,
  DeleteOutlined
} from '@ant-design/icons-vue'
import { Modal, message } from 'ant-design-vue'
import AppLayout from '../../Layouts/AppLayout.vue'
import OrganizationForm from '../../Components/Organizations/OrganizationForm.vue'
import { useOrganizations } from '../../composables/organizations/useOrganizations'
import { useOrganizationContext } from '../../composables/organizations/useOrganizationContext'
import { useUserPermissions } from '../../composables/organizations/useUserPermissions'

// Пропсы
const props = defineProps({
  initialOrganizations: {
    type: Array,
    default: () => []
  },
  initialStats: {
    type: Object,
    default: () => ({})
  }
})

// Composables
const {
  organizations,
  loading: isLoading,
  createOrganization,
  updateOrganization,
  deleteOrganization,
  fetchOrganizations
} = useOrganizations()

const {
  currentOrganization,
  switchOrganization
} = useOrganizationContext()

const {
  canCreateOrganization,
  canManageOrganization,
  isSuperUser
} = useUserPermissions()

// Состояние компонента
const isCreating = ref(false)
const formModalOpen = ref(false)
const selectedOrganization = ref(null)
const currentPage = ref(1)
const pageSize = ref(10)

// Фильтры
const filters = reactive({
  search: '',
  status: '',
  sort: 'name_asc'
})

// Статистика
const stats = ref({
  total: 0,
  active: 0,
  total_members: 0,
  total_projects: 0,
  ...props.initialStats
})

// Вычисляемые свойства
const hasFilters = computed(() => {
  return filters.search || filters.status
})

const filteredOrganizations = computed(() => {
  let filtered = [...organizations.value]

  // Поиск
  if (filters.search) {
    const query = filters.search.toLowerCase()
    filtered = filtered.filter(org => 
      org.name.toLowerCase().includes(query) ||
      (org.description && org.description.toLowerCase().includes(query))
    )
  }

  // Фильтр по статусу
  if (filters.status) {
    filtered = filtered.filter(org => {
      if (filters.status === 'active') return org.is_active
      if (filters.status === 'inactive') return !org.is_active
      return true
    })
  }

  // Сортировка
  filtered.sort((a, b) => {
    switch (filters.sort) {
      case 'name_asc':
        return a.name.localeCompare(b.name)
      case 'name_desc':
        return b.name.localeCompare(a.name)
      case 'created_desc':
        return new Date(b.created_at) - new Date(a.created_at)
      case 'created_asc':
        return new Date(a.created_at) - new Date(b.created_at)
      case 'members_desc':
        return (b.users_count || 0) - (a.users_count || 0)
      case 'members_asc':
        return (a.users_count || 0) - (b.users_count || 0)
      default:
        return 0
    }
  })

  return filtered
})

const paginatedOrganizations = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  const end = start + pageSize.value
  return filteredOrganizations.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(filteredOrganizations.value.length / pageSize.value)
})

// Методы проверки прав
const canDeleteOrganization = (organization) => {
  return isSuperUser.value && organization.users_count <= 1
}

// Методы форматирования
const getOrganizationColor = (id) => {
  const colors = [
    '#1890ff', '#52c41a', '#fa8c16', '#eb2f96', 
    '#722ed1', '#13c2c2', '#fa541c', '#2f54eb'
  ]
  return colors[id % colors.length]
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('ru-RU')
}

// Обработчики событий
const applyFilters = () => {
  currentPage.value = 1
}

const handlePageChange = (page) => {
  currentPage.value = page
}

const handleCreateOrganization = () => {
  selectedOrganization.value = null
  formModalOpen.value = true
}

const handleEditOrganization = (organization) => {
  selectedOrganization.value = organization
  formModalOpen.value = true
}

const handleSwitchOrganization = async (organizationId) => {
  try {
    await switchOrganization(organizationId)
    message.success('Организация успешно переключена')
    router.reload()
  } catch (error) {
    message.error(error.message || 'Ошибка переключения организации')
  }
}

const handleViewMembers = (organization) => {
  router.visit(`/organizations/${organization.id}/members`)
}

const handleViewSettings = (organization) => {
  router.visit(`/organizations/${organization.id}/settings`)
}

const handleActivateOrganization = async (organization) => {
  try {
    await updateOrganization(organization.id, { is_active: true })
    message.success('Организация активирована')
    await fetchOrganizations()
  } catch (error) {
    message.error(error.message || 'Ошибка активации организации')
  }
}

const handleDeactivateOrganization = (organization) => {
  Modal.confirm({
    title: 'Деактивировать организацию?',
    content: `Участники организации "${organization.name}" потеряют доступ к проектам и задачам.`,
    okText: 'Деактивировать',
    okType: 'danger',
    cancelText: 'Отмена',
    onOk: async () => {
      try {
        await updateOrganization(organization.id, { is_active: false })
        message.success('Организация деактивирована')
        await fetchOrganizations()
      } catch (error) {
        message.error(error.message || 'Ошибка деактивации организации')
      }
    }
  })
}

const handleDeleteOrganization = (organization) => {
  Modal.confirm({
    title: 'Удалить организацию?',
    content: `Вы действительно хотите удалить организацию "${organization.name}"? Это действие необратимо!`,
    okText: 'Удалить',
    okType: 'danger',
    cancelText: 'Отмена',
    onOk: async () => {
      try {
        await deleteOrganization(organization.id)
        message.success('Организация удалена')
        await fetchOrganizations()
      } catch (error) {
        message.error(error.message || 'Ошибка удаления организации')
      }
    }
  })
}

const handleFormSubmit = async (formData) => {
  isCreating.value = true
  
  try {
    if (selectedOrganization.value) {
      await updateOrganization(selectedOrganization.value.id, formData)
      message.success('Организация успешно обновлена')
    } else {
      const newOrganization = await createOrganization(formData)
      message.success('Организация успешно создана')
      await handleSwitchOrganization(newOrganization.id)
    }
    
    formModalOpen.value = false
    await fetchOrganizations()
  } catch (error) {
    message.error(error.message || 'Ошибка сохранения организации')
  } finally {
    isCreating.value = false
  }
}

const handleFormCancel = () => {
  formModalOpen.value = false
  selectedOrganization.value = null
}

// Инициализация
onMounted(async () => {
  if (props.initialOrganizations.length > 0) {
    organizations.value = props.initialOrganizations
  } else {
    await fetchOrganizations()
  }
  
  // Обновляем статистику
  stats.value = {
    total: organizations.value.length,
    active: organizations.value.filter(org => org.is_active).length,
    total_members: organizations.value.reduce((sum, org) => sum + (org.users_count || 0), 0),
    total_projects: organizations.value.reduce((sum, org) => sum + (org.projects_count || 0), 0)
  }
})
</script>

<style scoped>
.hover\:bg-gray-50:hover {
  background-color: #f9fafb;
}
</style>