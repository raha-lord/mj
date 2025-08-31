<template>
  <div class="p-6">
    <div class="bg-green-50 border border-green-200 p-4 rounded mb-4">
      <h3 class="font-bold text-green-800">Тест нашего Modal компонента</h3>
      <p class="text-green-700">Состояние модалки: {{ isModalOpen ? 'открыто' : 'закрыто' }}</p>
    </div>
    
    <button
      @click="isModalOpen = true"
      class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded mb-4"
    >
      Открыть наш Modal
    </button>

    <!-- Наш Modal компонент -->
    <Modal
      :is-open="isModalOpen"
      title="Тест нашего Modal"
      size="md"
      :show-footer="true"
      :show-cancel-button="true"
      :show-confirm-button="true"
      confirm-text="OK"
      cancel-text="Отмена"
      @close="isModalOpen = false"
      @confirm="handleConfirm"
    >
      <p>Если вы видите этот текст, наш Modal компонент работает правильно!</p>
    </Modal>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Modal from './Modal.vue'

console.log('TestModal компонент загружается...')

const isModalOpen = ref(false)

const handleConfirm = () => {
  console.log('Confirm clicked!')
  isModalOpen.value = false
}

console.log('TestModal компонент готов')
</script>