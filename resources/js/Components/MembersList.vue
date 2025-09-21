<template>
  <div class="members-list p-6">
    <!-- Заголовок и действия -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h3 class="text-xl font-semibold text-gray-900">Выберете участников</h3>
      </div>
      <a-button
        v-if="canInviteMembers"
        type="primary"
        @click="handleInviteUser"
        :loading="isInviting"
      >
        <UserAddOutlined />
        Пригласить участника
      </a-button>
    </div>

    <!-- Фильтры и поиск -->
    <div class="flex items-center gap-4 mb-6">
      <a-input-search
        v-model:value="searchQuery"
        placeholder="Поиск по имени или email..."
        class="max-w-md"
        allow-clear
      />
      <a-select
        v-model:value="roleFilter"
        placeholder="Фильтр по роли"
        class="w-40"
        allow-clear
      >
        <a-select-option value="org_admin">Администратор</a-select-option>
        <a-select-option value="member">Участник</a-select-option>
      </a-select>
      <a-select
        v-model:value="statusFilter"
        placeholder="Статус"
        class="w-32"
        allow-clear
      >
        <a-select-option value="active">Активный</a-select-option>
        <a-select-option value="pending">Ожидает</a-select-option>
        <a-select-option value="suspended">Заблокирован</a-select-option>
      </a-select>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <a-statistic
        title="Всего участников"
        :value="members.length"
        class="bg-gray-50 p-4 rounded-lg"
      />
      <a-statistic
        title="Администраторы"
        :value="adminCount"
        class="bg-blue-50 p-4 rounded-lg"
      />
      <a-statistic
        title="Участники"
        :value="memberCount"
        class="bg-green-50 p-4 rounded-lg"
      />
      <a-statistic
        title="Ожидают принятия"
        :value="pendingCount"
        class="bg-orange-50 p-4 rounded-lg"
      />
    </div>

    <!-- Список участников -->
    <div v-if="isLoading" class="space-y-4">
      <a-card v-for="i in 5" :key="i" :loading="true" />
    </div>

    <div v-else-if="filteredMembers.length === 0" class="text-center py-12">
      <UserOutlined class="text-6xl text-gray-300 mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">
        {{ searchQuery || roleFilter || statusFilter ? 'Участники не найдены' : 'Нет участников' }}
      </h3>
      <p class="text-gray-500 mb-6">
        {{ searchQuery || roleFilter || statusFilter 
          ? 'Попробуйте изменить параметры поиска'
          : 'Пригласите первого участника в организацию'
        }}
      </p>
      <a-button
        v-if="canInviteMembers && !searchQuery && !roleFilter && !statusFilter"
        type="primary"
        @click="handleInviteUser"
        :loading="isInviting"
      >
        <UserAddOutlined />
        Пригласить участника
      </a-button>
    </div>

    <div v-else class="space-y-4">
      <a-card
        v-for="member in filteredMembers"
        :key="member.id"
        class="member-card"
        :class="{ 'border-l-4 border-l-blue-500': member.id === currentUserId }"
      >
        <div class="flex items-center justify-between">
          <!-- Информация о пользователе -->
          <div class="flex items-center space-x-4">
            <a-avatar
              :size="48"
              :src="member.avatar"
              :style="{ backgroundColor: getUserColor(member.id) }"
            >
              {{ getUserInitials(member) }}
            </a-avatar>
            
            <div>
              <div class="flex items-center space-x-2">
                <h4 class="font-medium text-gray-900">
                  {{ member.name || member.email }}
                </h4>
                <a-tag v-if="member.id === currentUserId" color="blue" size="small">
                  Это вы
                </a-tag>
                <a-tag 
                  v-if="member.pivot.status === 'pending'" 
                  color="orange" 
                  size="small"
                >
                  Ожидает
                </a-tag>
                <a-tag 
                  v-if="member.pivot.status === 'suspended'" 
                  color="red" 
                  size="small"
                >
                  Заблокирован
                </a-tag>
              </div>
              <p class="text-gray-600 text-sm">{{ member.email }}</p>
              <div class="flex items-center space-x-4 text-xs text-gray-500 mt-1">
                <span>Роль: {{ getRoleLabel(member.pivot.role) }}</span>
                <span>Присоединился: {{ formatDate(member.pivot.created_at) }}</span>
                <span v-if="member.last_activity_at">
                  Активность: {{ formatRelativeTime(member.last_activity_at) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Действия -->
          <div class="flex items-center space-x-2">
            <!-- Селектор роли -->
            <UserRoleSelector
              v-if="canManageRole(member)"
              :user="member"
              :current-role="member.pivot.role"
              :organization-id="organizationId"
              @role-changed="handleRoleChanged"
            />

            <!-- Кнопка удаления участника -->
            <a-button
              v-if="canManageMember(member)"
              type="text"
              size="small"
              danger
              @click="handleRemoveMember(member)"
              :title="`Удалить ${member.name || member.email} из организации`"
            >
              <DeleteOutlined />
            </a-button>

            <!-- Дополнительные действия -->
            <a-dropdown
              v-if="canManageMember(member)"
              :trigger="['click']"
              placement="bottomRight"
            >
              <a-button type="text" size="small">
                <MoreOutlined />
              </a-button>
              
              <template #overlay>
                <a-menu>
                  <a-menu-item
                    v-if="member.pivot.status === 'suspended'"
                    @click="handleActivateMember(member)"
                  >
                    <CheckOutlined class="mr-2" />
                    Активировать
                  </a-menu-item>
                  <a-menu-item
                    v-else-if="member.pivot.status === 'active' && member.id !== currentUserId"
                    @click="handleSuspendMember(member)"
                  >
                    <StopOutlined class="mr-2" />
                    Заблокировать
                  </a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>
          </div>
        </div>
      </a-card>
    </div>

    <!-- Модал приглашения пользователя -->
    <InviteUserForm
      v-model:open="inviteModalOpen"
      :organization-id="organizationId"
      :loading="isInviting"
      @submit="handleInviteSubmit"
      @cancel="handleInviteCancel"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import {
  UserOutlined,
  UserAddOutlined,
  MoreOutlined,
  CheckOutlined,
  StopOutlined,
  DeleteOutlined
} from '@ant-design/icons-vue'
import { Modal, message } from 'ant-design-vue'
import { useUserPermissions } from '../composables/organizations/useUserPermissions'
import UserRoleSelector from './UserRoleSelector.vue'
import InviteUserForm from './InviteUserForm.vue'

// Пропсы
const props = defineProps({
  organizationId: {
    type: [String, Number],
    required: true
  },
  currentUserId: {
    type: [String, Number],
    required: true
  }
})

// Composables
const {
  canInviteMembers,
  canManageMembers,
  getRoleLabel
} = useUserPermissions()

// Состояние компонента
const members = ref([])
const isLoading = ref(true)
const searchQuery = ref('')
const roleFilter = ref('')
const statusFilter = ref('')
const inviteModalOpen = ref(false)
const isInviting = ref(false)

// Вычисляемые свойства
const filteredMembers = computed(() => {
  let filtered = members.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(member => 
      (member.name && member.name.toLowerCase().includes(query)) ||
      member.email.toLowerCase().includes(query)
    )
  }

  if (roleFilter.value) {
    filtered = filtered.filter(member => member.pivot.role === roleFilter.value)
  }

  if (statusFilter.value) {
    filtered = filtered.filter(member => member.pivot.status === statusFilter.value)
  }

  return filtered.sort((a, b) => {
    // Текущий пользователь первым
    if (a.id === props.currentUserId) return -1
    if (b.id === props.currentUserId) return 1
    
    // Потом по роли (админы, участники)
    const roleOrder = { org_admin: 0, member: 1 }
    const aOrder = roleOrder[a.pivot.role] || 2
    const bOrder = roleOrder[b.pivot.role] || 2
    
    if (aOrder !== bOrder) return aOrder - bOrder
    
    // Потом по имени
    return (a.name || a.email).localeCompare(b.name || b.email)
  })
})

const adminCount = computed(() => 
  members.value.filter(m => m.pivot.role === 'org_admin').length
)

const memberCount = computed(() => 
  members.value.filter(m => m.pivot.role === 'member').length
)

const pendingCount = computed(() => 
  members.value.filter(m => m.pivot.status === 'pending').length
)

// Методы проверки прав
const canManageMember = (member) => {
  return canManageMembers.value && member.id !== props.currentUserId
}

const canManageRole = (member) => {
  return canManageMembers.value && member.id !== props.currentUserId
}

// Методы форматирования
const getUserInitials = (user) => {
  if (user.name) {
    return user.name.split(' ').map(n => n[0]).join('').toUpperCase()
  }
  return user.email[0].toUpperCase()
}

const getUserColor = (id) => {
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

const formatRelativeTime = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  const now = new Date()
  const diffInHours = Math.floor((now - date) / (1000 * 60 * 60))
  
  if (diffInHours < 1) return 'Только что'
  if (diffInHours < 24) return `${diffInHours} ч. назад`
  
  const diffInDays = Math.floor(diffInHours / 24)
  if (diffInDays < 7) return `${diffInDays} дн. назад`
  
  return formatDate(dateString)
}

// API методы
const fetchMembers = async () => {
  isLoading.value = true
  try {
    const response = await fetch(`/api/organizations/${props.organizationId}/users`, {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
      },
      credentials: 'same-origin'
    })
    
    if (!response.ok) {
      const errorData = await response.text()
      console.error('API Error:', response.status, errorData)
      throw new Error(`HTTP ${response.status}: ${errorData}`)
    }
    
    const data = await response.json()
    console.log('Members data:', data)
    members.value = data.data || []
  } catch (error) {
    console.error('Fetch error:', error)
    message.error(error.message || 'Ошибка загрузки участников')
  } finally {
    isLoading.value = false
  }
}

