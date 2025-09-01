<template>
  <a-modal
    :open="isOpen"
    :title="modalTitle"
    width="800px"
    :destroy-on-close="true"
    @cancel="closeModal"
  >
    <template #footer>
      <a-space>
        <a-button @click="closeModal">
          Отмена
        </a-button>
        <a-button 
          v-if="mode !== 'view'"
          type="primary" 
          :loading="form.processing"
          @click="handleSubmit"
        >
          {{ submitButtonText }}
        </a-button>
      </a-space>
    </template>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <a-spin size="large" />
      <div class="mt-4 text-gray-500">Загрузка данных задачи...</div>
    </div>

    <!-- Task Form -->
    <a-form
      v-else
      :model="form"
      layout="vertical"
      :disabled="form.processing || mode === 'view'"
    >
      <!-- Name Field -->
      <a-form-item 
        label="Название задачи"
        v-bind="getFieldError('name')"
      >
        <a-input
          v-model:value="form.name"
          placeholder="Введите название задачи"
          :disabled="mode === 'view'"
          @input="clearFieldError('name')"
        />
      </a-form-item>

      <!-- Description Field -->
      <a-form-item 
        label="Описание"
        v-bind="getFieldError('description')"
      >
        <a-textarea
          v-model:value="form.description"
          placeholder="Описание задачи"
          :rows="4"
          :disabled="mode === 'view'"
          @input="clearFieldError('description')"
        />
      </a-form-item>

      <!-- Project and Status Row -->
      <a-row :gutter="16">
        <a-col :span="12">
          <a-form-item 
            label="Проект"
            v-bind="getFieldError('project_id')"
          >
            <a-select
              v-model:value="form.project_id"
              placeholder="Выберите проект"
              allow-clear
              :disabled="mode === 'view'"
              @change="clearFieldError('project_id')"
            >
              <a-select-option
                v-for="project in projects"
                :key="project.id"
                :value="project.id"
              >
                {{ project.name }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </a-col>
        
        <a-col :span="12">
          <a-form-item 
            label="Статус"
            v-bind="getFieldError('status_id')"
          >
            <a-select
              v-model:value="form.status_id"
              placeholder="Выберите статус"
              :disabled="mode === 'view'"
              @change="clearFieldError('status_id')"
            >
              <a-select-option
                v-for="status in statuses"
                :key="status.id"
                :value="status.id"
              >
                {{ status.name }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </a-col>
      </a-row>

      <!-- Priority and Size Row -->
      <a-row :gutter="16">
        <a-col :span="12">
          <a-form-item 
            label="Приоритет"
            v-bind="getFieldError('priority')"
          >
            <a-select
              v-model:value="form.priority"
              placeholder="Выберите приоритет"
              :disabled="mode === 'view'"
              @change="clearFieldError('priority')"
            >
              <a-select-option value="low">Низкий</a-select-option>
              <a-select-option value="normal">Обычный</a-select-option>
              <a-select-option value="high">Высокий</a-select-option>
              <a-select-option value="urgent">Срочный</a-select-option>
            </a-select>
          </a-form-item>
        </a-col>
        
        <a-col :span="12">
          <a-form-item 
            label="Размер"
            v-bind="getFieldError('size_id')"
          >
            <a-select
              v-model:value="form.size_id"
              placeholder="Выберите размер"
              allow-clear
              :disabled="mode === 'view'"
              @change="clearFieldError('size_id')"
            >
              <a-select-option
                v-for="size in sizes"
                :key="size.id"
                :value="size.id"
              >
                {{ size.name }} ({{ size.code }})
              </a-select-option>
            </a-select>
          </a-form-item>
        </a-col>
      </a-row>

      <!-- Assignee -->
      <a-form-item 
        label="Исполнитель"
        v-bind="getFieldError('user_id')"
      >
        <a-select
          v-model:value="form.user_id"
          placeholder="Выберите исполнителя"
          allow-clear
          :disabled="mode === 'view'"
          @change="clearFieldError('user_id')"
        >
          <a-select-option
            v-for="user in users"
            :key="user.id"
            :value="user.id"
          >
            {{ user.name }} ({{ user.email }})
          </a-select-option>
        </a-select>
      </a-form-item>

      <!-- Global Form Error -->
      <a-alert
        v-if="hasErrors && !form.processing"
        :message="getFirstError"
        type="error"
        show-icon
        class="mb-4"
      />
    </a-form>
  </a-modal>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useFormErrors } from '../composables/ui/useFormErrors.js'

// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  task: {
    type: Object,
    default: null
  },
  mode: {
    type: String,
    default: 'create', // 'create', 'edit', 'view'
    validator: (value) => ['create', 'edit', 'view'].includes(value)
  },
  loading: {
    type: Boolean,
    default: false
  },
  projects: {
    type: Array,
    default: () => []
  },
  statuses: {
    type: Array,
    default: () => []
  },
  sizes: {
    type: Array,
    default: () => []
  },
  users: {
    type: Array,
    default: () => []
  }
})

// Emits
const emit = defineEmits(['close', 'submit'])

// Form setup
const form = useForm({
  name: '',
  description: '',
  project_id: null,
  status_id: null,
  priority: 'normal',
  size_id: null,
  user_id: null
})

// Error handling
const { getFieldError, clearFieldError, hasErrors, getFirstError } = useFormErrors(form)

// Computed
const modalTitle = computed(() => {
  const titles = {
    create: 'Создание задачи',
    edit: 'Редактирование задачи', 
    view: 'Просмотр задачи'
  }
  return titles[props.mode] || 'Задача'
})

const submitButtonText = computed(() => {
  return props.mode === 'create' ? 'Создать' : 'Сохранить'
})

// Watch task changes to populate form
watch(() => props.task, (newTask) => {
  if (newTask && props.mode !== 'create') {
    // Populate form with task data
    form.name = newTask.name || ''
    form.description = newTask.description || ''
    form.project_id = newTask.project_id || null
    form.status_id = newTask.status_id || null
    form.priority = newTask.priority || 'normal'
    form.size_id = newTask.size_id || null
    form.user_id = newTask.user_id || null
  } else if (props.mode === 'create') {
    // Reset form for creation
    form.reset()
    form.priority = 'normal'
  }
}, { immediate: true })

// Methods
const closeModal = () => {
  form.reset()
  form.clearErrors()
  emit('close')
}

const handleSubmit = () => {
  const url = props.mode === 'create' ? '/tasks' : `/tasks/${props.task.id}`
  const method = props.mode === 'create' ? 'post' : 'put'

  form[method](url, {
    onSuccess: () => {
      emit('submit', { mode: props.mode, task: props.task })
      closeModal()
    },
    onError: () => {
      // Ошибки уже обработаны useFormErrors
    }
  })
}
</script>