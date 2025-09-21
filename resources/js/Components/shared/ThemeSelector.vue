<template>
  <div class="theme-selector">
    <a-button 
      @click="toggleTheme"
      size="small"
      :icon="currentThemeData?.icon"
      :title="`Переключить на ${currentTheme === 'light' ? 'темную' : 'светлую'} тему`"
      class="theme-toggle-btn"
    >
      {{ currentThemeData?.label }}
    </a-button>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const currentTheme = ref('light')
const availableThemes = ref([])

const currentThemeData = computed(() => {
  return availableThemes.value.find(theme => theme.value === currentTheme.value)
})

const toggleTheme = () => {
  const newTheme = window.themeManager.toggleTheme()
  
  // Эмитим событие для родительского компонента
  emit('theme-changed', newTheme)
}

const emit = defineEmits(['theme-changed'])

onMounted(() => {
  // Получаем доступные темы и текущую тему
  availableThemes.value = window.themeManager.getAvailableThemes()
  currentTheme.value = window.themeManager.getTheme()
  
  // Слушаем изменения темы из других источников
  window.addEventListener('theme-changed', (event) => {
    currentTheme.value = event.detail.theme
  })
})
</script>