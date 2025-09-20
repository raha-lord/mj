<template>
  <a-select
    :value="currentRole"
    :placeholder="placeholder"
    :size="size"
    :disabled="disabled || isLoading"
    :loading="isLoading"
    @change="handleRoleChange"
    class="user-role-selector"
    :class="{ 'w-32': !fullWidth, 'w-full': fullWidth }"
  >
    <a-select-option
      v-for="role in availableRoles"
      :key="role.value"
      :value="role.value"
      :disabled="!canAssignRole(role.value)"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <component :is="role.icon" :class="role.iconClass" />
          <span>{{ role.label }}</span>
        </div>
        <a-tag 
          v-if="role.value === currentRole" 
          color="blue" 
          size="small"
        >
          Текущая
        </a-tag>
      </div>
    </a-select-option>
  </a-select>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { 
  CrownOutlined, 
  TeamOutlined, 
  UserOutlined 
} from '@ant-design/icons-vue'
import { Modal, message } from 'ant-design-vue'
import { useUserPermissions } from '../composables/organizations/useUserPermissions'

// Пропсы
const props = defineProps({
  user: {
    type: Object,
    required: true
  },
  currentRole: {
    type: String,
    required: true
  },
  organizationId: {
    type: [String, Number],
    required: true
  },
  size: {
    type: String,
    default: 'default'
  },
  placeholder: {
    type: String,
    default: 'Выберите роль'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  fullWidth: {
    type: Boolean,
    default: false
  },
  confirmRoleChange: {
    type: Boolean,
    default: true
  }
})

// События
const emit = defineEmits(['role-changed', 'error'])

// Composables
const {
  canManageMembers,
  isSuperUser,
  getRoleLabel
} = useUserPermissions()

// Состояние компонента
const isLoading = ref(false)

// Доступные роли
const availableRoles = computed(() => {
  const roles = [
    {
      value: 'member',
      label: 'Участник',
      icon: UserOutlined,
      iconClass: 'text-gray-500',
      description: 'Может просматривать и работать с задачами'
    },
    {
      value: 'org_admin',
      label: 'Администратор',
      icon: CrownOutlined,
      iconClass: 'text-yellow-500',
      description: 'Может управлять участниками и настройками'
    }
  ]

  // Добавляем роль супер-пользователя только если текущий пользователь - супер-пользователь
  if (isSuperUser.value) {
    roles.push({
      value: 'super_user',
      label: 'Супер-пользователь',
      icon: TeamOutlined,
      iconClass: 'text-red-500',
      description: 'Полный доступ ко всем организациям'
    })
  }

  return roles
})

// Проверка возможности назначения роли
const canAssignRole = (roleValue) => {
  // Нельзя назначать роль супер-пользователя (это системная роль)
  if (roleValue === 'super_user' && !isSuperUser.value) {
    return false
  }

  // Обычные администраторы организации не могут назначать супер-пользователей
  if (roleValue === 'super_user' && !isSuperUser.value) {
    return false
  }

  // Нужны права управления участниками
  return canManageMembers.value
}

// Получение описания роли
const getRoleDescription = (roleValue) => {
  const role = availableRoles.value.find(r => r.value === roleValue)
  return role ? role.description : ''
}

// Обработчик изменения роли
const handleRoleChange = async (newRole) => {
  if (newRole === props.currentRole) {
    return
  }

  if (!canAssignRole(newRole)) {
    message.error('У вас нет прав для назначения этой роли')
    return
  }

  // Подтверждение изменения роли
  if (props.confirmRoleChange) {
    const confirmed = await showRoleChangeConfirmation(newRole)
    if (!confirmed) {
      return
    }
  }

  await updateUserRole(newRole)
}

// Показ диалога подтверждения
const showRoleChangeConfirmation = (newRole) => {
  return new Promise((resolve) => {
    const oldRoleLabel = getRoleLabel(props.currentRole)
    const newRoleLabel = getRoleLabel(newRole)
    const userName = props.user.name || props.user.email

    let content = `Изменить роль пользователя ${userName} с "${oldRoleLabel}" на "${newRoleLabel}"?`
    
    // Дополнительное предупреждение для критичных изменений
    if (newRole === 'super_user') {
      content += '\n\nВНИМАНИЕ: Супер-пользователь получит полный доступ ко всем организациям!'
    } else if (props.currentRole === 'org_admin' && newRole === 'member') {
      content += '\n\nПользователь потеряет права администратора организации.'
    }

    Modal.confirm({
      title: 'Изменение роли пользователя',
      content,
      okText: 'Изменить',
      cancelText: 'Отмена',
      okType: newRole === 'super_user' ? 'danger' : 'primary',
      onOk: () => resolve(true),
      onCancel: () => resolve(false)
    })
  })
}

// Обновление роли пользователя
const updateUserRole = async (newRole) => {
  isLoading.value = true
  
  try {
    const response = await fetch(
      `/api/organizations/${props.organizationId}/members/${props.user.id}/role`,
      {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ role: newRole })
      }
    )

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Ошибка изменения роли')
    }

    const data = await response.json()
    
    message.success(`Роль пользователя успешно изменена на "${getRoleLabel(newRole)}"`)
    emit('role-changed', props.user, newRole, data)
    
  } catch (error) {
    console.error('Ошибка изменения роли:', error)
    message.error(error.message || 'Ошибка изменения роли пользователя')
    emit('error', error)
  } finally {
    isLoading.value = false
  }
}

// Форматирование для отображения
const formatRoleForDisplay = (roleValue) => {
  const role = availableRoles.value.find(r => r.value === roleValue)
  return role ? role.label : roleValue
}
</script>

<style scoped>
.user-role-selector {
  @apply min-w-fit;
}

.user-role-selector .ant-select-selector {
  @apply border-gray-300;
}

.user-role-selector.ant-select-focused .ant-select-selector {
  @apply border-blue-500 shadow-sm;
}

.user-role-selector .ant-select-arrow {
  @apply text-gray-400;
}
</style>