// Обработчики событий
const handleInviteUser = () => {
  inviteModalOpen.value = true
}

const handleInviteSubmit = async (inviteData) => {
  isInviting.value = true
  try {
    const response = await fetch(`/api/organizations/${props.organizationId}/users`, {
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
    await fetchMembers()
  } catch (error) {
    message.error(error.message || 'Ошибка отправки приглашения')
  } finally {
    isInviting.value = false
  }
}

const handleInviteCancel = () => {
  inviteModalOpen.value = false
}

const handleRoleChanged = async (user, newRole) => {
  try {
    const response = await fetch(`/api/organizations/${props.organizationId}/users/${user.id}`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ role: newRole })
    })

    if (!response.ok) throw new Error('Ошибка изменения роли')

    message.success('Роль успешно изменена')
    await fetchMembers()
  } catch (error) {
    message.error(error.message || 'Ошибка изменения роли')
  }
}

const handleSuspendMember = (member) => {
  Modal.confirm({
    title: 'Заблокировать участника?',
    content: `Вы действительно хотите заблокировать участника ${member.name || member.email}?`,
    okText: 'Заблокировать',
    okType: 'danger',
    cancelText: 'Отмена',
    onOk: () => updateMemberStatus(member, 'suspended')
  })
}

const handleActivateMember = (member) => {
  updateMemberStatus(member, 'active')
}

