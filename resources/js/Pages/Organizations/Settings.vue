<template>
  <AppLayout :title="`${organization.name} - Настройки`">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <a-button 
            type="text" 
            @click="handleGoBack"
            class="mr-2"
          >
            <ArrowLeftOutlined />
          </a-button>
          <a-avatar 
            :size="40" 
            :style="{ backgroundColor: getOrganizationColor(organization.id) }"
          >
            <TeamOutlined />
          </a-avatar>
          <div>
            <h2 class="text-xl font-semibold text-gray-900">
              Настройки организации
            </h2>
            <p class="text-sm text-gray-600">
              {{ organization.name }}
            </p>
          </div>
        </div>

        <div class="flex items-center space-x-2">
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
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Навигация по настройкам -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
          <!-- Боковое меню -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow">
              <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Навигация</h3>
              </div>
              <nav class="p-2">
                <a
                  v-for="item in navigationItems"
                  :key="item.key"
                  @click="activeSection = item.key"
                  :class="[
                    'flex items-center px-3 py-2 text-sm font-medium rounded-md cursor-pointer transition-colors',
                    activeSection === item.key
                      ? 'bg-blue-100 text-blue-700'
                      : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                  ]"
                >
                  <component :is="item.icon" class="mr-3 h-5 w-5" />
                  {{ item.label }}
                </a>
              </nav>
            </div>

            <!-- Быстрые действия -->
            <div class="bg-white rounded-lg shadow mt-6">
              <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Быстрые действия</h3>
              </div>
              <div class="p-4 space-y-3">
                <a-button 
                  block 
                  @click="handleViewOrganization"
                >
                  <EyeOutlined />
                  Просмотр организации
                </a-button>
                <a-button 
                  block 
                  @click="handleViewMembers"
                >
                  <UserOutlined />
                  Участники ({{ organization.users_count || 0 }})
                </a-button>
                <a-button 
                  block 
                  @click="handleInviteUser"
                >
                  <UserAddOutlined />
                  Пригласить участника
                </a-button>
              </div>
            </div>
          </div>

          <!-- Основной контент -->
          <div class="lg:col-span-3">
            <!-- Основные настройки -->
            <div v-if="activeSection === 'general'">
              <OrganizationSettings :organization="organization" />
            </div>

            <!-- Участники -->
            <div v-else-if="activeSection === 'members'">
              <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                  <h3 class="text-lg font-medium text-gray-900">Управление участниками</h3>
                  <p class="text-sm text-gray-600 mt-1">
                    Управляйте участниками организации и их ролями
                  </p>
                </div>
                <MembersList 
                  :organization-id="organization.id"
                  :current-user-id="$page.props.auth.user.id"
                />
              </div>
            </div>

            <!-- Безопасность -->
            <div v-else-if="activeSection === 'security'">
              <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                  <h3 class="text-lg font-medium text-gray-900">Безопасность</h3>
                  <p class="text-sm text-gray-600 mt-1">
                    Настройки безопасности и доступа
                  </p>
                </div>
                <div class="p-6">
                  <SecuritySettings :organization="organization" />
                </div>
              </div>
            </div>

            <!-- Уведомления -->
            <div v-else-if="activeSection === 'notifications'">
              <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                  <h3 class="text-lg font-medium text-gray-900">Уведомления</h3>
                  <p class="text-sm text-gray-600 mt-1">
                    Настройки уведомлений для организации
                  </p>
                </div>
                <div class="p-6">
                  <NotificationSettings :organization="organization" />
                </div>
              </div>
            </div>

            <!-- Интеграции -->
            <div v-else-if="activeSection === 'integrations'">
              <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                  <h3 class="text-lg font-medium text-gray-900">Интеграции</h3>
                  <p class="text-sm text-gray-600 mt-1">
                    Подключение внешних сервисов и API
                  </p>
                </div>
                <div class="p-6">
                  <IntegrationsSettings :organization="organization" />
                </div>
              </div>
            </div>

            <!-- Выставление счетов -->
            <div v-else-if="activeSection === 'billing'">
              <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                  <h3 class="text-lg font-medium text-gray-900">Выставление счетов</h3>
                  <p class="text-sm text-gray-600 mt-1">
                    Управление подпиской и платежами
                  </p>
                </div>
                <div class="p-6">
                  <BillingSettings :organization="organization" />
                </div>
              </div>
            </div>

            <!-- Опасная зона -->
            <div v-else-if="activeSection === 'danger'">
              <div class="bg-white rounded-lg shadow border-red-200">
                <div class="p-6 border-b border-red-200">
                  <h3 class="text-lg font-medium text-red-900">Опасная зона</h3>
                  <p class="text-sm text-red-600 mt-1">
                    Необратимые действия с организацией
                  </p>
                </div>
                <div class="p-6">
                  <DangerZoneSettings :organization="organization" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

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
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  ArrowLeftOutlined,
  TeamOutlined,
  SettingOutlined,
  UserOutlined,
  SecurityScanOutlined,
  BellOutlined,
  ApiOutlined,
  CreditCardOutlined,
  ExclamationCircleOutlined,
  EyeOutlined,
  UserAddOutlined
} from '@ant-design/icons-vue'
import { message } from 'ant-design-vue'
import AppLayout from '../../Layouts/AppLayout.vue'
import MembersList from '../../Components/MembersList.vue'
import OrganizationSettings from '../../Components/OrganizationSettings.vue'
import InviteUserForm from '../../Components/InviteUserForm.vue'
import { useOrganizationContext } from '../../composables/organizations/useOrganizationContext'

