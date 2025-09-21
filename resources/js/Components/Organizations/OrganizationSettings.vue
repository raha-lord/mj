<template>
  <div class="organization-settings">
    <!-- Заголовок -->
    <div class="mb-8">
      <h2 class="text-2xl font-semibold text-gray-900">Настройки организации</h2>
      <p class="text-gray-600 mt-1">
        Управляйте основными настройками и политиками организации
      </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Основная форма настроек -->
      <div class="lg:col-span-2">
        <a-form
          ref="formRef"
          :model="form"
          :rules="formRules"
          layout="vertical"
          @finish="handleSubmit"
        >
          <!-- Основная информация -->
          <a-card title="Основная информация" class="mb-6">
            <a-form-item 
              label="Название организации" 
              name="name"
              :validate-status="nameValidateStatus"
              :help="nameHelp"
            >
              <a-input
                v-model:value="form.name"
                placeholder="Название организации"
                :maxlength="255"
                show-count
                @blur="validateName"
              />
            </a-form-item>

            <a-form-item 
              label="Описание" 
              name="description"
            >
              <a-textarea
                v-model:value="form.description"
                placeholder="Описание организации..."
                :rows="4"
                :maxlength="1000"
                show-count
              />
            </a-form-item>

            <a-form-item 
              label="Статус организации" 
              name="is_active"
            >
              <a-switch
                v-model:checked="form.is_active"
                checked-children="Активна"
                un-checked-children="Неактивна"
              />
              <div class="text-sm text-gray-500 mt-1">
                Неактивные организации недоступны для работы участников
              </div>
            </a-form-item>
          </a-card>

          <!-- Политики доступа -->
          <a-card title="Политики доступа" class="mb-6">
            <a-form-item 
              label="Кто может приглашать участников" 
              name="invite_policy"
            >
              <a-radio-group v-model:value="form.invite_policy" class="w-full">
                <a-radio value="admins_only" class="radio-block">
                  <div>
                    <div class="font-medium">Только администраторы</div>
                    <div class="text-sm text-gray-500">
                      Только администраторы организации могут приглашать новых участников
                    </div>
                  </div>
                </a-radio>
                <a-radio value="all_members" class="radio-block">
                  <div>
                    <div class="font-medium">Все участники</div>
                    <div class="text-sm text-gray-500">
                      Любой участник организации может приглашать новых пользователей
                    </div>
                  </div>
                </a-radio>
              </a-radio-group>
            </a-form-item>

            <a-form-item 
              label="Видимость проектов по умолчанию" 
              name="default_project_visibility"
            >
              <a-radio-group v-model:value="form.default_project_visibility" class="w-full">
                <a-radio value="organization" class="radio-block">
                  <div>
                    <div class="font-medium">Для участников организации</div>
                    <div class="text-sm text-gray-500">
                      Новые проекты видны всем участникам организации
                    </div>
                  </div>
                </a-radio>
                <a-radio value="project_members" class="radio-block">
                  <div>
                    <div class="font-medium">Только для участников проекта</div>
                    <div class="text-sm text-gray-500">
                      Новые проекты видны только назначенным участникам
                    </div>
                  </div>
                </a-radio>
              </a-radio-group>
            </a-form-item>

            <a-form-item 
              label="Автоматическое одобрение участников" 
              name="auto_approve_members"
            >
              <a-switch
                v-model:checked="form.auto_approve_members"
                checked-children="Включено"
                un-checked-children="Выключено"
              />
              <div class="text-sm text-gray-500 mt-1">
                Если включено, новые участники автоматически получают доступ без одобрения администратора
              </div>
            </a-form-item>
          </a-card>

          <!-- Уведомления -->
          <a-card title="Настройки уведомлений" class="mb-6">
            <a-form-item 
              label="Email уведомления" 
              name="email_notifications"
            >
              <a-checkbox-group 
                v-model:value="form.email_notifications" 
                class="w-full"
              >
                <div class="space-y-3">
                  <a-checkbox value="new_member" class="checkbox-block">
                    <div>
                      <div class="font-medium">Новые участники</div>
                      <div class="text-sm text-gray-500">
                        Уведомлять администраторов о новых участниках
                      </div>
                    </div>
                  </a-checkbox>
                  <a-checkbox value="project_created" class="checkbox-block">
                    <div>
                      <div class="font-medium">Новые проекты</div>
                      <div class="text-sm text-gray-500">
                        Уведомлять о создании новых проектов
                      </div>
                    </div>
                  </a-checkbox>
                  <a-checkbox value="task_assigned" class="checkbox-block">
                    <div>
                      <div class="font-medium">Назначение задач</div>
                      <div class="text-sm text-gray-500">
                        Уведомлять участников о назначенных задачах
                      </div>
                    </div>
                  </a-checkbox>
                </div>
              </a-checkbox-group>
            </a-form-item>
          </a-card>

          <!-- Кнопки действий -->
          <div class="flex items-center justify-between">
            <a-button 
              type="primary" 
              :loading="isLoading"
              @click="handleSubmit"
              :disabled="!hasChanges"
            >
              Сохранить изменения
            </a-button>
            <a-button @click="handleReset" :disabled="isLoading">
              Сбросить
            </a-button>
          </div>
        </a-form>
      </div>

      <!-- Боковая панель с дополнительной информацией -->
      <div class="space-y-6">
        <!-- Статистика организации -->
        <a-card title="Статистика" size="small">
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-gray-600">Участников:</span>
              <a-tag color="blue">{{ organization?.users_count || 0 }}</a-tag>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-gray-600">Проектов:</span>
              <a-tag color="green">{{ organization?.projects_count || 0 }}</a-tag>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-gray-600">Задач:</span>
              <a-tag color="orange">{{ organization?.tasks_count || 0 }}</a-tag>
            </div>
          </div>
        </a-card>

        <!-- Информация о создании -->
        <a-card title="Информация" size="small">
          <div class="space-y-2 text-sm">
            <div>
              <span class="text-gray-600">Создана:</span>
              <div class="mt-1">{{ formatDate(organization?.created_at) }}</div>
            </div>
            <div>
              <span class="text-gray-600">Обновлена:</span>
              <div class="mt-1">{{ formatDate(organization?.updated_at) }}</div>
            </div>
            <div v-if="organization?.created_by">
              <span class="text-gray-600">Создатель:</span>
              <div class="mt-1">{{ organization.created_by.name || organization.created_by.email }}</div>
            </div>
          </div>
        </a-card>

        <!-- Опасная зона -->
        <a-card title="Опасная зона" size="small" class="border-red-200">
          <div class="space-y-4">
            <a-button 
              danger 
              block 
              @click="handleArchiveOrganization"
              :disabled="!canArchive"
            >
              Архивировать организацию
            </a-button>
            <a-button 
              danger 
              type="primary" 
              block 
              @click="handleDeleteOrganization"
              :disabled="!canDelete"
            >
              Удалить организацию
            </a-button>
            <div class="text-xs text-gray-500">
              Внимание: эти действия необратимы и могут повлиять на всех участников организации
            </div>
          </div>
        </a-card>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { Modal, message } from 'ant-design-vue'
