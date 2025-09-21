<template>
  <button
    :class="buttonClasses"
    :disabled="loading || disabled"
    v-bind="$attrs"
    @click="$emit('click', $event)"
  >
    <LoadingSpinner v-if="loading" :size="spinnerSize" class="mr-2" />
    <slot v-else-if="$slots.icon" name="icon" />
    <slot />
  </button>
</template>

<script setup>
import { computed } from 'vue'
import LoadingSpinner from './LoadingSpinner.vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'danger', 'success', 'outline'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  loading: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

defineEmits(['click'])

// Базовые классы
const baseClasses = 'inline-flex items-center justify-center font-medium rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2'

// Размеры кнопок
const sizeClasses = {
  sm: 'px-3 py-1.5 text-sm',
  md: 'px-4 py-2 text-sm',
  lg: 'px-6 py-3 text-base'
}

// Варианты кнопок
const variantClasses = {
  primary: 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500 disabled:bg-green-300',
  secondary: 'bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500 disabled:bg-gray-300',
  danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 disabled:bg-red-300',
  success: 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500 disabled:bg-green-300',
  outline: 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:ring-green-500 disabled:bg-gray-50 disabled:text-gray-400'
}

// Размер спиннера в зависимости от размера кнопки
const spinnerSize = computed(() => {
  const sizes = { sm: 'xs', md: 'sm', lg: 'sm' }
  return sizes[props.size]
})

// Итоговые классы кнопки
const buttonClasses = computed(() => {
  const classes = [
    baseClasses,
    sizeClasses[props.size],
    variantClasses[props.variant]
  ]
  
  if (props.loading || props.disabled) {
    classes.push('cursor-not-allowed opacity-75')
  }
  
  return classes.join(' ')
})
</script>