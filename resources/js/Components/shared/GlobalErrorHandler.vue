<template>
  <!-- Компонент для отображения глобальных ошибок -->
  <div>
    <!-- Здесь будут отображаться глобальные уведомления об ошибках -->
  </div>
</template>

<script setup>
import { onMounted } from 'vue'

onMounted(() => {
  // TODO: Настроить глобальную обработку ошибок Inertia
  console.log('GlobalErrorHandler mounted')
})
</script>