import { useUserPermissions } from '../../composables/organizations/useUserPermissions.js'

// Пропсы
const props = defineProps({
  organization: {
    type: Object,
    required: true
  }
})

// Composables
const { canManageOrganization, isSuperUser } = useUserPermissions()

// Состояние формы
const formRef = ref()
const isLoading = ref(false)
const originalForm = ref({})

const form = ref({
  name: '',
  description: '',
  is_active: true,
  invite_policy: 'admins_only',
  default_project_visibility: 'organization',
  auto_approve_members: true,
  email_notifications: ['new_member', 'project_created']
})

// Валидация
const nameValidateStatus = ref('')
const nameHelp = ref('')

const formRules = {
  name: [
    { required: true, message: 'Название организации обязательно' },
    { min: 2, max: 255, message: 'Название должно быть от 2 до 255 символов' }
  ]
}

// Вычисляемые свойства
const hasChanges = computed(() => {
  return JSON.stringify(form.value) !== JSON.stringify(originalForm.value)
})

const canArchive = computed(() => {
  return canManageOrganization.value && props.organization?.is_active
})

const canDelete = computed(() => {
  return isSuperUser.value && props.organization?.users_count <= 1
})

// Методы валидации
const validateName = async () => {
  if (!form.value.name.trim()) {
    nameValidateStatus.value = 'error'
    nameHelp.value = 'Название организации обязательно'
    return
  }

  if (form.value.name.trim().length < 2) {
    nameValidateStatus.value = 'error'
    nameHelp.value = 'Название должно содержать минимум 2 символа'
    return
  }

  nameValidateStatus.value = 'success'
  nameHelp.value = ''
}

