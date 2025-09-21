<template>
  <!-- Global Error Notifications -->
  <div class="fixed top-4 right-4 z-50 space-y-2" v-if="notifications.length > 0">
    <a-alert
      v-for="notification in notifications"
      :key="notification.id"
      :message="notification.title"
      :description="notification.message"
      :type="notification.type"
      show-icon
      closable
      @close="removeNotification(notification.id)"
      class="max-w-md shadow-lg"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { notification } from 'ant-design-vue'

// Reactive state
const notifications = ref([])
let notificationId = 0

// Methods
const addNotification = (type, title, message, duration = 5000) => {
  const id = ++notificationId
  const newNotification = { id, type, title, message }
  
  notifications.value.push(newNotification)
  
  // Auto remove after duration
  if (duration > 0) {
    setTimeout(() => {
      removeNotification(id)
    }, duration)
  }
  
  return id
}

const removeNotification = (id) => {
  const index = notifications.value.findIndex(n => n.id === id)
  if (index > -1) {
    notifications.value.splice(index, 1)
  }
}

const clearAllNotifications = () => {
  notifications.value = []
}

// Error handlers
const handleInertiaError = (event) => {
  const { detail } = event
  
  if (detail.status === 500) {
    addNotification(
      'error',
      'Ошибка сервера',
      'Произошла внутренняя ошибка сервера. Попробуйте позже.',
      8000
    )
  } else if (detail.status === 403) {
    addNotification(
      'warning',
      'Доступ запрещен',
      'У вас нет прав для выполнения этого действия.',
      6000
    )
  } else if (detail.status === 404) {
    addNotification(
      'warning',
      'Страница не найдена',
      'Запрашиваемая страница не существует.',
      6000
    )
  } else if (detail.status === 419) {
    addNotification(
      'warning',
      'Сессия истекла',
      'Ваша сессия истекла. Перезагрузите страницу.',
      0 // Don't auto-hide
    )
  } else if (detail.status >= 400) {
    addNotification(
      'error',
      'Ошибка запроса',
      detail.message || 'Произошла ошибка при обработке запроса.',
      6000
    )
  }
}

const handleInertiaStart = () => {
  // Clear previous errors when new request starts
  clearAllNotifications()
}

const handleInertiaFinish = (event) => {
  // Handle successful operations with flash messages
  const page = event.detail.visit.completed ? event.detail.visit.page : null
  
  if (page?.props?.flash) {
    const flash = page.props.flash
    
    if (flash.success) {
      notification.success({
        message: 'Успешно',
        description: flash.success,
        duration: 4
      })
    }
    
    if (flash.error) {
      addNotification(
        'error',
        'Ошибка',
        flash.error,
        6000
      )
    }
    
    if (flash.warning) {
      addNotification(
        'warning',
        'Внимание',
        flash.warning,
        5000
      )
    }
    
    if (flash.info || flash.message) {
      notification.info({
        message: 'Информация',
        description: flash.info || flash.message,
        duration: 4
      })
    }
  }
}

// Global error handler for uncaught errors
const handleGlobalError = (event) => {
  console.error('Global error:', event.error)
  
  addNotification(
    'error',
    'Неожиданная ошибка',
    'Произошла неожиданная ошибка. Проверьте консоль браузера.',
    8000
  )
}

const handleUnhandledRejection = (event) => {
  console.error('Unhandled promise rejection:', event.reason)
  
  addNotification(
    'error',
    'Ошибка приложения',
    'Произошла ошибка при выполнении операции.',
    6000
  )
}

// Lifecycle
onMounted(() => {
  // Listen for Inertia events
  document.addEventListener('inertia:error', handleInertiaError)
  document.addEventListener('inertia:start', handleInertiaStart)
  document.addEventListener('inertia:finish', handleInertiaFinish)
  
  // Listen for global errors
  window.addEventListener('error', handleGlobalError)
  window.addEventListener('unhandledrejection', handleUnhandledRejection)
  
  console.log('GlobalErrorHandler mounted')
})

onUnmounted(() => {
  // Cleanup event listeners
  document.removeEventListener('inertia:error', handleInertiaError)
  document.removeEventListener('inertia:start', handleInertiaStart)
  document.removeEventListener('inertia:finish', handleInertiaFinish)
  
  window.removeEventListener('error', handleGlobalError)
  window.removeEventListener('unhandledrejection', handleUnhandledRejection)
})

// Expose methods for manual use
defineExpose({
  addNotification,
  removeNotification,
  clearAllNotifications
})
</script>