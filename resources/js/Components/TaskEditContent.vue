<template>
  <!-- Loading state -->
  <div v-if="loading" class="flex justify-center items-center py-12">
    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    <span class="ml-2 text-gray-600 dark:text-gray-300">{{ loadingText }}</span>
  </div>

  <!-- Form -->
  <form v-else @submit.prevent="handleSubmit" class="space-y-4">
    <!-- Название -->
    <div>
      <label for="edit_task_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Название <span class="text-red-500">*</span>
      </label>
      <input
        id="edit_task_name"
        v-model="form.name"
        type="text"
        required
        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
        :class="{ 'border-red-500': errors.name }"
      />
      <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
    </div>

    <!-- Описание -->
    <div>
      <label for="edit_task_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Описание
      </label>
      <textarea
        id="edit_task_description"
        v-model="form.description"
        rows="3"
        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
        :class="{ 'border-red-500': errors.description }"
      />
      <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Проект -->
      <div>
        <label for="edit_task_project" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Проект
        </label>
        <select
          id="edit_task_project"
          v-model="form.project_id"
          class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          :class="{ 'border-red-500': errors.project_id }"
        >
          <option value="">Выберите проект</option>
          <option v-for="project in projects" :key="project.id" :value="project.id">
            {{ project.name }}
          </option>
        </select>
        <p v-if="errors.project_id" class="mt-1 text-sm text-red-600">{{ errors.project_id[0] }}</p>
      </div>

      <!-- Статус -->
      <div>
        <label for="edit_task_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Статус <span class="text-red-500">*</span>
        </label>
        <select
          id="edit_task_status"
          v-model="form.status_id"
          required
          class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          :class="{ 'border-red-500': errors.status_id }"
        >
          <option v-for="status in statuses" :key="status.id" :value="status.id">
            {{ status.name }}
          </option>
        </select>
        <p v-if="errors.status_id" class="mt-1 text-sm text-red-600">{{ errors.status_id[0] }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Приоритет -->
      <div>
        <label for="edit_task_priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Приоритет <span class="text-red-500">*</span>
        </label>
        <select
          id="edit_task_priority"
          v-model="form.priority"
          required
          class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          :class="{ 'border-red-500': errors.priority }"
        >
          <option value="low">Низкий</option>
          <option value="normal">Обычный</option>
          <option value="high">Высокий</option>
          <option value="urgent">Срочный</option>
        </select>
        <p v-if="errors.priority" class="mt-1 text-sm text-red-600">{{ errors.priority[0] }}</p>
      </div>

      <!-- Размер -->
      <div>
        <label for="edit_task_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Размер
        </label>
        <select
          id="edit_task_size"
          v-model="form.size_id"
          class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          :class="{ 'border-red-500': errors.size_id }"
        >
          <option value="">Автоопределение</option>
          <option v-for="size in sizes" :key="size.id" :value="size.id">
            {{ size.code }} - {{ size.name }}
          </option>
        </select>
        <p v-if="errors.size_id" class="mt-1 text-sm text-red-600">{{ errors.size_id[0] }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Оценка времени -->
      <div>
        <label for="edit_task_hours" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Оценка времени (часы)
        </label>
        <input
          id="edit_task_hours"
          v-model="form.estimated_hours"
          type="number"
          step="0.25"
          min="0.25"
          max="1000"
          placeholder="0.25"
          class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          :class="{ 'border-red-500': errors.estimated_hours }"
        />
        <p v-if="errors.estimated_hours" class="mt-1 text-sm text-red-600">{{ errors.estimated_hours[0] }}</p>
      </div>

      <!-- Дата завершения -->
      <div>
        <label for="edit_task_due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Срок выполнения
        </label>
        <input
          id="edit_task_due_date"
          v-model="form.d_end"
          type="datetime-local"
          class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          :class="{ 'border-red-500': errors.d_end }"
        />
        <p v-if="errors.d_end" class="mt-1 text-sm text-red-600">{{ errors.d_end[0] }}</p>
      </div>
    </div>

    <!-- Исполнители -->
    <div>
      <label for="edit_task_assignees" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Исполнители
      </label>
      <select
        id="edit_task_assignees"
        v-model="form.assignees"
        multiple
        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
        :class="{ 'border-red-500': errors.assignees }"
      >
        <option v-for="user in users" :key="user.id" :value="user.id">
          {{ user.name }}
        </option>
      </select>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Удерживайте Ctrl (Cmd) для выбора нескольких исполнителей
      </p>
      <p v-if="errors.assignees" class="mt-1 text-sm text-red-600">{{ errors.assignees[0] }}</p>
    </div>

    <!-- Кнопки -->
    <div class="flex justify-end space-x-3 pt-4">
      <button
        type="button"
        @click="$emit('close')"
        class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
      >
        Отмена
      </button>
      <button
        type="submit"
        :disabled="loading"
        class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
      >
        {{ loading ? 'Сохранение...' : 'Сохранить изменения' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'

const props = defineProps({
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
const loadingText = computed(() => {
  return 'Сохранение...'
})

// Helper functions
const formatDateForInput = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toISOString().slice(0, 16)
}

const populateForm = (task) => {
  if (!task) return
  
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

// Watchers
watch(() => props.task, (newTask) => {
  if (newTask) {
    populateForm(newTask)
  }
}, { immediate: true })

// Methods
const handleSubmit = async () => {
  if (loading.value) return
  
  errors.value = {}
  
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
      isEdit: true,
      taskId: props.task?.id
    })
  } catch (error) {
    console.error('Ошибка отправки формы:', error)
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