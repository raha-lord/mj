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
      <div class="mt-4 text-gray-500">Загрузка данных статуса...</div>
    </div>

    <!-- Status Form -->
    <a-form
      v-else
      :model="form"
      layout="vertical"
      :disabled="form.processing || mode === 'view'"
    >
      <!-- Name Field -->
      <a-form-item 
        label="Название статуса"
        v-bind="getFieldError('name')"
      >
        <a-input
          v-model:value="form.name"
          placeholder="Введите название статуса"
          :disabled="mode === 'view'"
          @input="clearFieldError('name')"
        />
      </a-form-item>

      <!-- Slug Field -->
      <a-form-item 
        label="Slug (идентификатор)"
        v-bind="getFieldError('slug')"
      >
        <a-input
          v-model:value="form.slug"
          placeholder="status-slug"
          :disabled="mode === 'view'"
          @input="clearFieldError('slug')"
        />
      </a-form-item>

      <!-- Type and Color Row -->
      <a-row :gutter="16">
        <a-col :span="12">
          <a-form-item 
            label="Тип"
            v-bind="getFieldError('type')"
          >
            <a-select
              v-model:value="form.type"
              placeholder="Выберите тип"
              :disabled="mode === 'view'"
              @change="clearFieldError('type')"
            >
              <a-select-option value="task">Задача</a-select-option>
              <a-select-option value="project">Проект</a-select-option>
            </a-select>
          </a-form-item>
        </a-col>
        
        <a-col :span="12">
          <a-form-item 
            label="Цвет"
            v-bind="getFieldError('color')"
          >
            <a-select
              v-model:value="form.color"
              placeholder="Выберите цвет"
              allow-clear
              :disabled="mode === 'view'"
              @change="clearFieldError('color')"
            >
              <a-select-option value="red">Красный</a-select-option>
              <a-select-option value="orange">Оранжевый</a-select-option>
              <a-select-option value="yellow">Желтый</a-select-option>
              <a-select-option value="green">Зеленый</a-select-option>
              <a-select-option value="blue">Синий</a-select-option>
              <a-select-option value="purple">Фиолетовый</a-select-option>
              <a-select-option value="gray">Серый</a-select-option>
            </a-select>
          </a-form-item>
        </a-col>
      </a-row>

      <!-- Is Final Checkbox -->
      <a-form-item 
        label="Финальный статус"
        v-bind="getFieldError('is_final')"
      >
        <a-checkbox
          v-model:checked="form.is_final"
          :disabled="mode === 'view'"
          @change="clearFieldError('is_final')"
        >
          Является финальным статусом (завершающим)
        </a-checkbox>
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
import { useFormErrors } from '../../composables/ui/useFormErrors.js'

// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  status: {
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
  slug: '',
  type: 'task',
  color: 'blue',
  is_final: false
})

// Error handling
const { getFieldError, clearFieldError, hasErrors, getFirstError } = useFormErrors(form)

// Computed
const modalTitle = computed(() => {
  const titles = {
    create: 'Создание статуса',
    edit: 'Редактирование статуса', 
    view: 'Просмотр статуса'
  }
  return titles[props.mode] || 'Статус'
})

const submitButtonText = computed(() => {
  return props.mode === 'create' ? 'Создать' : 'Сохранить'
})

// Watch status changes to populate form
watch(() => props.status, (newStatus) => {
  if (newStatus && props.mode !== 'create') {
    // Populate form with status data
    form.name = newStatus.name || ''
    form.slug = newStatus.slug || ''
    form.type = newStatus.type || 'task'
    form.color = newStatus.color || 'blue'
    form.is_final = newStatus.is_final || false
  } else if (props.mode === 'create') {
    // Reset form for creation
    form.reset()
    form.type = 'task'
    form.color = 'blue'
    form.is_final = false
  }
}, { immediate: true })

// Methods
const closeModal = () => {
  form.reset()
  form.clearErrors()
  emit('close')
}

const handleSubmit = () => {
  const url = props.mode === 'create' ? '/statuses' : `/statuses/${props.status.id}`
  const method = props.mode === 'create' ? 'post' : 'put'

  form[method](url, {
    onSuccess: () => {
      emit('submit', { mode: props.mode, status: props.status })
      closeModal()
    },
    onError: () => {
      // Ошибки уже обработаны useFormErrors
    }
  })
}
</script>