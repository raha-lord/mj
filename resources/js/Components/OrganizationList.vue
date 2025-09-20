<template>
  <div class="organization-list">
    <!-- Заголовок -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-semibold text-gray-900">Мои организации</h2>
        <p class="text-gray-600 mt-1">Управляйте своими организациями и участием в них</p>
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

    <!-- Фильтры -->
    <div class="flex items-center gap-4 mb-6">
      <a-input-search
        v-model:value="searchQuery"
        placeholder="Поиск по названию организации..."
        class="max-w-md"
        allow-clear
      />
      <a-select
        v-model:value="roleFilter"
        placeholder="Фильтр по роли"
        class="w-40"
        allow-clear
      >
        <a-select-option value="super_user">Супер админ</a-select-option>
        <a-select-option value="org_admin">Админ организации</a-select-option>
        <a-select-option value="member">Участник</a-select-option>
      </a-select>
    </div>

    <!-- Список организаций -->
    <div v-if="isLoading" class="space-y-4">
      <a-card v-for="i in 3" :key="i" :loading="true" />
    </div>

    <div v-else-if="filteredOrganizations.length === 0" class="text-center py-12">
      <TeamOutlined class="text-6xl text-gray-300 mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">
        {{ searchQuery || roleFilter ? 'Организации не найдены' : 'У вас пока нет организаций' }}
      </h3>
      <p class="text-gray-500 mb-6">
        {{ searchQuery || roleFilter 
          ? 'Попробуйте изменить параметры поиска'
          : 'Создайте новую организацию или попросите пригласить вас в существующую'
        }}
      </p>
      <a-button
        v-if="canCreateOrganization && !searchQuery && !roleFilter"
        type="primary"
        @click="handleCreateOrganization"
        :loading="isCreating"
      >
        <PlusOutlined />
        Создать первую организацию
      </a-button>
    </div>

    <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      <a-card
        v-for="organization in filteredOrganizations"
        :key="organization.id"
        :hoverable="true"
        class="organization-card"
        @click="handleOrganizationClick(organization)"
      >
        <template #actions>
          <a-tooltip title="Переключиться на эту организацию">
            <SwapOutlined 
              @click.stop="handleSwitchOrganization(organization.id)"
              :class="{ 'text-primary': organization.id === currentOrganization?.id }"
            />
          </a-tooltip>
          <a-tooltip title="Настройки организации" v-if="canManageOrganization(organization)">
            <SettingOutlined @click.stop="handleOrganizationSettings(organization)" />
          </a-tooltip>
          <a-tooltip title="Участники организации">
            <UserOutlined @click.stop="handleOrganizationMembers(organization)" />
          </a-tooltip>
        </template>

        <a-card-meta>
          <template #avatar>
            <a-avatar size="large" :style="{ backgroundColor: getOrganizationColor(organization.id) }">
              <TeamOutlined />
            </a-avatar>
          </template>
          
          <template #title>
            <div class="flex items-center justify-between">
              <span class="truncate">{{ organization.name }}</span>
              <a-tag
                v-if="organization.id === currentOrganization?.id"
                color="green"
                size="small"
              >
                Текущая
              </a-tag>
            </div>
          </template>
          
          <template #description>
            <div class="space-y-2">
              <p v-if="organization.description" class="text-gray-600 text-sm line-clamp-2">
                {{ organization.description }}
              </p>
              <div class="flex items-center justify-between text-xs text-gray-500">
                <span class="flex items-center">
                  <UserOutlined class="mr-1" />
                  {{ organization.users_count }} участников
                </span>
                <a-tag :color="getRoleColor(organization.user_role)" size="small">
                  {{ getRoleLabel(organization.user_role) }}
                </a-tag>
              </div>
            </div>
          </template>
        </a-card-meta>
      </a-card>
    </div>

    <!-- Модал создания/редактирования организации -->
    <OrganizationForm
      v-model:open="formModalOpen"
      :organization="selectedOrganization"
      :loading="isCreating"
      @submit="handleFormSubmit"
      @cancel="handleFormCancel"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  PlusOutlined,
  TeamOutlined,
  UserOutlined,
  SettingOutlined,
  SwapOutlined
} from '@ant-design/icons-vue'
import { useOrganizations } from '../composables/organizations/useOrganizations'
import { useOrganizationContext } from '../composables/organizations/useOrganizationContext'
import { useUserPermissions } from '../composables/organizations/useUserPermissions'
import { message } from 'ant-design-vue'
import OrganizationForm from './OrganizationForm.vue'

