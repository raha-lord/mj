<template>
  <div :class="containerClass">
    <a-spin :size="size" :spinning="true">
      <template #indicator v-if="customIcon">
        <component :is="customIcon" :style="{ fontSize: iconSize }" spin />
      </template>
    </a-spin>
    
    <div v-if="message" :class="messageClass">
      {{ message }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { LoadingOutlined } from '@ant-design/icons-vue'

// Props
const props = defineProps({
  size: {
    type: String,
    default: 'default', // 'small' | 'default' | 'large'
    validator: (value) => ['small', 'default', 'large'].includes(value)
  },
  message: {
    type: String,
    default: null
  },
  centered: {
    type: Boolean,
    default: true
  },
  fullHeight: {
    type: Boolean,
    default: false
  },
  customIcon: {
    type: Object,
    default: null
  },
  theme: {
    type: String,
    default: 'default', // 'default' | 'overlay' | 'minimal'
    validator: (value) => ['default', 'overlay', 'minimal'].includes(value)
  }
})

// Computed
const containerClass = computed(() => {
  const classes = ['loading-spinner']
  
  if (props.centered) {
    classes.push('flex', 'justify-center', 'items-center')
  }
  
  if (props.fullHeight) {
    classes.push('min-h-screen')
  } else {
    classes.push('py-8')
  }
  
  if (props.theme === 'overlay') {
    classes.push('fixed', 'inset-0', 'bg-white', 'bg-opacity-75', 'z-50')
  } else if (props.theme === 'minimal') {
    classes.push('p-4')
  }
  
  return classes
})

const messageClass = computed(() => {
  const classes = ['mt-4', 'text-gray-600']
  
  if (props.centered) {
    classes.push('text-center')
  }
  
  if (props.size === 'large') {
    classes.push('text-lg')
  } else if (props.size === 'small') {
    classes.push('text-sm')
  }
  
  return classes
})

const iconSize = computed(() => {
  const sizes = {
    small: '16px',
    default: '24px',
    large: '32px'
  }
  return sizes[props.size]
})
</script>

<style scoped>
.loading-spinner {
  transition: all 0.3s ease;
}

.loading-spinner.overlay {
  backdrop-filter: blur(2px);
}
</style>