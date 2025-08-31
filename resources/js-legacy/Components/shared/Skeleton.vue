<template>
  <div :class="skeletonClasses" :style="customStyle">
    <div class="animate-pulse bg-gradient-to-r from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 w-full h-full rounded"></div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  width: {
    type: [String, Number],
    default: '100%'
  },
  height: {
    type: [String, Number],
    default: '20px'
  },
  variant: {
    type: String,
    default: 'text',
    validator: (value) => ['text', 'circular', 'rectangular'].includes(value)
  },
  rounded: {
    type: String,
    default: 'md',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'full'].includes(value)
  }
})

const skeletonClasses = computed(() => {
  const classes = ['inline-block']
  
  // Округления
  const roundedClasses = {
    none: 'rounded-none',
    sm: 'rounded-sm',
    md: 'rounded-md', 
    lg: 'rounded-lg',
    full: 'rounded-full'
  }
  
  if (props.variant === 'circular') {
    classes.push('rounded-full')
  } else {
    classes.push(roundedClasses[props.rounded])
  }
  
  return classes.join(' ')
})

const customStyle = computed(() => {
  const style = {}
  
  // Ширина
  if (typeof props.width === 'number') {
    style.width = `${props.width}px`
  } else {
    style.width = props.width
  }
  
  // Высота
  if (typeof props.height === 'number') {
    style.height = `${props.height}px`
  } else {
    style.height = props.height
  }
  
  // Для круглых скелетонов делаем квадратными
  if (props.variant === 'circular') {
    const size = typeof props.width === 'number' ? `${props.width}px` : props.width
    style.width = size
    style.height = size
  }
  
  return style
})
</script>