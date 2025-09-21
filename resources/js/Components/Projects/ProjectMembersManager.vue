<template>
  <div class="project-members-manager">
    <!-- Заголовок и кнопка добавления -->
    <div class="flex items-center justify-between">
      <div>
        <h3 class="text-lg font-medium text-gray-900">Участники проекта</h3>
      </div>
      <a-button
        v-if="canManage"
        type="primary"
        @click="openAddMemberModal"
        :loading="isLoading"
      >
        <UserAddOutlined />
        Добавить участников
      </a-button>
    </div>

    <!-- Список участников -->
    <div v-if="isLoading" class="space-y-1">
      <a-card v-for="i in 3" :key="i" :loading="true" />
    </div>

    <div v-else-if="members.length === 0" class="text-center py-8">
      <UserOutlined class="text-4xl text-gray-300 mb-4" />
      <h4 class="text-lg font-medium text-gray-900 mb-2">Нет участников</h4>
      <p class="text-gray-500 mb-4">
        Добавьте участников для предоставления доступа к проекту
      </p>
    </div>

    <div v-else class="space-y-3">
      <a-card
        v-for="member in members"
        :key="member.id"
        size="small"
        class="member-card"
      >
        <div class="flex items-center justify-between">
          <!-- Информация о пользователе -->
          <div class="flex items-center space-x-3">
            <a-avatar
              :size="40"
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
                <a-tag 
                  :color="getRoleColor(member.pivot.role)" 
                  size="small"
                >
                  {{ getRoleLabel(member.pivot.role) }}
                </a-tag>
              </div>
              <p class="text-gray-600 text-sm">{{ member.email }}</p>
              <p class="text-xs text-gray-500 mt-1">
                Добавлен: {{ formatDate(member.pivot.joined_at) }}
              </p>
            </div>
          </div>

          <!-- Действия -->
          <div class="flex items-center space-x-2">
            <!-- Селектор роли -->
            <a-select
              v-if="canManageMember(member)"
              :value="member.pivot.role"
              @change="(value) => handleRoleChange(member, value)"
              size="small"
              style="width: 100px"
            >
              <a-select-option value="member">Участник</a-select-option>
              <a-select-option value="manager">Менеджер</a-select-option>
            </a-select>

            <!-- Кнопка удаления -->
            <a-button
              v-if="canManageMember(member)"
              type="text"
              size="small"
              danger
              @click="handleRemoveMember(member)"
              :title="`Удалить ${member.name || member.email} из проекта`"
            >
              <DeleteOutlined />
            </a-button>
          </div>
        </div>
      </a-card>
    </div>

    <!-- Модал добавления участников -->
    <a-modal
      v-model:open="showAddMemberModal"
      title="Добавить участников в проект"
      :confirm-loading="isAdding"
      @ok="handleAddMembers"
      @cancel="handleCancelAdd"
      width="700px"
    >
      <div class="space-y-4">
        <!-- Поиск -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Поиск пользователей
          </label>
          <a-input
            v-model:value="searchQuery"
            placeholder="Поиск по имени или email..."
            @input="handleSearch"
            allow-clear
          >
            <template #prefix>
              <SearchOutlined />
            </template>
          </a-input>
        </div>

        <!-- Роль по умолчанию -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Роль по умолчанию для выбранных участников
          </label>
          <a-select
            v-model:value="defaultRole"
            placeholder="Выберите роль"
            style="width: 100%"
          >
            <a-select-option value="member">Участник</a-select-option>
            <a-select-option value="manager">Менеджер проекта</a-select-option>
          </a-select>
        </div>

        <!-- Список пользователей -->
        <div>
          <div class="flex items-center justify-between mb-3">
            <label class="block text-sm font-medium text-gray-700">
              Выберите участников ({{ selectedUsers.length }} выбрано)
            </label>
            <div class="space-x-2">
              <a-button size="small" @click="selectAll">
                Выбрать всех
              </a-button>
              <a-button size="small" @click="deselectAll">
                Снять выбор
              </a-button>
            </div>
          </div>

          <div v-if="isSearching" class="text-center py-4">
            <a-spin />
            <p class="text-gray-500 mt-2">Загрузка пользователей...</p>
          </div>

          <div v-else-if="filteredUsers.length === 0" class="text-center py-8 text-gray-500">
            <UserOutlined class="text-2xl mb-2" />
            <p>Нет доступных пользователей</p>
          </div>

          <div v-else class="max-h-96 overflow-y-auto border rounded-lg">
            <div
              v-for="user in filteredUsers"
              :key="user.id"
              class="flex items-center p-3 border-b last:border-b-0 hover:bg-gray-50 cursor-pointer"
              @click="toggleUser(user.id)"
            >
              <a-checkbox
                :checked="selectedUsers.includes(user.id)"
                @change="() => toggleUser(user.id)"
                class="mr-3"
              />
              
              <a-avatar
                :size="32"
                :src="user.avatar"
                :style="{ backgroundColor: getUserColor(user.id) }"
                class="mr-3"
              >
                {{ getUserInitials(user) }}
              </a-avatar>
              
              <div class="flex-1">
                <div class="font-medium text-gray-900">
                  {{ user.name || 'Без имени' }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ user.email }}
                </div>
              </div>

              <!-- Индивидуальная роль -->
              <a-select
                v-if="selectedUsers.includes(user.id)"
                :value="userRoles[user.id] || defaultRole"
                @change="(value) => setUserRole(user.id, value)"
                size="small"
                style="width: 100px"
                @click.stop
              >
                <a-select-option value="member">Участник</a-select-option>
                <a-select-option value="manager">Менеджер</a-select-option>
              </a-select>
            </div>
          </div>
        </div>

      </div>
    </a-modal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import {
  UserOutlined,
  UserAddOutlined,
  DeleteOutlined,
  SearchOutlined
} from '@ant-design/icons-vue'
import { Modal, message } from 'ant-design-vue'

