<template>
  <div class="theme-selector">
    <a-select 
      v-model:value="currentTheme" 
      @change="handleThemeChange"
      :style="{ width: '140px' }"
      size="small"
    >
      <a-select-option 
        v-for="theme in availableThemes" 
        :key="theme.value" 
        :value="theme.value"
      >
        <div class="flex items-center gap-2">
          <div 
            class="w-3 h-3 rounded-full border" 
            :style="{ backgroundColor: theme.color }"
          ></div>
          {{ theme.label }}
        </div>
      </a-select-option>
    </a-select>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const currentTheme = ref('green');
const availableThemes = ref([]);

const handleThemeChange = (themeName) => {
  window.themeManager.setTheme(themeName);
  
  // Эмитим событие для родительского компонента
  emit('theme-changed', themeName);
};

const emit = defineEmits(['theme-changed']);

onMounted(() => {
  // Получаем доступные темы и текущую тему
  availableThemes.value = window.themeManager.getAvailableThemes();
  currentTheme.value = window.themeManager.getTheme();
  
  // Слушаем изменения темы из других источников
  window.addEventListener('theme-changed', (event) => {
    currentTheme.value = event.detail.theme;
  });
});
</script>

<style scoped>
.theme-selector {
  display: inline-block;
}
</style>