// Временные компоненты для разделов, которые будут реализованы позже
const SecuritySettings = {
  props: ['organization'],
  template: `
    <div class="space-y-6">
      <div>
        <h4 class="text-base font-medium text-gray-900 mb-4">Двухфакторная аутентификация</h4>
        <p class="text-sm text-gray-600 mb-4">Требовать 2FA для всех участников организации</p>
        <a-switch v-model:checked="require2FA" />
      </div>
      <div>
        <h4 class="text-base font-medium text-gray-900 mb-4">IP-ограничения</h4>
        <p class="text-sm text-gray-600 mb-4">Разрешить доступ только с определенных IP-адресов</p>
        <a-textarea placeholder="192.168.1.0/24" :rows="3" />
      </div>
    </div>
  `,
  data() {
    return { require2FA: false }
  }
}

const NotificationSettings = {
  props: ['organization'],
  template: `
    <div class="space-y-6">
      <div>
        <h4 class="text-base font-medium text-gray-900 mb-4">Email уведомления</h4>
        <div class="space-y-3">
          <a-checkbox v-model:checked="emailSettings.newMember">Новые участники</a-checkbox>
          <a-checkbox v-model:checked="emailSettings.projectCreated">Новые проекты</a-checkbox>
          <a-checkbox v-model:checked="emailSettings.taskAssigned">Назначение задач</a-checkbox>
        </div>
      </div>
    </div>
  `,
  data() {
    return {
      emailSettings: {
        newMember: true,
        projectCreated: true,
        taskAssigned: false
      }
    }
  }
}

const IntegrationsSettings = {
  props: ['organization'],
  template: `
    <div class="text-center py-12">
      <ApiOutlined class="text-6xl text-gray-300 mb-4" />
      <p class="text-gray-500">Интеграции будут доступны в следующих версиях</p>
    </div>
  `
}

const BillingSettings = {
  props: ['organization'],
  template: `
    <div class="text-center py-12">
      <CreditCardOutlined class="text-6xl text-gray-300 mb-4" />
      <p class="text-gray-500">Настройки биллинга будут доступны в следующих версиях</p>
    </div>
  `
}

const DangerZoneSettings = {
  props: ['organization'],
  template: `
    <div class="space-y-6">
      <div class="p-4 border border-red-200 rounded-lg bg-red-50">
        <h4 class="text-base font-medium text-red-900 mb-2">Архивировать организацию</h4>
        <p class="text-sm text-red-600 mb-4">
          Участники потеряют доступ к проектам и задачам. Данные сохранятся.
        </p>
        <a-button danger @click="archiveOrganization">Архивировать</a-button>
      </div>
      
      <div class="p-4 border border-red-300 rounded-lg bg-red-100">
        <h4 class="text-base font-medium text-red-900 mb-2">Удалить организацию</h4>
        <p class="text-sm text-red-700 mb-4">
          Все данные будут удалены безвозвратно. Это действие нельзя отменить!
        </p>
        <a-button danger type="primary" @click="deleteOrganization">Удалить навсегда</a-button>
      </div>
    </div>
  `,
  methods: {
    archiveOrganization() {
      console.log('Archive organization')
    },
    deleteOrganization() {
      console.log('Delete organization')
    }
  }
}

// Пропсы
const props = defineProps({
  organization: {
    type: Object,
    required: true
  }
})

// Composables
const { currentOrganization } = useOrganizationContext()

// Состояние компонента
const activeSection = ref('general')
const inviteModalOpen = ref(false)
const isInviting = ref(false)

// Навигационные элементы
const navigationItems = [
  {
    key: 'general',
    label: 'Основные',
    icon: SettingOutlined
  },
  {
    key: 'members',
    label: 'Участники',
    icon: UserOutlined
  },
  {
    key: 'security',
    label: 'Безопасность',
    icon: SecurityScanOutlined
  },
  {
    key: 'notifications',
    label: 'Уведомления',
    icon: BellOutlined
  },
  {
    key: 'integrations',
    label: 'Интеграции',
    icon: ApiOutlined
  },
  {
    key: 'billing',
    label: 'Биллинг',
    icon: CreditCardOutlined
  },
  {
    key: 'danger',
    label: 'Опасная зона',
    icon: ExclamationCircleOutlined
  }
]

// Методы форматирования
const getOrganizationColor = (id) => {
  const colors = [
    '#1890ff', '#52c41a', '#fa8c16', '#eb2f96', 
    '#722ed1', '#13c2c2', '#fa541c', '#2f54eb'
  ]
  return colors[id % colors.length]
}

// Обработчики событий
const handleGoBack = () => {
  router.visit(`/organizations/${props.organization.id}`)
}

const handleViewOrganization = () => {
  router.visit(`/organizations/${props.organization.id}`)
}

const handleViewMembers = () => {
  activeSection.value = 'members'
}

const handleInviteUser = () => {
  inviteModalOpen.value = true
}

const handleInviteSubmit = async (inviteData) => {
  isInviting.value = true
  
  try {
    const response = await fetch(`/api/organizations/${props.organization.id}/users`, {
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
</script>

<style scoped>
.ant-tabs-card > .ant-tabs-content {
  margin-top: -16px;
}

.ant-tabs-card > .ant-tabs-content > .ant-tabs-tabpane {
  background: #fff;
  padding: 0;
}
</style>