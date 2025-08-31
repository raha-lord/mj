<template>
  <div :class="spinnerClasses" role="status" :aria-label="ariaLabel">
    <svg
      class="animate-spin"
      :class="sizeClasses"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle
        class="opacity-25"
        cx="12"
        cy="12"
        r="10"
        stroke="currentColor"
        stroke-width="4"
      />
      <path
        class="opacity-75"
        fill="currentColor"
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
      />
    </svg>
    <span v-if="showText" :class="textClasses" class="ml-2">
      {{ text }}
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  color: {
    type: String,
    default: 'blue',
    validator: (value) => ['blue', 'gray', 'white', 'green', 'red'].includes(value)
  },
  text: {
    type: String,
    default: 'Загрузка...'
  },
  showText: {
    type: Boolean,
    default: false
  }
})

// Размеры спиннера
const sizeClasses = computed(() => {
  const sizes = {
    xs: 'h-3 w-3',
    sm: 'h-4 w-4', 
    md: 'h-6 w-6',
    lg: 'h-8 w-8',
    xl: 'h-12 w-12'
  }
  return sizes[props.size]
})

// Цвета спиннера
const colorClasses = {
  blue: 'text-blue-600',
  gray: 'text-gray-600', 
  white: 'text-white',
  green: 'text-green-600',
  red: 'text-red-600'
}

// Размеры текста
const textSizes = {
  xs: 'text-xs',
  sm: 'text-sm',
  md: 'text-sm', 
  lg: 'text-base',
  xl: 'text-lg'
}

// Классы контейнера
const spinnerClasses = computed(() => {
  return `inline-flex items-center ${colorClasses[props.color]}`
})

// Классы текста
const textClasses = computed(() => {
  return `${textSizes[props.size]} ${colorClasses[props.color]}`
})

// Aria label для доступности
const ariaLabel = computed(() => {
  return props.showText ? props.text : 'Загрузка'
})
</script>