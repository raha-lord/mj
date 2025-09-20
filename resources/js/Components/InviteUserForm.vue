<template>
  <a-modal
    :open="open"
    title="Пригласить пользователя"
    :confirm-loading="loading"
    :width="600"
    @ok="handleSubmit"
    @cancel="handleCancel"
  >
    <template #footer>
      <a-button @click="handleCancel">Отмена</a-button>
      <a-button 
        type="primary" 
        :loading="loading"
        @click="handleSubmit"
        :disabled="!isFormValid"
      >
        Отправить приглашение
      </a-button>
    </template>

    <a-form
      ref="formRef"
      :model="form"
      :rules="formRules"
      layout="vertical"
      @finish="handleFormFinish"
      class="pt-4"
    >
      <!-- Способ приглашения -->
      <a-form-item label="Способ приглашения">
        <a-radio-group v-model:value="inviteMethod" @change="handleMethodChange">
          <a-radio value="email">По email адресу</a-radio>
          <a-radio value="existing">Существующий пользователь</a-radio>
        </a-radio-group>
      </a-form-item>

      <!-- Приглашение по email -->
      <template v-if="inviteMethod === 'email'">
        <a-form-item 
          label="Email адрес" 
          name="email"
          :validate-status="emailValidateStatus"
          :help="emailHelp"
        >
          <a-input
            v-model:value="form.email"
            placeholder="Введите email адрес"
            type="email"
            @blur="validateEmail"
          />
        </a-form-item>

        <a-form-item 
          label="Имя (необязательно)" 
          name="name"
        >
          <a-input
            v-model:value="form.name"
            placeholder="Введите имя пользователя"
            :maxlength="255"
          />
        </a-form-item>
      </template>

      <!-- Выбор существующего пользователя -->
      <template v-if="inviteMethod === 'existing'">
        <a-form-item 
          label="Пользователь" 
          name="user_id"
        >
          <a-select
            v-model:value="form.user_id"
            placeholder="Найдите и выберите пользователя"
            show-search
            :filter-option="false"
            :loading="isSearchingUsers"
            @search="handleUserSearch"
            @change="handleUserSelect"
          >
            <a-select-option
              v-for="user in availableUsers"
              :key="user.id"
              :value="user.id"
            >
              <div class="flex items-center space-x-2">
                <a-avatar 
                  :size="24" 
                  :src="user.avatar"
                  :style="{ backgroundColor: getUserColor(user.id) }"
                >
                  {{ getUserInitials(user) }}
                </a-avatar>
                <div>
                  <div class="font-medium">{{ user.name || user.email }}</div>
                  <div class="text-xs text-gray-500">{{ user.email }}</div>
                </div>
              </div>
            </a-select-option>
          </a-select>
        </a-form-item>
      </template>

      <!-- Роль в организации -->
      <a-form-item 
        label="Роль в организации" 
        name="role"
        :help="getRoleDescription(form.role)"
      >
        <a-radio-group v-model:value="form.role">
          <a-radio value="member">
            <div>
              <div class="font-medium">Участник</div>
              <div class="text-xs text-gray-500">Может просматривать и работать с задачами</div>
            </div>
          </a-radio>
          <a-radio value="org_admin" v-if="canAssignAdminRole">
            <div>
              <div class="font-medium">Администратор организации</div>
              <div class="text-xs text-gray-500">Может управлять участниками и настройками</div>
            </div>
          </a-radio>
        </a-radio-group>
      </a-form-item>

      <!-- Персональное сообщение -->
      <a-form-item 
        label="Персональное сообщение (необязательно)" 
        name="message"
      >
        <a-textarea
          v-model:value="form.message"
          placeholder="Добавьте персональное сообщение к приглашению..."
          :rows="3"
          :maxlength="500"
          show-count
        />
      </a-form-item>

      <!-- Дополнительные опции -->
      <a-form-item>
        <a-checkbox v-model:checked="form.notify_immediately">
          Отправить уведомление немедленно
        </a-checkbox>
      </a-form-item>
    </a-form>

    <!-- Предпросмотр приглашения -->
    <a-alert
      v-if="isFormValid"
      type="info"
      show-icon
      class="mt-4"
    >
      <template #message>
        <div class="text-sm">
          <strong>Предпросмотр приглашения:</strong>
          <div class="mt-2">
            {{ getInvitePreview() }}
          </div>
        </div>
      </template>
    </a-alert>
  </a-modal>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { message } from 'ant-design-vue'
import { useUserPermissions } from '../composables/organizations/useUserPermissions'

