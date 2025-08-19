<template>
  <div>
    <!-- Debug info -->
    <div class="mb-4 p-4 bg-blue-50 rounded">
      <p>Vue компонент загружен: ✅</p>
      <p>Modal состояние: {{ isModalOpen ? 'открыто' : 'закрыто' }}</p>
    </div>
    
    <!-- Test button -->
    <button
      @click="toggleModal"
      class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
    >
      {{ isModalOpen ? 'Закрыть' : 'Открыть' }} тестовую модалку
    </button>

    <!-- Simple modal -->
    <Dialog 
      :open="isModalOpen" 
      @close="closeModal"
      class="relative z-50"
    >
      <div class="fixed inset-0 bg-black/30" aria-hidden="true" />
      
      <div class="fixed inset-0 flex w-screen items-center justify-center p-4">
        <DialogPanel class="mx-auto max-w-md bg-white rounded p-6">
          <DialogTitle class="text-lg font-bold mb-4">Тестовая модалка</DialogTitle>
          <p class="mb-4">Headless UI Dialog работает! 🎉</p>
          <button 
            @click="closeModal"
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
import { ref } from 'vue'
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'

const isModalOpen = ref(false)

const toggleModal = () => {
  isModalOpen.value = !isModalOpen.value
  console.log('Modal toggled:', isModalOpen.value)
}

const closeModal = () => {
  isModalOpen.value = false
  console.log('Modal closed')
}
</script>