<template>
  <div>
    <!-- Debug info -->
    <div class="mb-4 p-4 bg-blue-50 rounded">
      <h3 class="font-bold">SimpleTaskManager загружен</h3>
      <p>TaskModal состояние: {{ modals.task.isOpen ? 'открыто' : 'закрыто' }}</p>
      <p>Кликов: {{ clickCount }}</p>
    </div>

    <!-- Кнопка создания задачи -->
    <div class="flex justify-between items-center mb-6">
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Упрощенный TaskManager
      </h2>
      <button
        @click="openCreateModal"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Создать задачу ({{ clickCount }})
      </button>
    </div>

    <!-- TaskModal -->
    <TaskModal
      :is-open="modals.task.isOpen"
      :task="modals.task.data"
      :projects="projects"
      :statuses="statuses"
      :sizes="sizes"
      :users="users"
      @close="closeTaskModal"
      @submit="handleTaskSubmit"
    />
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import TaskModal from './TaskModal.vue'

console.log('SimpleTaskManager загружается...')

const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({})
  }
})

const clickCount = ref(0)

const modals = reactive({
  task: {
    isOpen: false,
    data: null
  }
})

// Static data из props
const projects = ref(props.initialData.projects || [])
const statuses = ref(props.initialData.statuses || [])
const sizes = ref(props.initialData.sizes || [])
const users = ref(props.initialData.users || [])

const openCreateModal = () => {
  clickCount.value++
  console.log('openCreateModal вызван, счётчик:', clickCount.value)
  modals.task.data = null
  modals.task.isOpen = true
  console.log('Модалка должна открыться:', modals.task.isOpen)
}

const closeTaskModal = () => {
  console.log('closeTaskModal вызван')
  modals.task.isOpen = false
  modals.task.data = null
}

const handleTaskSubmit = ({ data, isEdit, taskId }) => {
  console.log('handleTaskSubmit вызван:', { data, isEdit, taskId })
  // Заглушка - просто закрываем модалку
  closeTaskModal()
}

console.log('SimpleTaskManager готов')
console.log('Props:', props.initialData)
</script>