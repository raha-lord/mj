<template>
  <a-modal
    :open="open"
    :title="isEditing ? 'Редактирование организации' : 'Создание организации'"
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
        {{ isEditing ? 'Сохранить' : 'Создать' }}
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
      <!-- Название организации -->
      <a-form-item 
        label="Название организации" 
        name="name"
        :validate-status="nameValidateStatus"
        :help="nameHelp"
      >
        <a-input
          v-model:value="form.name"
          placeholder="Введите название организации"
          :maxlength="255"
          show-count
          @blur="validateName"
        />
      </a-form-item>

      <!-- Описание организации -->
      <a-form-item 
        label="Описание" 
        name="description"
        :help="'Краткое описание деятельности организации (необязательно)'"
      >
        <a-textarea
          v-model:value="form.description"
          placeholder="Описание организации..."
          :rows="4"
          :maxlength="1000"
          show-count
        />
      </a-form-item>

      <!-- Настройки организации (только при редактировании) -->
      <template v-if="isEditing">
        <a-divider>Настройки</a-divider>
        
        <!-- Статус организации -->
        <a-form-item 
          label="Статус организации" 
          name="is_active"
          :help="'Неактивные организации недоступны для работы участников'"
        >
          <a-switch
            v-model:checked="form.is_active"
            checked-children="Активна"
            un-checked-children="Неактивна"
          />
        </a-form-item>

        <!-- Настройки приглашений -->
        <a-form-item 
          label="Кто может приглашать участников" 
          name="invite_policy"
        >
          <a-radio-group v-model:value="form.invite_policy">
            <a-radio value="admins_only">Только администраторы</a-radio>
            <a-radio value="all_members">Все участники</a-radio>
          </a-radio-group>
        </a-form-item>

        <!-- Настройки видимости проектов -->
        <a-form-item 
          label="Видимость проектов по умолчанию" 
          name="default_project_visibility"
        >
          <a-radio-group v-model:value="form.default_project_visibility">
            <a-radio value="organization">Для участников организации</a-radio>
            <a-radio value="project_members">Только для участников проекта</a-radio>
          </a-radio-group>
        </a-form-item>
      </template>

      <!-- Информация о текущих участниках (только при редактировании) -->
      <template v-if="isEditing && organization">
        <a-divider>Информация</a-divider>
        
        <a-descriptions :column="2" size="small">
          <a-descriptions-item label="Количество участников">
            <a-tag color="blue">{{ organization.users_count || 0 }}</a-tag>
          </a-descriptions-item>
          <a-descriptions-item label="Количество проектов">
            <a-tag color="green">{{ organization.projects_count || 0 }}</a-tag>
          </a-descriptions-item>
          <a-descriptions-item label="Дата создания">
            {{ formatDate(organization.created_at) }}
          </a-descriptions-item>
          <a-descriptions-item label="Последнее обновление">
            {{ formatDate(organization.updated_at) }}
          </a-descriptions-item>
        </a-descriptions>
      </template>
    </a-form>
  </a-modal>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { message } from 'ant-design-vue'

// Пропсы
const props = defineProps({
  open: {
    type: Boolean,
    default: false
  },
  organization: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
})

// События
const emit = defineEmits(['update:open', 'submit', 'cancel'])

// Состояние формы
const formRef = ref()
const form = ref({
  name: '',
  description: '',
  is_active: true,
  invite_policy: 'admins_only',
  default_project_visibility: 'organization'
})

// Валидация
const nameValidateStatus = ref('')
const nameHelp = ref('')

const formRules = {
  name: [
    { required: true, message: 'Название организации обязательно' },
    { min: 2, max: 255, message: 'Название должно быть от 2 до 255 символов' },
    { validator: validateNameUnique, trigger: 'blur' }
  ]
}

// Вычисляемые свойства
const isEditing = computed(() => !!props.organization)

const isFormValid = computed(() => {
  return form.value.name.trim().length >= 2 && 
         form.value.name.trim().length <= 255 &&
         nameValidateStatus.value !== 'error'
})

// Методы валидации
async function validateNameUnique(rule, value) {
  if (!value || value.trim().length < 2) return Promise.resolve()
  
  // Пропускаем валидацию для текущего названия при редактировании
  if (isEditing.value && value.trim() === props.organization?.name) {
    return Promise.resolve()
  }

  // Здесь можно добавить проверку уникальности через API
  // Пока просто базовая валидация
  const forbiddenNames = ['admin', 'api', 'www', 'test', 'system']
  if (forbiddenNames.includes(value.toLowerCase())) {
    return Promise.reject(new Error('Это название зарезервировано'))
  }

  return Promise.resolve()
}

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

  if (form.value.name.trim().length > 255) {
    nameValidateStatus.value = 'error'
    nameHelp.value = 'Название не должно превышать 255 символов'
    return
  }

  try {
    await validateNameUnique(null, form.value.name.trim())
    nameValidateStatus.value = 'success'
    nameHelp.value = ''
  } catch (error) {
    nameValidateStatus.value = 'error'
    nameHelp.value = error.message
  }
}

// Методы форматирования
const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Обработчики событий
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
    message.warning('Пожалуйста, исправьте ошибки в форме')
    return
  }

  const formData = {
    name: form.value.name.trim(),
    description: form.value.description.trim() || null
  }

  // Добавляем дополнительные поля только при редактировании
  if (isEditing.value) {
    formData.is_active = form.value.is_active
    formData.invite_policy = form.value.invite_policy
    formData.default_project_visibility = form.value.default_project_visibility
  }

  emit('submit', formData)
}

const handleCancel = () => {
  emit('cancel')
}

// Сброс формы
const resetForm = () => {
  form.value = {
    name: '',
    description: '',
    is_active: true,
    invite_policy: 'admins_only',
    default_project_visibility: 'organization'
  }
  nameValidateStatus.value = ''
  nameHelp.value = ''
  
  nextTick(() => {
    formRef.value?.clearValidate()
  })
}

// Заполнение формы при редактировании
const fillForm = (organization) => {
  if (organization) {
    form.value = {
      name: organization.name || '',
      description: organization.description || '',
      is_active: organization.is_active !== false,
      invite_policy: organization.invite_policy || 'admins_only',
      default_project_visibility: organization.default_project_visibility || 'organization'
    }
  } else {
    resetForm()
  }
}

// Watchers
watch(() => props.open, (isOpen) => {
  if (isOpen) {
    fillForm(props.organization)
  }
})

watch(() => props.organization, (newOrg) => {
  if (props.open) {
    fillForm(newOrg)
  }
})
</script>

<style scoped>
.ant-descriptions-item-label {
  @apply font-medium text-gray-700;
}

.ant-descriptions-item-content {
  @apply text-gray-900;
}

.ant-radio-group {
  @apply w-full;
}

.ant-radio {
  @apply block mb-2;
}
</style>