<template>
  <AppLayout :title="`${organization.name} - Участники`">
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
              Участники организации
            </h2>
            <p class="text-sm text-gray-600">
              {{ organization.name }}
            </p>
          </div>
        </div>

        <div class="flex items-center space-x-4">
          <div class="text-right">
            <div class="text-sm text-gray-500">Всего участников</div>
            <div class="text-lg font-semibold text-gray-900">{{ organization.users_count || 0 }}</div>
          </div>
          <a-button
            v-if="canInviteUsers"
            type="primary"
            @click="handleInviteUser"
            :loading="isInviting"
          >
            <UserAddOutlined />
            Пригласить участника
          </a-button>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Фильтры и статистика -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
          <!-- Статистика -->
          <div class="lg:col-span-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                  <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <UserOutlined class="text-xl" />
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Всего участников</p>
                    <p class="text-xl font-semibold text-gray-900">{{ stats.total }}</p>
                  </div>
                </div>
              </div>
              <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                  <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <CrownOutlined class="text-xl" />
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Администраторы</p>
                    <p class="text-xl font-semibold text-gray-900">{{ stats.admins }}</p>
                  </div>
                </div>
              </div>
              <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                  <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <CheckCircleOutlined class="text-xl" />
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Активные</p>
                    <p class="text-xl font-semibold text-gray-900">{{ stats.active }}</p>
                  </div>
                </div>
              </div>
              <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                  <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                    <ClockCircleOutlined class="text-xl" />
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Ожидают</p>
                    <p class="text-xl font-semibold text-gray-900">{{ stats.pending }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Вкладки -->
        <div class="bg-white rounded-lg shadow">
          <a-tabs v-model:activeKey="activeTab" type="card" class="px-6">
            <a-tab-pane key="all" :tab="`Все участники (${stats.total})`">
              <MembersList 
                :organization-id="organization.id"
                :current-user-id="$page.props.auth.user.id"
                @stats-updated="handleStatsUpdate"
              />
            </a-tab-pane>
            
            <a-tab-pane key="admins" :tab="`Администраторы (${stats.admins})`">
              <MembersList 
                :organization-id="organization.id"
                :current-user-id="$page.props.auth.user.id"
                :filter-role="'org_admin'"
                @stats-updated="handleStatsUpdate"
              />
            </a-tab-pane>
            
            <a-tab-pane key="pending" :tab="`Ожидают (${stats.pending})`">
              <MembersList 
                :organization-id="organization.id"
                :current-user-id="$page.props.auth.user.id"
                :filter-status="'pending'"
                @stats-updated="handleStatsUpdate"
              />
            </a-tab-pane>
            
            <a-tab-pane 
              v-if="canManageMembers" 
              key="invitations" 
              tab="Приглашения"
            >
              <div class="p-6">
                <InvitationsList 
                  :organization-id="organization.id"
                  @invitation-updated="handleInvitationUpdate"
                />
              </div>
            </a-tab-pane>
          </a-tabs>
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
import { ref, computed, onMounted, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  ArrowLeftOutlined,
  TeamOutlined,
  UserOutlined,
  UserAddOutlined,
  CrownOutlined,
  CheckCircleOutlined,
  ClockCircleOutlined
} from '@ant-design/icons-vue'
import { message } from 'ant-design-vue'
import AppLayout from '../../Layouts/AppLayout.vue'
import MembersList from '../../Components/MembersList.vue'
import InviteUserForm from '../../Components/InviteUserForm.vue'
import { useUserPermissions } from '../../composables/organizations/useUserPermissions'

// Временный компонент для списка приглашений
const InvitationsList = {
  props: ['organizationId'],
  emits: ['invitation-updated'],
  template: `
    <div>
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-medium text-gray-900">Отправленные приглашения</h3>
        <a-button type="primary" @click="sendInvitation">
          <MailOutlined />
          Отправить приглашение
        </a-button>
      </div>
      
      <div v-if="invitations.length === 0" class="text-center py-12">
        <MailOutlined class="text-6xl text-gray-300 mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">Нет отправленных приглашений</h3>
        <p class="text-gray-500">Отправьте первое приглашение, чтобы пригласить пользователей</p>
      </div>
      
      <div v-else class="space-y-4">
        <div 
          v-for="invitation in invitations" 
          :key="invitation.id"
          class="p-4 border border-gray-200 rounded-lg"
        >
          <div class="flex items-center justify-between">
            <div>
              <h4 class="font-medium text-gray-900">{{ invitation.email }}</h4>
              <p class="text-sm text-gray-600">
                Роль: {{ getRoleLabel(invitation.role) }} • 
                Отправлено: {{ formatDate(invitation.created_at) }}
              </p>
            </div>
            <div class="flex items-center space-x-2">
              <a-tag :color="getInvitationStatusColor(invitation.status)">
                {{ getInvitationStatusLabel(invitation.status) }}
              </a-tag>
              <a-button size="small" @click="resendInvitation(invitation)">
                Отправить повторно
              </a-button>
              <a-button size="small" danger @click="cancelInvitation(invitation)">
                Отменить
              </a-button>
            </div>
          </div>
        </div>
      </div>
    </div>
  `,
  data() {
    return {
      invitations: [
        {
          id: 1,
          email: 'user@example.com',
          role: 'member',
          status: 'pending',
          created_at: '2024-01-15T10:00:00Z'
        }
      ]
    }
  },
  methods: {
    sendInvitation() {
      this.$emit('invitation-updated')
    },
    resendInvitation(invitation) {
      console.log('Resend invitation:', invitation)
    },
    cancelInvitation(invitation) {
      console.log('Cancel invitation:', invitation)
    },
    getRoleLabel(role) {
      const labels = {
        member: 'Участник',
        org_admin: 'Администратор'
      }
      return labels[role] || role
    },
    getInvitationStatusColor(status) {
      const colors = {
        pending: 'orange',
        accepted: 'green',
        expired: 'red'
      }
      return colors[status] || 'default'
    },
    getInvitationStatusLabel(status) {
      const labels = {
        pending: 'Ожидает',
        accepted: 'Принято',
        expired: 'Истекло'
      }
      return labels[status] || status
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('ru-RU')
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
const {
  canInviteUsers,
  canManageMembers
} = useUserPermissions()

// Состояние компонента
const activeTab = ref('all')
const inviteModalOpen = ref(false)
const isInviting = ref(false)

// Статистика
const stats = reactive({
  total: 0,
  admins: 0,
  active: 0,
  pending: 0
})

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

const handleInviteUser = () => {
  inviteModalOpen.value = true
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
    
    // Обновляем счетчики
    updateStats()
    
    // Переключаемся на вкладку "Ожидают" если приглашение по email
    if (inviteData.email) {
      activeTab.value = 'pending'
    }
  } catch (error) {
    message.error(error.message || 'Ошибка отправки приглашения')
  } finally {
    isInviting.value = false
  }
}

const handleInviteCancel = () => {
  inviteModalOpen.value = false
}

const handleStatsUpdate = (newStats) => {
  Object.assign(stats, newStats)
}

const handleInvitationUpdate = () => {
  updateStats()
}

// Методы для работы со статистикой
const updateStats = async () => {
  try {
    const response = await fetch(`/api/organizations/${props.organization.id}/members/stats`)
    if (response.ok) {
      const data = await response.json()
      Object.assign(stats, data)
    }
  } catch (error) {
    console.error('Ошибка обновления статистики:', error)
  }
}

// Инициализация
onMounted(() => {
  // Инициализируем статистику данными организации
  stats.total = props.organization.users_count || 0
  stats.admins = Math.floor(stats.total * 0.2) // Примерная оценка
  stats.active = Math.floor(stats.total * 0.9) // Примерная оценка
  stats.pending = stats.total - stats.active
  
  // Получаем актуальную статистику
  updateStats()
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