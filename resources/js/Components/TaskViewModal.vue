<template>
  <Modal
    :is-open="isOpen"
    title="Просмотр задачи"
    size="2xl"
    max-height="80vh"
    :show-footer="true"
    :show-cancel-button="false"
    :show-confirm-button="false"
    @close="$emit('close')"
  >
    <!-- Loading state -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <span class="ml-2 text-gray-600 dark:text-gray-300">Загрузка задачи...</span>
    </div>

    <!-- Task content -->
    <div v-else-if="task" class="space-y-6">
      <!-- Mode switcher -->
      <div class="flex space-x-2 mb-4">
        <button
          @click="$emit('switch-mode', 'view')"
          :class="mode === 'view' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-300'"
          class="px-4 py-2 rounded-md transition-colors duration-200"
        >
          Просмотр
        </button>
        <button
          @click="$emit('switch-mode', 'edit')"
          :class="mode === 'edit' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-300'"
          class="px-4 py-2 rounded-md transition-colors duration-200"
        >
          Редактирование
        </button>
      </div>

      <!-- Task details -->
      <div class="bg-white dark:bg-gray-800 rounded-lg space-y-6">
        <!-- Header -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ task.name }}</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">ID: #{{ task.id }}</p>
        </div>
        
        <!-- Description -->
        <div v-if="task.description">
          <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Описание</h3>
          <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ task.description }}</p>
        </div>
        
        <!-- Task info grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <!-- Project -->
            <div>
              <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Проект</span>
              <p class="text-gray-900 dark:text-white">{{ task.project ? task.project.name : '—' }}</p>
            </div>
            
            <!-- Status -->
            <div>
              <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Статус</span>
              <div class="mt-1">
                <span 
                  v-if="task.status"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                  :style="`background-color: ${task.status.color}20; color: ${task.status.color};`"
                >
                  {{ task.status.name }}
                </span>
                <span v-else class="text-gray-500">—</span>
              </div>
            </div>
            
            <!-- Priority -->
            <div>
              <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Приоритет</span>
              <div class="mt-1">
                <span :class="`priority-${task.priority}`" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ getPriorityLabel(task.priority) }}
                </span>
              </div>
            </div>
            
            <!-- Size -->
            <div v-if="task.size">
              <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Размер</span>
              <p class="text-gray-900 dark:text-white">{{ task.size.code }} - {{ task.size.name }}</p>
            </div>
          </div>
          
          <div class="space-y-4">
            <!-- Estimated hours -->
            <div v-if="task.estimated_hours">
              <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Оценка времени</span>
              <p class="text-gray-900 dark:text-white">{{ task.estimated_hours }} часов</p>
            </div>
            
            <!-- Due date -->
            <div v-if="task.due_date">
              <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Срок выполнения</span>
              <p class="text-gray-900 dark:text-white">{{ formatDate(task.due_date) }}</p>
            </div>
            
            <!-- Created date -->
            <div>
              <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Создана</span>
              <p class="text-gray-900 dark:text-white">{{ formatDate(task.created_at) }}</p>
            </div>
            
            <!-- Author -->
            <div>
              <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Автор</span>
              <p class="text-gray-900 dark:text-white">{{ task.created_by ? task.created_by.name : '—' }}</p>
            </div>
          </div>
        </div>
        
        <!-- Assignees -->
        <div v-if="task.assignees && task.assignees.length > 0">
          <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Исполнители</span>
          <div class="mt-2 flex flex-wrap gap-2">
            <span 
              v-for="assignee in task.assignees" 
              :key="assignee.id"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200"
            >
              {{ assignee.name }}
            </span>
          </div>
        </div>

        <!-- Time logs (if any) -->
        <div v-if="task.time_logs && task.time_logs.length > 0">
          <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">История трудозатрат</h3>
          <div class="space-y-2">
            <div 
              v-for="log in task.time_logs" 
              :key="log.id"
              class="flex justify-between items-center text-sm p-2 bg-gray-50 dark:bg-gray-700 rounded"
            >
              <span class="text-gray-900 dark:text-white">{{ log.user.name }}</span>
              <span class="text-gray-600 dark:text-gray-300">{{ log.hours }} ч.</span>
              <span class="text-gray-500 dark:text-gray-400">{{ formatDate(log.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-between w-full">
        <button
          v-if="task && mode === 'view'"
          @click="$emit('switch-mode', 'edit')"
          class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
          Редактировать
        </button>
        <div v-else></div>
        
        <button
          @click="$emit('close')"
          class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
          Закрыть
        </button>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import { computed } from 'vue'
import Modal from './Modal.vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  task: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  },
  mode: {
    type: String,
    default: 'view',
    validator: (value) => ['view', 'edit'].includes(value)
  }
})

defineEmits(['close', 'switch-mode'])

// Helper methods
const formatDate = (dateString) => {
  if (!dateString) return '—'
  return new Date(dateString).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getPriorityLabel = (priority) => {
  const labels = {
    low: 'Низкий',
    normal: 'Обычный', 
    high: 'Высокий',
    urgent: 'Срочный'
  }
  return labels[priority] || priority
}
</script>

<style scoped>
.priority-urgent {
  background-color: rgb(254, 226, 226);
  color: rgb(153, 27, 27);
}
.priority-high {
  background-color: rgb(255, 237, 213);
  color: rgb(154, 52, 18);
}
.priority-normal {
  background-color: rgb(219, 234, 254);
  color: rgb(30, 64, 175);
}
.priority-low {
  background-color: rgb(220, 252, 231);
  color: rgb(22, 101, 52);
}
</style>