// Пропсы
const props = defineProps({
  open: {
    type: Boolean,
    default: false
  },
  organizationId: {
    type: [String, Number],
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
})

// События
const emit = defineEmits(['update:open', 'submit', 'cancel'])

// Composables
const { canManageMembers, getRoleLabel } = useUserPermissions()

// Состояние формы
const formRef = ref()
const inviteMethod = ref('email')
const form = ref({
  email: '',
  name: '',
  user_id: null,
  role: 'member',
  message: '',
  notify_immediately: true
})

// Состояние для поиска пользователей
const availableUsers = ref([])
const isSearchingUsers = ref(false)
const searchTimeout = ref(null)

// Валидация
const emailValidateStatus = ref('')
const emailHelp = ref('')

const formRules = {
  email: [
    { 
      required: () => inviteMethod.value === 'email', 
      message: 'Email адрес обязателен' 
    },
    { 
      type: 'email', 
      message: 'Введите корректный email адрес' 
    }
  ],
  user_id: [
    { 
      required: () => inviteMethod.value === 'existing', 
      message: 'Выберите пользователя' 
    }
  ],
  role: [
    { required: true, message: 'Выберите роль' }
  ]
}

// Вычисляемые свойства
const canAssignAdminRole = computed(() => canManageMembers.value)

const isFormValid = computed(() => {
  if (inviteMethod.value === 'email') {
    return form.value.email && 
           form.value.email.includes('@') && 
           form.value.role &&
           emailValidateStatus.value !== 'error'
  } else {
    return form.value.user_id && form.value.role
  }
})

// Методы валидации
const validateEmail = async () => {
  if (!form.value.email) {
    emailValidateStatus.value = 'error'
    emailHelp.value = 'Email адрес обязателен'
    return
  }

  if (!form.value.email.includes('@')) {
    emailValidateStatus.value = 'error'
    emailHelp.value = 'Введите корректный email адрес'
    return
  }

  // Проверка на существование пользователя с таким email
  try {
    const response = await fetch(`/api/users/check-email?email=${encodeURIComponent(form.value.email)}&organization_id=${props.organizationId}`)
    const data = await response.json()
    
    if (data.exists) {
      if (data.in_organization) {
        emailValidateStatus.value = 'error'
        emailHelp.value = 'Пользователь с таким email уже состоит в организации'
      } else if (data.has_pending_invitation) {
        emailValidateStatus.value = 'error'
        emailHelp.value = 'Пользователю уже отправлено приглашение в эту организацию'
      } else {
        emailValidateStatus.value = 'warning'
        emailHelp.value = 'Пользователь с таким email уже зарегистрирован. Рассмотрите возможность использования "Существующий пользователь"'
      }
    } else {
      emailValidateStatus.value = 'success'
      emailHelp.value = 'Новому пользователю будет отправлено приглашение для регистрации'
    }
  } catch (error) {
    emailValidateStatus.value = 'success'
    emailHelp.value = ''
  }
}

// Методы работы с пользователями
const handleUserSearch = (searchValue) => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }

  searchTimeout.value = setTimeout(async () => {
    if (!searchValue || searchValue.length < 2) {
      availableUsers.value = []
      return
    }

    isSearchingUsers.value = true
    try {
      const response = await fetch(
        `/api/users/search?q=${encodeURIComponent(searchValue)}&exclude_organization=${props.organizationId}`,
        {
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
          },
          credentials: 'same-origin'
        }
      )
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }
      
      const data = await response.json()
      console.log('Found users:', data)
      availableUsers.value = data.data || []
    } catch (error) {
      console.error('Ошибка поиска пользователей:', error)
      availableUsers.value = []
    } finally {
      isSearchingUsers.value = false
    }
  }, 300)
}

const handleUserSelect = (userId) => {
  const selectedUser = availableUsers.value.find(user => user.id === userId)
  if (selectedUser && !form.value.name) {
    form.value.name = selectedUser.name
  }
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

const getRoleDescription = (role) => {
  const descriptions = {
    member: 'Может просматривать и работать с задачами в проектах организации',
    org_admin: 'Может управлять участниками, настройками и всеми проектами организации'
  }
  return descriptions[role] || ''
}

const getInvitePreview = () => {
  if (inviteMethod.value === 'email') {
    const name = form.value.name || form.value.email
    return `${name} будет приглашен(-а) в организацию с ролью "${getRoleLabel(form.value.role)}"`
  } else {
    const selectedUser = availableUsers.value.find(user => user.id === form.value.user_id)
    if (selectedUser) {
      return `${selectedUser.name || selectedUser.email} будет приглашен(-а) в организацию с ролью "${getRoleLabel(form.value.role)}"`
    }
  }
  return ''
}

// Обработчики событий
const handleMethodChange = () => {
  // Сбрасываем форму при изменении метода
  form.value.email = ''
  form.value.name = ''
  form.value.user_id = null
  emailValidateStatus.value = ''
  emailHelp.value = ''
  availableUsers.value = []
}

const handleSubmit = async () => {
  try {
    await formRef.value.validateFields()
    await handleFormFinish()
  } catch (error) {
    console.error('Validation failed:', error)
  }
}

const handleFormFinish = async () => {
  if (!isFormValid.value) {
    message.warning('Пожалуйста, заполните все обязательные поля')
    return
  }

  const inviteData = {
    role: form.value.role,
    message: form.value.message.trim() || null,
    notify_immediately: form.value.notify_immediately
  }

  if (inviteMethod.value === 'email') {
    inviteData.email = form.value.email.trim()
    if (form.value.name.trim()) {
      inviteData.name = form.value.name.trim()
    }
  } else {
    inviteData.user_id = form.value.user_id
  }

  emit('submit', inviteData)
}

const handleCancel = () => {
  emit('cancel')
}

// Сброс формы
const resetForm = () => {
  inviteMethod.value = 'email'
  form.value = {
    email: '',
    name: '',
    user_id: null,
    role: 'member',
    message: '',
    notify_immediately: true
  }
  emailValidateStatus.value = ''
  emailHelp.value = ''
  availableUsers.value = []
  
  nextTick(() => {
    formRef.value?.clearValidate()
  })
}

// Watchers
watch(() => props.open, (isOpen) => {
  if (isOpen) {
    resetForm()
  }
})
</script>

<style scoped>
.ant-radio {
  @apply block mb-3;
}

.ant-radio .ant-radio-wrapper {
  @apply flex items-start;
}
</style>