// Composables
const {
  organizations,
  loading: isLoading,
  createOrganization,
  updateOrganization,
  fetchOrganizations
} = useOrganizations()

const {
  currentOrganization,
  switchOrganization
} = useOrganizationContext()

const {
  canCreateOrganization,
  canManageOrganization,
  getRoleLabel,
  getRoleColor
} = useUserPermissions()

// Состояние компонента
const searchQuery = ref('')
const roleFilter = ref('')
const formModalOpen = ref(false)
const selectedOrganization = ref(null)
const isCreating = ref(false)

// Вычисляемые свойства
const filteredOrganizations = computed(() => {
  let filtered = organizations.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(org => 
      org.name.toLowerCase().includes(query) ||
      (org.description && org.description.toLowerCase().includes(query))
    )
  }

  if (roleFilter.value) {
    filtered = filtered.filter(org => org.user_role === roleFilter.value)
  }

  return filtered.sort((a, b) => {
    // Текущая организация первая
    if (a.id === currentOrganization.value?.id) return -1
    if (b.id === currentOrganization.value?.id) return 1
    
    // Потом по роли (супер админ, админ, участник)
    const roleOrder = { super_user: 0, org_admin: 1, member: 2 }
    const aOrder = roleOrder[a.user_role] || 3
    const bOrder = roleOrder[b.user_role] || 3
    
    if (aOrder !== bOrder) return aOrder - bOrder
    
    // Потом по названию
    return a.name.localeCompare(b.name)
  })
})

// Методы
const handleOrganizationClick = (organization) => {
  if (organization.id !== currentOrganization.value?.id) {
    handleSwitchOrganization(organization.id)
  }
}

const handleSwitchOrganization = async (organizationId) => {
  try {
    await switchOrganization(organizationId)
    message.success('Организация успешно переключена')
    
    // Обновляем страницу для применения нового контекста
    router.reload()
  } catch (error) {
    message.error(error.message || 'Ошибка переключения организации')
  }
}

const handleCreateOrganization = () => {
  selectedOrganization.value = null
  formModalOpen.value = true
}

const handleOrganizationSettings = (organization) => {
  router.visit(`/organizations/${organization.id}/settings`)
}

const handleOrganizationMembers = (organization) => {
  router.visit(`/organizations/${organization.id}/members`)
}

const handleFormSubmit = async (formData) => {
  isCreating.value = true
  
  try {
    if (selectedOrganization.value) {
      // Редактирование существующей организации
      await updateOrganization(selectedOrganization.value.id, formData)
      message.success('Организация успешно обновлена')
    } else {
      // Создание новой организации
      const newOrganization = await createOrganization(formData)
      message.success('Организация успешно создана')
      
      // Переключаемся на новую организацию
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

const getOrganizationColor = (id) => {
  const colors = [
    '#1890ff', '#52c41a', '#fa8c16', '#eb2f96', 
    '#722ed1', '#13c2c2', '#fa541c', '#2f54eb'
  ]
  return colors[id % colors.length]
}

// Инициализация
onMounted(async () => {
  await fetchOrganizations()
})
</script>

<style scoped>
.organization-list {
  @apply p-6;
}

.organization-card {
  @apply cursor-pointer transition-all duration-200;
}

.organization-card:hover {
  @apply shadow-lg;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>