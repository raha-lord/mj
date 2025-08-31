<template>
  <div>
    <!-- Debug info -->
    <div class="mb-4 p-4 bg-blue-50 rounded">
      <h3 class="font-bold">Debug Info:</h3>
      <p>Vue компонент загружен: ✅</p>
      <p>TaskModal состояние: {{ modals.task.isOpen ? 'открыто' : 'закрыто' }}</p>
      <p>Клик по кнопке сработал: {{ clickCount }} раз</p>
    </div>
    
    <!-- Test button -->
    <button
      @click="openCreateModal"
      class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center mb-6"
    >
      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
      </svg>
      Открыть модалку (клики: {{ clickCount }})
    </button>

    <!-- Simple TaskModal test -->
    <TaskModal
      :is-open="modals.task.isOpen"
      :task="modals.task.data"
      :projects="[]"
      :statuses="[{id: 1, name: 'Новая'}]"
      :sizes="[]"
      :users="[]"
      @close="closeTaskModal"
      @submit="handleTaskSubmit"
    />

    <!-- Raw Headless UI test -->
    <button
      @click="rawModalOpen = true"
      class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg mr-4"
    >
      Тест сырого Dialog
    </button>

    <Dialog 
      :open="rawModalOpen" 
      @close="rawModalOpen = false"
      class="relative z-50"
    >
      <div class="fixed inset-0 bg-black/30" aria-hidden="true" />
      
      <div class="fixed inset-0 flex w-screen items-center justify-center p-4">
        <DialogPanel class="mx-auto max-w-md bg-white rounded p-6">
          <DialogTitle class="text-lg font-bold mb-4">Сырой Dialog</DialogTitle>
          <p class="mb-4">Если это видно - Headless UI работает!</p>
          <button 
            @click="rawModalOpen = false"
            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded"
          >
            Закрыть
          </button>
        </DialogPanel>
      </div>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'
import TaskModal from './TaskModal.vue'

const clickCount = ref(0)
const rawModalOpen = ref(false)

const modals = reactive({
  task: {
    isOpen: false,
    data: null
  }
})

const openCreateModal = () => {
  clickCount.value++
  console.log('openCreateModal called, count:', clickCount.value)
  modals.task.data = null
  modals.task.isOpen = true
  console.log('Modal state:', modals.task.isOpen)
}

const closeTaskModal = () => {
  console.log('closeTaskModal called')
  modals.task.isOpen = false
  modals.task.data = null
}

const handleTaskSubmit = (data) => {
  console.log('handleTaskSubmit called:', data)
  closeTaskModal()
}
</script>