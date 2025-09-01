<template>
  <a-modal
    :open="isOpen"
    :title="modalTitle"
    width="600px"
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
      <div class="mt-4 text-gray-500">Загрузка данных проекта...</div>
    </div>

    <!-- Project Form -->
    <a-form
      v-else
      :model="form"
      layout="vertical"
      :disabled="form.processing || mode === 'view'"
    >
      <!-- Name Field -->
      <a-form-item 
        label="Название проекта"
        v-bind="getFieldError('name')"
      >
        <a-input
          v-model:value="form.name"
          placeholder="Введите название проекта"
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
          placeholder="Описание проекта"
          :rows="4"
          :disabled="mode === 'view'"
          @input="clearFieldError('description')"
        />
      </a-form-item>

      <!-- Status Field -->
      <a-form-item 
        label="Статус"
        v-bind="getFieldError('status')"
      >
        <a-select
          v-model:value="form.status"
          placeholder="Выберите статус"
          :disabled="mode === 'view'"
          @change="clearFieldError('status')"
        >
          <a-select-option value="active">Активный</a-select-option>
          <a-select-option value="inactive">Неактивный</a-select-option>
          <a-select-option value="completed">Завершен</a-select-option>
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
  project: {
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
  }
})

// Emits
const emit = defineEmits(['close', 'submit'])

// Form setup
const form = useForm({
  name: '',
  description: '',
  status: 'active'
})

// Error handling
const { getFieldError, clearFieldError, hasErrors, getFirstError } = useFormErrors(form)

// Computed
const modalTitle = computed(() => {
  const titles = {
    create: 'Создание проекта',
    edit: 'Редактирование проекта', 
    view: 'Просмотр проекта'
  }
  return titles[props.mode] || 'Проект'
})

const submitButtonText = computed(() => {
  return props.mode === 'create' ? 'Создать' : 'Сохранить'
})

// Watch project changes to populate form
watch(() => props.project, (newProject) => {
  if (newProject && props.mode !== 'create') {
    // Populate form with project data
    form.name = newProject.name || ''
    form.description = newProject.description || ''
    form.status = newProject.status || 'active'
  } else if (props.mode === 'create') {
    // Reset form for creation
    form.reset()
    form.status = 'active'
  }
}, { immediate: true })

// Methods
const closeModal = () => {
  form.reset()
  form.clearErrors()
  emit('close')
}

const handleSubmit = () => {
  const url = props.mode === 'create' ? '/projects' : `/projects/${props.project.id}`
  const method = props.mode === 'create' ? 'post' : 'put'

  form[method](url, {
    onSuccess: () => {
      emit('submit', { mode: props.mode, project: props.project })
      closeModal()
    },
    onError: () => {
      // Ошибки уже обработаны useFormErrors
    }
  })
}
</script>