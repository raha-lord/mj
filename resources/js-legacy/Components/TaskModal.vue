<template>
  <Modal
    :is-open="isOpen"
    :title="isEditMode ? 'Редактирование задачи' : 'Создание задачи'"
    size="xl"
    :show-footer="true"
    :show-cancel-button="true"
    :show-confirm-button="true"
    :confirm-text="isEditMode ? 'Сохранить' : 'Создать'"
    cancel-text="Отмена"
    @close="closeModal"
    @confirm="handleSubmit"
  >
    <!-- Loading state -->
    <TaskFormSkeleton v-if="loading" />

    <!-- Form -->
    <form v-else @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Название -->
      <TextInput
        id="task_name"
        v-model="form.name"
        label="Название"
        required
        :error="errors.name?.[0]"
      />

      <!-- Описание -->
      <TextAreaInput
        id="task_description"
        v-model="form.description"
        label="Описание"
        :rows="3"
        :error="errors.description?.[0]"
      />

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Проект -->
        <FormSelect
          id="task_project"
          v-model="form.project_id"
          label="Проект"
          :options="projects"
          placeholder="Выберите проект"
          :error="errors.project_id?.[0]"
        />

        <!-- Статус -->
        <FormSelect
          id="task_status"
          v-model="form.status_id"
          label="Статус"
          :options="statuses"
          required
          :error="errors.status_id?.[0]"
        />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Приоритет -->
        <FormSelect
          id="task_priority"
          v-model="form.priority"
          label="Приоритет"
          :options="PRIORITY_OPTIONS"
          option-value="value"
          option-label="label"
          required
          :error="errors.priority?.[0]"
        />

        <!-- Размер -->
        <FormSelect
          id="task_size"
          v-model="form.size_id"
          label="Размер"
          :options="sizes"
          placeholder="Автоопределение"
          :error="errors.size_id?.[0]"
        >
          <template #option="{ option }">
            {{ option.code }} - {{ option.name }}
          </template>
        </FormSelect>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Оценка времени -->
        <TextInput
          id="task_hours"
          v-model="form.estimated_hours"
          label="Оценка времени (часы)"
          type="number"
          placeholder="0.25"
          :error="errors.estimated_hours?.[0]"
        />

        <!-- Дата завершения -->
        <TextInput
          id="task_due_date"
          v-model="form.d_end"
          label="Срок выполнения"
          type="datetime-local"
          :error="errors.d_end?.[0]"
        />
      </div>

      <!-- Исполнители -->
      <MultiSelect
        id="task_assignees"
        v-model="form.assignees"
        label="Исполнители"
        :options="users"
        :error="errors.assignees?.[0]"
      />
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Удерживайте Ctrl (Cmd) для выбора нескольких исполнителей
      </p>
    </form>
  </Modal>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import Modal from './Modal.vue'
import LoadingSpinner from './shared/LoadingSpinner.vue'
import TextInput from './shared/TextInput.vue'
import TextAreaInput from './shared/TextAreaInput.vue'
import FormSelect from './shared/FormSelect.vue'
import MultiSelect from './shared/MultiSelect.vue'
import TaskFormSkeleton from './shared/TaskFormSkeleton.vue'
import { PRIORITY_OPTIONS } from '@/utils/constants.js'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  task: {
    type: Object,
    default: null
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

const emit = defineEmits(['close', 'submit'])

// Состояние
const loading = ref(false)
const errors = ref({})

// Форма
const form = reactive({
  name: '',
  description: '',
  project_id: '',
  status_id: '',
  priority: 'normal',
  size_id: '',
  estimated_hours: '',
  d_end: '',
  assignees: []
})

// Computed
const isEditMode = computed(() => !!props.task)
const loadingText = computed(() => {
  return isEditMode.value ? 'Сохранение...' : 'Создание...'
})

// Helper functions (объявляем ДО всего остального)
const formatDateForInput = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toISOString().slice(0, 16)
}

const resetForm = () => {
  form.name = ''
  form.description = ''
  form.project_id = ''
  form.status_id = props.statuses.length > 0 ? props.statuses[0].id : ''
  form.priority = 'normal'
  form.size_id = ''
  form.estimated_hours = ''
  form.d_end = ''
  form.assignees = []
}

const populateForm = (task) => {
  form.name = task.name || ''
  form.description = task.description || ''
  form.project_id = task.project_id || ''
  form.status_id = task.status_id || ''
  form.priority = task.priority || 'normal'
  form.size_id = task.size_id || ''
  form.estimated_hours = task.estimated_hours || ''
  form.d_end = task.due_date ? formatDateForInput(task.due_date) : ''
  form.assignees = task.assignees ? task.assignees.map(a => a.id) : []
}

// Watchers (используют функции, объявленные выше)
watch(() => props.task, (newTask) => {
  if (newTask) {
    populateForm(newTask)
  } else {
    resetForm()
  }
}, { immediate: true })

watch(() => props.isOpen, (isOpen) => {
  if (isOpen && !props.task) {
    resetForm()
  }
  if (!isOpen) {
    errors.value = {}
  }
})

const closeModal = () => {
  emit('close')
}

const handleSubmit = async () => {
  if (loading.value) return
  
  errors.value = {}
  loading.value = true
  
  try {
    const formData = { ...form }
    
    // Очищаем пустые значения
    Object.keys(formData).forEach(key => {
      if (formData[key] === '' || formData[key] === null) {
        formData[key] = null
      }
    })
    
    emit('submit', {
      data: formData,
      isEdit: isEditMode.value,
      taskId: props.task?.id
    })
  } catch (error) {
    console.error('Ошибка отправки формы:', error)
  } finally {
    loading.value = false
  }
}

// Expose methods for parent component
const setErrors = (newErrors) => {
  errors.value = newErrors
}

const setLoading = (state) => {
  loading.value = state
}

defineExpose({
  setErrors,
  setLoading
})
</script>