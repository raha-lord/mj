<template>
  <div class="p-6">
    <div class="bg-green-50 border border-green-200 p-4 rounded mb-4">
      <h3 class="font-bold text-green-800">✅ Vue компонент работает!</h3>
      <p class="text-green-700">Если вы видите это сообщение, Vue успешно загрузился.</p>
    </div>
    
    <button
      @click="count++"
      class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded mr-4"
    >
      Кликов: {{ count }}
    </button>
    
    <button
      @click="showDialog = true"
      class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded"
    >
      Открыть Headless UI Dialog
    </button>

    <!-- Headless UI Dialog -->
    <Dialog 
      :open="showDialog" 
      @close="showDialog = false"
      class="relative z-50"
    >
      <!-- Overlay -->
      <div class="fixed inset-0 bg-black/30" aria-hidden="true" />
      
      <!-- Container -->
      <div class="fixed inset-0 flex w-screen items-center justify-center p-4">
        <DialogPanel class="mx-auto max-w-md bg-white rounded-lg p-6 shadow-xl">
          <DialogTitle class="text-lg font-bold mb-4">
            🎉 Headless UI работает!
          </DialogTitle>
          <p class="mb-4 text-gray-600">
            Dialog компонент успешно открылся. Это означает, что Headless UI правильно установлен и работает.
          </p>
          <div class="flex space-x-2">
            <button 
              @click="showDialog = false"
              class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded"
            >
              Закрыть
            </button>
            <button 
              @click="count++"
              class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded"
            >
              Счётчик: {{ count }}
            </button>
          </div>
        </DialogPanel>
      </div>
    </Dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'

console.log('MinimalTest компонент загружается...')

const count = ref(0)
const showDialog = ref(false)

console.log('MinimalTest компонент готов')
</script>