<template>
  <div class="p-6">
    <div class="bg-green-50 border border-green-200 p-4 rounded mb-4">
      <h3 class="font-bold text-green-800">Тест TaskModal компонента</h3>
      <p class="text-green-700">Состояние TaskModal: {{ isModalOpen ? 'открыто' : 'закрыто' }}</p>
    </div>
    
    <button
      @click="isModalOpen = true"
      class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded mb-4"
    >
      Открыть TaskModal
    </button>

    <!-- TaskModal компонент -->
    <TaskModal
      :is-open="isModalOpen"
      :task="null"
      :projects="testProjects"
      :statuses="testStatuses"
      :sizes="testSizes"
      :users="testUsers"
      @close="handleClose"
      @submit="handleSubmit"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import TaskModal from './TaskModal.vue'

console.log('TestTaskModal компонент загружается...')

const isModalOpen = ref(false)

// Тестовые данные
const testProjects = ref([
  { id: 1, name: 'Тестовый проект' }
])

const testStatuses = ref([
  { id: 1, name: 'Новая' },
  { id: 2, name: 'В работе' }
])

const testSizes = ref([
  { id: 1, code: 'S', name: 'Маленькая' },
  { id: 2, code: 'M', name: 'Средняя' }
])

const testUsers = ref([
  { id: 1, name: 'Тестовый пользователь' }
])

const handleClose = () => {
  console.log('TaskModal закрыт')
  isModalOpen.value = false
}

const handleSubmit = (data) => {
  console.log('TaskModal отправлен:', data)
  isModalOpen.value = false
}

console.log('TestTaskModal компонент готов')
</script>