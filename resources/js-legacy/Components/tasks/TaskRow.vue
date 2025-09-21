<template>
  <tr
    class="hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer transition-colors duration-200"
    @click="handleRowClick"
  >
    <!-- Задача -->
    <td class="px-6 py-4">
      <div class="flex flex-col">
        <div class="text-sm font-medium text-gray-900 dark:text-white">
          {{ task.name }}
        </div>
        <div v-if="task.description" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
          {{ truncateText(task.description, 60) }}
        </div>
      </div>
    </td>

    <!-- Проект -->
    <td class="px-6 py-4">
      <div class="text-sm text-gray-900 dark:text-white">
        {{ task.project?.name || '—' }}
      </div>
    </td>

    <!-- Статус -->
    <td class="px-6 py-4">
      <span
        v-if="task.status"
        :class="getStatusClass(task.status)"
        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
      >
        {{ task.status.name }}
      </span>
      <span v-else class="text-gray-400">—</span>
    </td>

    <!-- Приоритет -->
    <td class="px-6 py-4">
      <span
        v-if="task.priority"
        :class="getPriorityClass(task.priority)"
        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
      >
        {{ task.priority }}
      </span>
      <span v-else class="text-gray-400">—</span>
    </td>

    <!-- Размер -->
    <td class="px-6 py-4">
      <span
        v-if="task.size"
        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
      >
        {{ task.size.code }}
      </span>
      <span v-else class="text-gray-400">—</span>
    </td>

    <!-- Действия -->
    <td class="px-6 py-4">
      <div class="flex items-center space-x-2">
        <Button
          size="sm"
          variant="outline"
          @click="handleViewClick"
        >
          Просмотр
        </Button>
        <Button
          size="sm"
          variant="primary"
          @click="handleEditClick"
        >
          Изменить
        </Button>
      </div>
    </td>
  </tr>
</template>

<script setup>
import Button from '../shared/Button.vue'
import { truncateText, getPriorityClass, getStatusClass } from '@/utils/helpers.js'

const props = defineProps({
  task: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['view', 'edit', 'delete'])

const handleRowClick = (event) => {
  // Не запускаем просмотр если кликнули на кнопку
  if (event.target.closest('button')) return
  emit('view', props.task.id)
}

const handleViewClick = (event) => {
  event.stopPropagation()
  emit('view', props.task.id)
}

const handleEditClick = (event) => {
  event.stopPropagation()
  emit('edit', props.task.id)
}


</script>