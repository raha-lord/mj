<template>
  <a-modal
    :open="isOpen"
    :title="modalTitle"
    width="800px"
    :destroy-on-close="true"
    @cancel="closeModal"
    :footer="null"
  >
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <a-spin size="large" />
      <div class="mt-4 text-gray-500">Загрузка данных проекта...</div>
    </div>

    <!-- Project Form -->
    <ProjectForm
      v-else
      :project="project"
      :loading="form.processing"
      :can-manage-members="canManageMembers"
      :mode="mode"
      @submit="handleSubmit"
      @cancel="closeModal"
    />

    <!-- Global Form Error -->
    <a-alert
      v-if="hasErrors && !form.processing"
      :message="getFirstError"
      type="error"
      show-icon
      class="mt-4"
    />
  </a-modal>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useFormErrors } from '../composables/ui/useFormErrors.js'
import ProjectForm from './ProjectForm.vue'

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
  name: props.project?.name || '',
  description: props.project?.description || '',
  status: props.project?.status || 'active',
  visibility: props.project?.visibility || 'public'
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

const canManageMembers = computed(() => {
  // Можно управлять участниками если пользователь админ организации
  // TODO: получать это из контекста пользователя
  return true
})

// Methods
const closeModal = () => {
  form.reset()
  form.clearErrors()
  emit('close')
}

const handleSubmit = (formData) => {
  // В режиме просмотра не выполняем submit
  if (props.mode === 'view') {
    return
  }

  // Обновляем форму данными из ProjectForm
  Object.keys(formData).forEach(key => {
    if (form.hasOwnProperty(key)) {
      form[key] = formData[key]
    }
  })

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

// Обновляем форму при изменении проекта
watch(() => props.project, (newProject) => {
  if (newProject) {
    form.name = newProject.name || ''
    form.description = newProject.description || ''
    form.status = newProject.status || 'active'
    form.visibility = newProject.visibility || 'public'
  }
}, { immediate: true, deep: true })
</script>