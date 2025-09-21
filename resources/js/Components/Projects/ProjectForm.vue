<template>
  <div class="project-form">
    <a-form
      :model="form"
      layout="vertical"
      @finish="handleSubmit"
      :loading="loading"
    >
      <!-- Основная информация -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <a-form-item
          label="Название проекта"
          name="name"
          :rules="isViewMode ? [] : [{ required: true, message: 'Введите название проекта' }]"
        >
          <div v-if="isViewMode" class="text-lg font-medium text-gray-900">
            {{ form.name || 'Без названия' }}
          </div>
          <a-input
            v-else
            v-model:value="form.name"
            placeholder="Название проекта"
            size="large"
          />
        </a-form-item>

        <a-form-item
          label="Статус"
          name="status"
          :rules="isViewMode ? [] : [{ required: true, message: 'Выберите статус' }]"
        >
          <div v-if="isViewMode">
            <a-tag :color="getStatusColor(form.status)" size="large">
              {{ getStatusLabel(form.status) }}
            </a-tag>
          </div>
          <a-select
            v-else
            v-model:value="form.status"
            placeholder="Выберите статус"
            size="large"
          >
            <a-select-option value="active">Активный</a-select-option>
            <a-select-option value="inactive">Неактивный</a-select-option>
            <a-select-option value="completed">Завершен</a-select-option>
          </a-select>
        </a-form-item>
      </div>

      <a-form-item
        label="Описание"
        name="description"
      >
        <div v-if="isViewMode" class="text-gray-700 whitespace-pre-wrap">
          {{ form.description || 'Описание не указано' }}
        </div>
        <a-textarea
          v-else
          v-model:value="form.description"
          placeholder="Описание проекта"
          :rows="4"
        />
      </a-form-item>

      <!-- Настройки приватности -->
      <div class="bg-gray-50 p-4 rounded-lg mb-3">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Настройки доступа</h3>
        
        <a-form-item
          name="visibility"
          class="flex w-full"
          :rules="isViewMode ? [] : [{ required: true, message: 'Выберите тип доступа' }]"
        >
          <div v-if="isViewMode">
            <a-tag 
              :color="form.visibility === 'private' ? 'orange' : 'blue'" 
              size="large"
            >
              <component :is="form.visibility === 'private' ? LockOutlined : GlobalOutlined" class="mr-2" />
              {{ form.visibility === 'private' ? 'Приватный' : 'Публичный' }}
            </a-tag>
          </div>
          <a-radio-group v-else v-model:value="form.visibility" size="large" class="flex w-full">
            <a-radio-button value="public"  class="w-1/2">
              <div class="flex items-center space-x-2">
                <GlobalOutlined />
                <span>Публичный</span>
              </div>
            </a-radio-button>
            <a-radio-button value="private"  class="w-1/2">
              <div class="flex items-center space-x-2">
                <LockOutlined />
                <span>Приватный</span>
              </div>
            </a-radio-button>
          </a-radio-group>
        </a-form-item>

        <!-- Описание типов доступа -->
        <div class="mt-4 p-3 rounded-md" :class="visibilityInfoClass">
          <div class="flex">
            <component :is="visibilityIcon" class="h-5 w-5 mt-0.5 mr-3" />

              <div class="text-sm" :class="visibilityTextClass">
                {{ visibilityDescription }}
              </div>

          </div>
        </div>

      </div>

      <!-- Управление участниками для приватных проектов -->
      <div v-if="form.visibility === 'private' && isEditMode && canManageMembers && !isViewMode">
        <ProjectMembersManager
          :project-id="projectId"
          :can-manage="canManageMembers"
        />
      </div>

      <!-- Просмотр участников для режима просмотра -->
      <div v-if="form.visibility === 'private' && isViewMode && projectId">
        <ProjectMembersManager
          :project-id="projectId"
          :can-manage="false"
        />
      </div>

      <!-- Кнопки действий -->
      <div v-if="!isViewMode" class="flex justify-end space-x-3 pt-6">
        <a-button @click="handleCancel" size="large">
          Отмена
        </a-button>
        <a-button
          type="primary"
          html-type="submit"
          :loading="loading"
          size="large"
        >
          {{ isEditMode ? 'Сохранить изменения' : 'Создать проект' }}
        </a-button>
      </div>

      <!-- Кнопка закрытия для режима просмотра -->
      <div v-else class="flex justify-end pt-6">
        <a-button @click="handleCancel" size="large">
          Закрыть
        </a-button>
      </div>
    </a-form>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import {
  GlobalOutlined,
  LockOutlined,
  InfoCircleOutlined,
  SafetyOutlined
} from '@ant-design/icons-vue'
import ProjectMembersManager from './ProjectMembersManager.vue'

// Пропсы
const props = defineProps({
  project: {
    type: Object,
    default: () => ({})
  },
  loading: {
    type: Boolean,
    default: false
  },
  canManageMembers: {
    type: Boolean,
    default: false
  },
  mode: {
    type: String,
    default: 'create',
    validator: (value) => ['create', 'edit', 'view'].includes(value)
  }
})

// События
const emit = defineEmits(['submit', 'cancel'])

// Состояние формы
const form = ref({
  name: '',
  description: '',
  status: 'active',
  visibility: 'public'
})

// Вычисляемые свойства
const isEditMode = computed(() => {
  return props.project && props.project.id
})

const isViewMode = computed(() => {
  return props.mode === 'view'
})

const projectId = computed(() => {
  return props.project?.id
})

const visibilityIcon = computed(() => {
  return form.value.visibility === 'public' ? InfoCircleOutlined : SafetyOutlined
})

const visibilityTitle = computed(() => {
  return form.value.visibility === 'public' 
    ? 'Публичный проект' 
    : 'Приватный проект'
})

const visibilityDescription = computed(() => {
  return form.value.visibility === 'public'
    ? 'Все участники организации могут видеть и работать с этим проектом'
    : 'Только добавленные участники могут видеть и работать с этим проектом'
})

const visibilityInfoClass = computed(() => {
  return form.value.visibility === 'public'
    ? 'bg-blue-50 border border-blue-200'
    : 'bg-orange-50 border border-orange-200'
})

const visibilityTextClass = computed(() => {
  return form.value.visibility === 'public'
    ? 'text-blue-900'
    : 'text-orange-900'
})

// Методы
const handleSubmit = () => {
  emit('submit', { ...form.value })
}

const handleCancel = () => {
  emit('cancel')
}

const initializeForm = () => {
  if (props.project && props.project.id) {
    form.value = {
      name: props.project.name || '',
      description: props.project.description || '',
      status: props.project.status || 'active',
      visibility: props.project.visibility || 'public'
    }
  } else {
    form.value = {
      name: '',
      description: '',
      status: 'active',
      visibility: 'public'
    }
  }
}

// Отслеживание изменений проекта
watch(() => props.project, () => {
  initializeForm()
}, { immediate: true, deep: true })


// Методы для форматирования статусов
const getStatusLabel = (status) => {
  const labels = {
    active: 'Активный',
    inactive: 'Неактивный', 
    completed: 'Завершен'
  }
  return labels[status] || status
}

const getStatusColor = (status) => {
  const colors = {
    active: 'green',
    inactive: 'orange',
    completed: 'blue'
  }
  return colors[status] || 'default'
}
</script>

<style scoped>
.project-form {
  @apply space-y-6;
}

.ant-radio-button-wrapper {
  @apply flex items-center justify-center;
  min-width: 120px;
}
</style>