// Пропсы
const props = defineProps({
  projectId: {
    type: [String, Number],
    required: true
  },
  canManage: {
    type: Boolean,
    default: false
  }
})

// Состояние компонента
const members = ref([])
const isLoading = ref(true)
const showAddMemberModal = ref(false)
const isAdding = ref(false)
const isSearching = ref(false)
const availableUsers = ref([])

// Новые переменные для множественного выбора
const searchQuery = ref('')
const selectedUsers = ref([])
const userRoles = ref({})
const defaultRole = ref('member')
const filteredUsers = ref([])

const newMember = ref({
  userId: null,
  role: 'member'
})

// Методы для работы с участниками
const fetchMembers = async () => {
  isLoading.value = true
  try {
    const response = await fetch(`/api/projects/${props.projectId}/members`, {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
      },
      credentials: 'same-origin'
    })
    
    if (!response.ok) throw new Error(`HTTP ${response.status}`)
    
    const data = await response.json()
    members.value = data.data || []
  } catch (error) {
    console.error('Error fetching project members:', error)
    message.error('Ошибка загрузки участников проекта')
  } finally {
    isLoading.value = false
  }
}

// Загрузка всех доступных пользователей
const fetchAllAvailableUsers = async () => {
  isSearching.value = true
  try {
    const response = await fetch(`/api/projects/${props.projectId}/available-users`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
      },
      credentials: 'same-origin'
    })
    
    if (!response.ok) throw new Error(`HTTP ${response.status}`)
    
    const data = await response.json()
    availableUsers.value = data.data || []
    filteredUsers.value = data.data || []
  } catch (error) {
    console.error('Error fetching users:', error)
    message.error('Ошибка загрузки пользователей')
  } finally {
    isSearching.value = false
  }
}

// Поиск пользователей (локальная фильтрация)
const handleSearch = () => {
  const query = searchQuery.value.toLowerCase().trim()
  
  if (!query) {
    filteredUsers.value = availableUsers.value
    return
  }
  
  filteredUsers.value = availableUsers.value.filter(user => {
    const name = user.name?.toLowerCase() || ''
    const email = user.email?.toLowerCase() || ''
    return name.includes(query) || email.includes(query)
  })
}