const handleRemoveMember = (member) => {
  Modal.confirm({
    title: 'Удалить участника?',
    content: `Вы действительно хотите удалить ${member.name || member.email} из организации?`,
    okText: 'Удалить',
    okType: 'danger',
    cancelText: 'Отмена',
    onOk: () => removeMember(member)
  })
}


const updateMemberStatus = async (member, status) => {
  try {
    const response = await fetch(`/api/organizations/${props.organizationId}/users/${member.id}`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ status })
    })

    if (!response.ok) throw new Error('Ошибка изменения статуса')

    const action = status === 'active' ? 'активирован' : 'заблокирован'
    message.success(`Участник ${action}`)
    await fetchMembers()
  } catch (error) {
    message.error(error.message || 'Ошибка изменения статуса')
  }
}

const removeMember = async (member) => {
  try {
    const response = await fetch(`/api/organizations/${props.organizationId}/users/${member.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    })

    if (!response.ok) throw new Error('Ошибка удаления участника')

    message.success('Участник удален из организации')
    await fetchMembers()
  } catch (error) {
    message.error(error.message || 'Ошибка удаления участника')
  }
}

// Инициализация
onMounted(() => {
  fetchMembers()
})

// Обновление при изменении организации
watch(() => props.organizationId, () => {
  fetchMembers()
})
</script>

<style scoped>
.members-list {
  @apply space-y-6;
}

.member-card {
  @apply transition-all duration-200;
}

.member-card:hover {
  @apply shadow-md;
}
</style>