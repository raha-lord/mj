<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <!-- Название задачи -->
    <div>
      <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Название задачи <span class="text-red-500">*</span>
      </label>
      <input
        id="name"
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
      <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        Описание
      </label>
      <textarea
        id="description"
        v-model="form.description"
        rows="4"
        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
        :class="{ 'border-red-500': errors.description }"
      />
      <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</p>
    </div>

    <!-- Селекты -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Проект -->
      <FormSelect
        id="project_id"
        v-model="form.project_id"
        label="Проект"
        :options="projects"
        placeholder="Выберите проект"
        :error="errors.project_id?.[0]"
      />

      <!-- Статус -->
      <FormSelect
        id="status_id"
        v-model="form.status_id"
        label="Статус"
        :options="statuses"
        placeholder="Выберите статус"
        :error="errors.status_id?.[0]"
      />

      <!-- Приоритет -->
      <FormSelect
        id="priority"
        v-model="form.priority"
        label="Приоритет"
        :options="PRIORITY_OPTIONS"
        option-value="value"
        option-label="label"
        placeholder="Выберите приоритет"
        :error="errors.priority?.[0]"
      />

      <!-- Размер -->
      <FormSelect
        id="size_id"
        v-model="form.size_id"
        label="Размер"
        :options="sizes"
        placeholder="Выберите размер"
        :error="errors.size_id?.[0]"
      >
        <template #option="{ option }">
          {{ option.code }} - {{ option.name }}
        </template>
      </FormSelect>
    </div>

    <!-- Кнопки -->
    <div class="flex justify-end space-x-3">
      <Button
        type="button"
        variant="outline"
        @click="$emit('cancel')"
      >
        Отмена
      </Button>
      <Button
        type="submit"
        variant="primary"
        :loading="loading"
      >
        {{ isEdit ? 'Обновить' : 'Создать' }}
      </Button>
    </div>
  </form>
</template>

<script setup>
import { reactive, computed, watch } from 'vue'
import Button from '../shared/Button.vue'
import FormSelect from '../shared/FormSelect.vue'
import { PRIORITY_OPTIONS } from '@/utils/constants.js'

const props = defineProps({
  task: {
    type: Object,
    default: null
  },
  projects: {
    type: Array,
    required: true
  },
  statuses: {
    type: Array,
    required: true
  },
  sizes: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['submit', 'cancel'])

const form = reactive({
  name: '',
  description: '',
  project_id: '',
  status_id: '',
  priority: '',
  size_id: ''
})

const isEdit = computed(() => !!props.task?.id)

// Инициализация формы при изменении task
watch(() => props.task, (newTask) => {
  if (newTask) {
    form.name = newTask.name || ''
    form.description = newTask.description || ''
    form.project_id = newTask.project_id || ''
    form.status_id = newTask.status_id || ''
    form.priority = newTask.priority || ''
    form.size_id = newTask.size_id || ''
  } else {
    resetForm()
  }
}, { immediate: true })

const resetForm = () => {
  form.name = ''
  form.description = ''
  form.project_id = ''
  form.status_id = ''
  form.priority = ''
  form.size_id = ''
}

const handleSubmit = () => {
  emit('submit', { ...form })
}
</script>