const handleUserSearch = async (search) => {
  if (!search || search.length < 2) {
    availableUsers.value = []
    return
  }

  isSearching.value = true
  try {
    const response = await fetch(`/api/projects/${props.projectId}/available-users?search=${encodeURIComponent(search)}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
      },
      credentials: 'same-origin'
    })
    
    if (!response.ok) throw new Error(`HTTP ${response.status}`)
    
    const data = await response.json()
    availableUsers.value = data.data || []
  } catch (error) {
    console.error('Error searching users:', error)
    message.error('Ошибка поиска пользователей')
  } finally {
    isSearching.value = false
  }
}

// Добавление множественных участников
const handleAddMembers = async () => {
  if (selectedUsers.value.length === 0) {
    message.error('Выберите хотя бы одного участника')
    return
  }

  isAdding.value = true
  try {
    const members = selectedUsers.value.map(userId => ({
      user_id: userId,
      role: userRoles.value[userId] || defaultRole.value
    }))

    const response = await fetch(`/api/projects/${props.projectId}/members/bulk`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ members })
    })

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Ошибка добавления участников')
    }

    const result = await response.json()
    const addedCount = result.added || selectedUsers.value.length
    
    message.success(`Успешно добавлено ${addedCount} участников в проект`)
    showAddMemberModal.value = false
    resetBulkForm()
    await fetchMembers()
  } catch (error) {
    message.error(error.message || 'Ошибка добавления участников')
  } finally {
    isAdding.value = false
  }
}

const handleAddMember = async () => {
  if (!newMember.value.userId) {
    message.error('Выберите пользователя')
    return
  }

  isAdding.value = true
  try {
    const response = await fetch(`/api/projects/${props.projectId}/members`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        user_id: newMember.value.userId,
        role: newMember.value.role
      })
    })

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Ошибка добавления участника')
    }

    message.success('Участник успешно добавлен в проект')
    showAddMemberModal.value = false
    resetNewMemberForm()
    await fetchMembers()
  } catch (error) {
    message.error(error.message || 'Ошибка добавления участника')
  } finally {
    isAdding.value = false
  }
}

const handleRoleChange = async (member, newRole) => {
  try {
    const response = await fetch(`/api/projects/${props.projectId}/members/${member.id}`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ role: newRole })
    })

    if (!response.ok) throw new Error('Ошибка изменения роли')

    message.success('Роль участника изменена')
    await fetchMembers()
  } catch (error) {
    message.error(error.message || 'Ошибка изменения роли')
  }
}

const handleRemoveMember = (member) => {
  Modal.confirm({
    title: 'Удалить участника?',
    content: `Вы действительно хотите удалить ${member.name || member.email} из проекта?`,
    okText: 'Удалить',
    okType: 'danger',
    cancelText: 'Отмена',
    onOk: () => removeMember(member)
  })
}

const removeMember = async (member) => {
  try {
    const response = await fetch(`/api/projects/${props.projectId}/members/${member.id}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    })

    if (!response.ok) throw new Error('Ошибка удаления участника')

    message.success('Участник удален из проекта')
    await fetchMembers()
  } catch (error) {
    message.error(error.message || 'Ошибка удаления участника')
  }
}

const handleCancelAdd = () => {
  showAddMemberModal.value = false
  resetBulkForm()
}

// Методы для работы с множественным выбором
const toggleUser = (userId) => {
  const index = selectedUsers.value.indexOf(userId)
  if (index > -1) {
    selectedUsers.value.splice(index, 1)
    delete userRoles.value[userId]
  } else {
    selectedUsers.value.push(userId)
    userRoles.value[userId] = defaultRole.value
  }
}

const selectAll = () => {
  filteredUsers.value.forEach(user => {
    if (!selectedUsers.value.includes(user.id)) {
      selectedUsers.value.push(user.id)
      userRoles.value[user.id] = defaultRole.value
    }
  })
}

const deselectAll = () => {
  selectedUsers.value = []
  userRoles.value = {}
}

const setUserRole = (userId, role) => {
  userRoles.value[userId] = role
}

const resetBulkForm = () => {
  searchQuery.value = ''
  selectedUsers.value = []
  userRoles.value = {}
  defaultRole.value = 'member'
  filteredUsers.value = []
  availableUsers.value = []
}

const resetNewMemberForm = () => {
  newMember.value = {
    userId: null,
    role: 'member'
  }
  availableUsers.value = []
}

// Открытие модала с загрузкой пользователей
const openAddMemberModal = async () => {
  showAddMemberModal.value = true
  await fetchAllAvailableUsers()
}

// Вспомогательные методы
const canManageMember = (member) => {
  return props.canManage
}

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
    manager: 'purple'
  }
  return colors[role] || 'default'
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('ru-RU')
}

// Инициализация
onMounted(() => {
  fetchMembers()
})
</script>

<style scoped>
.project-members-manager {
  @apply space-y-1;
}

.member-card {
  @apply transition-all duration-200;
}

.member-card:hover {
  @apply shadow-md;
}
</style>