<template>
  <Dialog 
    :open="isOpen" 
    @close="closeModal"
    class="relative z-50"
  >
    <!-- Background overlay -->
    <div class="fixed inset-0 bg-black/30" aria-hidden="true" />
    
    <!-- Full-screen container to center the panel -->
    <div class="fixed inset-0 flex w-screen items-center justify-center p-4">
      <DialogPanel 
        :class="panelClasses"
        class="mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all"
      >
        <!-- Header -->
        <div 
          v-if="showHeader"
          class="flex justify-between items-center px-6 py-4 border-b border-gray-200 dark:border-gray-700"
        >
          <DialogTitle 
            v-if="title"
            class="text-lg font-semibold text-gray-900 dark:text-white"
          >
            {{ title }}
          </DialogTitle>
          <slot name="title" v-else />
          
          <button
            v-if="showCloseButton"
            @click="closeModal"
            type="button"
            class="rounded-md bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          >
            <span class="sr-only">Закрыть</span>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div :class="contentClasses">
          <slot />
        </div>

        <!-- Footer -->
        <div 
          v-if="showFooter"
          :class="footerClasses"
        >
          <slot name="footer">
            <div class="flex justify-end space-x-3">
              <button
                v-if="showCancelButton"
                @click="closeModal"
                type="button"
                class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              >
                {{ cancelText }}
              </button>
              <button
                v-if="showConfirmButton"
                @click="$emit('confirm')"
                type="button"
                :class="confirmButtonClass"
                class="inline-flex justify-center rounded-md border border-transparent px-4 py-2 text-sm font-medium text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2"
              >
                {{ confirmText }}
              </button>
            </div>
          </slot>
        </div>
      </DialogPanel>
    </div>
  </Dialog>
</template>

<script setup>
import { computed } from 'vue'
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'

const props = defineProps({
  // Основные
  isOpen: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  
  // Размеры
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl', 'full'].includes(value)
  },
  maxHeight: {
    type: String,
    default: '90vh'
  },
  
  // Видимость элементов
  showHeader: {
    type: Boolean,
    default: true
  },
  showFooter: {
    type: Boolean,
    default: false
  },
  showCloseButton: {
    type: Boolean,
    default: true
  },
  showCancelButton: {
    type: Boolean,
    default: false
  },
  showConfirmButton: {
    type: Boolean,
    default: false
  },
  
  // Тексты кнопок
  cancelText: {
    type: String,
    default: 'Отмена'
  },
  confirmText: {
    type: String,
    default: 'Подтвердить'
  },
  
  // Стили кнопки подтверждения
  confirmType: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'danger', 'success', 'warning'].includes(value)
  },
  
  // Отступы контента
  padding: {
    type: String,
    default: 'default'
  },
  
  // Закрытие по ESC
  closeOnEscape: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['close', 'confirm'])

// Computed свойства для размеров
const panelClasses = computed(() => {
  const sizeClasses = {
    xs: 'max-w-xs w-full',
    sm: 'max-w-sm w-full', 
    md: 'max-w-md w-full',
    lg: 'max-w-lg w-full',
    xl: 'max-w-xl w-full',
    '2xl': 'max-w-2xl w-full',
    '3xl': 'max-w-3xl w-full',
    full: 'max-w-7xl w-full'
  }
  
  return [
    sizeClasses[props.size],
    `max-h-[${props.maxHeight}]`,
    'overflow-y-auto'
  ]
})

const contentClasses = computed(() => {
  const paddingClasses = {
    none: '',
    sm: 'p-4',
    default: 'px-6 py-4',
    lg: 'px-8 py-6',
    xl: 'px-10 py-8'
  }
  
  return paddingClasses[props.padding] || paddingClasses.default
})

const footerClasses = computed(() => {
  return [
    'px-6 py-4 border-t border-gray-200 dark:border-gray-700',
    'bg-gray-50 dark:bg-gray-700/50'
  ]
})

const confirmButtonClass = computed(() => {
  const typeClasses = {
    primary: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
    danger: 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
    success: 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
    warning: 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500'
  }
  
  return typeClasses[props.confirmType] || typeClasses.primary
})

// Методы
const closeModal = () => {
  emit('close')
}
</script>