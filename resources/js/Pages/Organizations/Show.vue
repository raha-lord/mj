<template>
  <AppLayout :title="organization.name">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <a-avatar 
            :size="48" 
            :style="{ backgroundColor: getOrganizationColor(organization.id) }"
          >
            <TeamOutlined />
          </a-avatar>
          <div>
            <h2 class="text-xl font-semibold text-gray-900">
              {{ organization.name }}
            </h2>
            <div class="flex items-center space-x-2 mt-1">
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
                Текущая организация
              </a-tag>
              <span class="text-sm text-gray-500">
                {{ getRoleLabel(userRole) }}
              </span>
            </div>
          </div>
        </div>

        <div class="flex items-center space-x-2">
          <a-button
            v-if="organization.id !== currentOrganization?.id && organization.is_active"
            @click="handleSwitchOrganization"
            :loading="isSwitching"
          >
            <SwapOutlined />
            Переключиться
          </a-button>
          
          <a-dropdown 
            v-if="canManageOrganization"
            :trigger="['click']"
            placement="bottomRight"
          >
            <a-button type="primary">
              <SettingOutlined />
              Управление
              <DownOutlined />
            </a-button>
            
            <template #overlay>
              <a-menu>
                <a-menu-item @click="handleEditOrganization">
                  <EditOutlined class="mr-2" />
                  Редактировать организацию
                </a-menu-item>
                <a-menu-item @click="handleOrganizationSettings">
                  <SettingOutlined class="mr-2" />
                  Настройки организации
                </a-menu-item>
                <a-menu-divider />
                <a-menu-item @click="handleInviteUser">
                  <UserAddOutlined class="mr-2" />
                  Пригласить участника
                </a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Описание организации -->
        <div v-if="organization.description" class="bg-white rounded-lg shadow p-6 mb-6">
          <h3 class="text-lg font-medium text-gray-900 mb-2">О нас</h3>
          <p class="text-gray-600">{{ organization.description }}</p>
        </div>

        <!-- Статистика -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
          <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <UserOutlined class="text-2xl" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Участники</p>
                <p class="text-2xl font-semibold text-gray-900">{{ organization.users_count || 0 }}</p>
              </div>
            </div>
          </div>
          <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-green-100 text-green-600">
                <ProjectOutlined class="text-2xl" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Проекты</p>
                <p class="text-2xl font-semibold text-gray-900">{{ organization.projects_count || 0 }}</p>
              </div>
            </div>
          </div>
          <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                <CheckSquareOutlined class="text-2xl" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Задачи</p>
                <p class="text-2xl font-semibold text-gray-900">{{ organization.tasks_count || 0 }}</p>
              </div>
            </div>
          </div>
          <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <ClockCircleOutlined class="text-2xl" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Активность</p>
                <p class="text-2xl font-semibold text-gray-900">{{ formatActivity(organization.last_activity_at) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Вкладки -->
        <div class="bg-white rounded-lg shadow">
          <a-tabs v-model:activeKey="activeTab" type="card" class="px-6">
            <a-tab-pane key="overview" tab="Обзор">
              <OrganizationOverview 
                :organization="organization"
                :user-role="userRole"
              />
            </a-tab-pane>
            
            <a-tab-pane key="members" tab="Участники">
              <MembersList 
                :organization-id="organization.id"
                :current-user-id="$page.props.auth.user.id"
              />
            </a-tab-pane>
            
            <a-tab-pane key="projects" tab="Проекты">
              <OrganizationProjects 
                :organization-id="organization.id"
                :user-role="userRole"
              />
            </a-tab-pane>
            
            <a-tab-pane 
              v-if="canManageOrganization" 
              key="settings" 
              tab="Настройки"
            >
              <OrganizationSettings :organization="organization" />
            </a-tab-pane>
          </a-tabs>
        </div>
      </div>
    </div>

    <!-- Модал редактирования организации -->
    <OrganizationForm
      v-model:open="editModalOpen"
      :organization="organization"
      :loading="isUpdating"
      @submit="handleUpdateOrganization"
      @cancel="handleEditCancel"
    />

    <!-- Модал приглашения пользователя -->
    <InviteUserForm
      v-model:open="inviteModalOpen"
      :organization-id="organization.id"
      :loading="isInviting"
      @submit="handleInviteSubmit"
      @cancel="handleInviteCancel"
    />
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  TeamOutlined,
  UserOutlined,
  ProjectOutlined,
  CheckSquareOutlined,
  ClockCircleOutlined,
  SwapOutlined,
  SettingOutlined,
  DownOutlined,
  EditOutlined,
  UserAddOutlined
} from '@ant-design/icons-vue'
import { message } from 'ant-design-vue'
import AppLayout from '../../Layouts/AppLayout.vue'
import MembersList from '../../Components/MembersList.vue'
import OrganizationSettings from '../../Components/OrganizationSettings.vue'
import OrganizationForm from '../../Components/OrganizationForm.vue'
import InviteUserForm from '../../Components/InviteUserForm.vue'
import { useOrganizationContext } from '../../composables/organizations/useOrganizationContext'
import { useUserPermissions } from '../../composables/organizations/useUserPermissions'
import { useOrganizations } from '../../composables/organizations/useOrganizations'

// Компоненты, которые будут созданы позже
const OrganizationOverview = {
  props: ['organization', 'userRole'],
  template: `
    <div class="p-6">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Последние проекты -->
        <div>
          <h4 class="text-lg font-medium text-gray-900 mb-4">Последние проекты</h4>
          <div class="space-y-3">
            <div v-for="project in organization.recent_projects || []" :key="project.id" 
                 class="p-4 border border-gray-200 rounded-lg">
              <h5 class="font-medium text-gray-900">{{ project.name }}</h5>
              <p class="text-sm text-gray-600 mt-1">{{ project.description }}</p>
              <div class="text-xs text-gray-500 mt-2">
                Обновлен: {{ formatDate(project.updated_at) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Последняя активность -->
        <div>
          <h4 class="text-lg font-medium text-gray-900 mb-4">Последняя активность</h4>
          <div class="space-y-3">
            <div v-for="activity in organization.recent_activities || []" :key="activity.id" 
                 class="p-4 border border-gray-200 rounded-lg">
              <div class="flex items-center space-x-2">
                <span class="font-medium">{{ activity.user.name }}</span>
                <span class="text-sm text-gray-600">{{ activity.description }}</span>
              </div>
              <div class="text-xs text-gray-500 mt-1">
                {{ formatDate(activity.created_at) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `,
  methods: {
    formatDate(dateString) {
      if (!dateString) return '-'
      return new Date(dateString).toLocaleDateString('ru-RU')
    }
  }
}

const OrganizationProjects = {
  props: ['organizationId', 'userRole'],
  template: `
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h4 class="text-lg font-medium text-gray-900">Проекты организации</h4>
        <a-button type="primary" @click="createProject">
          <PlusOutlined />
          Создать проект
        </a-button>
      </div>
      <div class="text-center py-12">
        <ProjectOutlined class="text-6xl text-gray-300 mb-4" />
        <p class="text-gray-500">Здесь будет список проектов организации</p>
      </div>
    </div>
  `,
  methods: {
    createProject() {
      this.$router.visit('/projects/create')
    }
  }
}

// Пропсы
const props = defineProps({
  organization: {
    type: Object,
    required: true
  },
  userRole: {
    type: String,
    required: true
  }
})

// Composables
const {
  currentOrganization,
  switchOrganization
} = useOrganizationContext()

const {
  canManageOrganization,
  getRoleLabel
} = useUserPermissions()

const {
  updateOrganization
} = useOrganizations()

// Состояние компонента
const activeTab = ref('overview')
const isSwitching = ref(false)
const editModalOpen = ref(false)
const inviteModalOpen = ref(false)
const isUpdating = ref(false)
const isInviting = ref(false)

// Методы форматирования
const getOrganizationColor = (id) => {
  const colors = [
    '#1890ff', '#52c41a', '#fa8c16', '#eb2f96', 
    '#722ed1', '#13c2c2', '#fa541c', '#2f54eb'
  ]
  return colors[id % colors.length]
}

const formatActivity = (dateString) => {
  if (!dateString) return 'Нет данных'
  
  const date = new Date(dateString)
  const now = new Date()
  const diffInHours = Math.floor((now - date) / (1000 * 60 * 60))
  
  if (diffInHours < 1) return 'Только что'
  if (diffInHours < 24) return `${diffInHours} ч. назад`
  
  const diffInDays = Math.floor(diffInHours / 24)
  if (diffInDays < 7) return `${diffInDays} дн. назад`
  
  return date.toLocaleDateString('ru-RU')
}

// Обработчики событий
const handleSwitchOrganization = async () => {
  isSwitching.value = true
  
  try {
    await switchOrganization(props.organization.id)
    message.success('Организация успешно переключена')
    router.reload()
  } catch (error) {
    message.error(error.message || 'Ошибка переключения организации')
  } finally {
    isSwitching.value = false
  }
}

const handleEditOrganization = () => {
  editModalOpen.value = true
}

const handleOrganizationSettings = () => {
  activeTab.value = 'settings'
}

const handleInviteUser = () => {
  inviteModalOpen.value = true
}

const handleUpdateOrganization = async (formData) => {
  isUpdating.value = true
  
  try {
    await updateOrganization(props.organization.id, formData)
    message.success('Организация успешно обновлена')
    editModalOpen.value = false
    router.reload({ only: ['organization'] })
  } catch (error) {
    message.error(error.message || 'Ошибка обновления организации')
  } finally {
    isUpdating.value = false
  }
}

const handleEditCancel = () => {
  editModalOpen.value = false
}

const handleInviteSubmit = async (inviteData) => {
  isInviting.value = true
  
  try {
    const response = await fetch(`/api/organizations/${props.organization.id}/invite`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify(inviteData)
    })

    if (!response.ok) throw new Error('Ошибка отправки приглашения')

    message.success('Приглашение успешно отправлено')
    inviteModalOpen.value = false
    
    // Обновляем счетчик участников
    router.reload({ only: ['organization'] })
  } catch (error) {
    message.error(error.message || 'Ошибка отправки приглашения')
  } finally {
    isInviting.value = false
  }
}

const handleInviteCancel = () => {
  inviteModalOpen.value = false
}

// Инициализация
onMounted(() => {
  // Можно добавить дополнительную инициализацию если нужно
})
</script>

<style scoped>
.ant-tabs-card > .ant-tabs-content {
  margin-top: -16px;
}

.ant-tabs-card > .ant-tabs-content > .ant-tabs-tabpane {
  background: #fff;
  padding: 0;
}

.ant-tabs-card > .ant-tabs-nav .ant-tabs-tab {
  border-color: transparent;
  background: transparent;
}

.ant-tabs-card > .ant-tabs-nav .ant-tabs-tab-active {
  border-color: #d9d9d9;
  background: #fff;
}
</style>