// Методы форматирования
const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Инициализация формы
const initializeForm = () => {
  const formData = {
    name: props.organization.name || '',
    description: props.organization.description || '',
    is_active: props.organization.is_active !== false,
    invite_policy: props.organization.invite_policy || 'admins_only',
    default_project_visibility: props.organization.default_project_visibility || 'organization',
    auto_approve_members: props.organization.auto_approve_members !== false,
    email_notifications: props.organization.email_notifications || ['new_member', 'project_created']
  }
  
  form.value = { ...formData }
  originalForm.value = { ...formData }
}

// Обработчики событий
const handleSubmit = async () => {
  try {
    await formRef.value.validateFields()
    await saveSettings()
  } catch (error) {
    console.error('Validation failed:', error)
  }
}

const saveSettings = async () => {
  isLoading.value = true
  
  try {
    const response = await fetch(`/api/organizations/${props.organization.id}`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify(form.value)
    })

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Ошибка сохранения настроек')
    }

    const data = await response.json()
    
    message.success('Настройки организации успешно сохранены')
    originalForm.value = { ...form.value }
    
    // Обновляем данные на странице
    router.reload({ only: ['organization'] })
    
  } catch (error) {
    message.error(error.message || 'Ошибка сохранения настроек')
  } finally {
    isLoading.value = false
  }
}

const handleReset = () => {
  form.value = { ...originalForm.value }
  nameValidateStatus.value = ''
  nameHelp.value = ''
  formRef.value?.clearValidate()
}

const handleArchiveOrganization = () => {
  Modal.confirm({
    title: 'Архивировать организацию?',
    content: `Вы действительно хотите архивировать организацию "${props.organization.name}"? Участники потеряют доступ к проектам и задачам.`,
    okText: 'Архивировать',
    okType: 'danger',
    cancelText: 'Отмена',
    onOk: async () => {
      try {
        const response = await fetch(`/api/organizations/${props.organization.id}/archive`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        })

        if (!response.ok) throw new Error('Ошибка архивирования')

        message.success('Организация успешно архивирована')
        router.visit('/organizations')
      } catch (error) {
        message.error(error.message || 'Ошибка архивирования организации')
      }
    }
  })
}

const handleDeleteOrganization = () => {
  Modal.confirm({
    title: 'Удалить организацию?',
    content: `Вы действительно хотите УДАЛИТЬ организацию "${props.organization.name}"? Это действие необратимо и удалит все проекты, задачи и данные!`,
    okText: 'УДАЛИТЬ',
    okType: 'danger',
    cancelText: 'Отмена',
    onOk: async () => {
      try {
        const response = await fetch(`/api/organizations/${props.organization.id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        })

        if (!response.ok) throw new Error('Ошибка удаления')

        message.success('Организация успешно удалена')
        router.visit('/organizations')
      } catch (error) {
        message.error(error.message || 'Ошибка удаления организации')
      }
    }
  })
}

// Инициализация
onMounted(() => {
  initializeForm()
})

// Обновление при изменении организации
watch(() => props.organization, () => {
  initializeForm()
}, { immediate: true })
</script>

<style scoped>
.organization-settings {
  @apply p-6;
}

.radio-block {
  @apply block w-full p-3 mb-3 border border-gray-200 rounded-lg;
}

.radio-block:hover {
  @apply border-blue-300 bg-blue-50;
}

.checkbox-block {
  @apply block w-full p-3 border border-gray-200 rounded-lg;
}

.checkbox-block:hover {
  @apply border-blue-300 bg-blue-50;
}

.ant-card-head-title {
  @apply text-lg font-semibold;
}
</style>