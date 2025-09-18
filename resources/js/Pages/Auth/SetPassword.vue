<template>
  <AuthLayout title="Установка пароля">
    <template #header>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        Установка пароля
      </h2>
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
        Установите пароль для вашего аккаунта
      </p>
    </template>

    <!-- User Info -->
    <div v-if="user" class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
      <div class="flex items-center">
        <UserOutlined class="text-blue-600 mr-3" />
        <div>
          <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
          <p class="text-sm text-gray-600 dark:text-gray-400">{{ user.email }}</p>
        </div>
      </div>
    </div>

    <!-- Password Setup Form -->
    <a-form
      :model="form"
      @finish="handleSubmit"
      layout="vertical"
      :disabled="form.processing"
    >
      <!-- Password Field -->
      <a-form-item 
        label="Новый пароль"
        v-bind="getFieldError('password')"
      >
        <a-input-password
          v-model:value="form.password"
          placeholder="Введите новый пароль"
          size="large"
          @input="clearFieldError('password')"
        >
          <template #prefix>
            <LockOutlined />
          </template>
        </a-input-password>
        <div class="mt-2 text-xs text-gray-500">
          Пароль должен содержать минимум 8 символов
        </div>
      </a-form-item>

      <!-- Password Confirmation Field -->
      <a-form-item 
        label="Подтверждение пароля"
        v-bind="getFieldError('password_confirmation')"
      >
        <a-input-password
          v-model:value="form.password_confirmation"
          placeholder="Повторите новый пароль"
          size="large"
          @input="clearFieldError('password_confirmation')"
        >
          <template #prefix>
            <LockOutlined />
          </template>
        </a-input-password>
      </a-form-item>

      <!-- Password Strength Indicator -->
      <div v-if="form.password" class="mb-4">
        <div class="flex items-center justify-between mb-1">
          <span class="text-sm text-gray-600 dark:text-gray-400">Надежность пароля:</span>
          <span class="text-sm font-medium" :class="passwordStrengthColor">
            {{ passwordStrengthText }}
          </span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
          <div 
            class="h-2 rounded-full transition-all duration-300"
            :class="passwordStrengthBarColor"
            :style="{ width: passwordStrengthPercentage + '%' }"
          ></div>
        </div>
      </div>

      <!-- General Error -->
      <a-alert
        v-if="hasErrors && !getFieldError('password').validateStatus && !getFieldError('password_confirmation').validateStatus"
        :message="getFirstError"
        type="error"
        class="mb-4"
        show-icon
      />

      <!-- Submit Button -->
      <a-form-item>
        <a-button
          type="primary"
          html-type="submit"
          size="large"
          block
          :loading="form.processing"
          :disabled="!canSubmit"
        >
          <template v-if="!form.processing">
            <SafetyOutlined class="mr-2" />
            Установить пароль
          </template>
          <template v-else>
            Установка пароля...
          </template>
        </a-button>
      </a-form-item>
    </a-form>

    <!-- Help Text -->
    <div class="mt-6 text-center">
      <p class="text-sm text-gray-600 dark:text-gray-400">
        После установки пароля вы будете перенаправлены в систему
      </p>
    </div>
  </AuthLayout>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Layouts/AuthLayout.vue'
import { useFormErrors } from '@/composables/ui/useFormErrors'
import {
  UserOutlined,
  LockOutlined,
  SafetyOutlined
} from '@ant-design/icons-vue'
import { message } from 'ant-design-vue'

// Props
const props = defineProps({
  user: {
    type: Object,
    default: null
  }
})

// Form setup
const form = useForm({
  password: '',
  password_confirmation: ''
})

const { getFieldError, hasErrors, clearFieldError, getFirstError } = useFormErrors(form)

// Password strength calculation
const passwordStrength = computed(() => {
  const password = form.password
  if (!password) return 0
  
  let score = 0
  
  // Length
  if (password.length >= 8) score += 1
  if (password.length >= 12) score += 1
  
  // Complexity
  if (/[a-z]/.test(password)) score += 1
  if (/[A-Z]/.test(password)) score += 1
  if (/[0-9]/.test(password)) score += 1
  if (/[^A-Za-z0-9]/.test(password)) score += 1
  
  return Math.min(score, 4)
})

const passwordStrengthPercentage = computed(() => {
  return (passwordStrength.value / 4) * 100
})

const passwordStrengthText = computed(() => {
  const texts = ['Очень слабый', 'Слабый', 'Средний', 'Хороший', 'Отличный']
  return texts[passwordStrength.value] || 'Очень слабый'
})

const passwordStrengthColor = computed(() => {
  const colors = [
    'text-red-600',
    'text-red-500', 
    'text-yellow-500',
    'text-blue-500',
    'text-green-500'
  ]
  return colors[passwordStrength.value] || 'text-red-600'
})

const passwordStrengthBarColor = computed(() => {
  const colors = [
    'bg-red-600',
    'bg-red-500',
    'bg-yellow-500', 
    'bg-blue-500',
    'bg-green-500'
  ]
  return colors[passwordStrength.value] || 'bg-red-600'
})

// Form validation
const canSubmit = computed(() => {
  return form.password.length >= 8 && 
         form.password === form.password_confirmation &&
         !form.processing
})

// Methods
const handleSubmit = () => {
  if (!canSubmit.value) {
    if (form.password.length < 8) {
      message.error('Пароль должен содержать минимум 8 символов')
      return
    }
    if (form.password !== form.password_confirmation) {
      message.error('Пароли не совпадают')
      return
    }
  }

  form.post(route('set-password.store'), {
    onSuccess: () => {
      message.success('Пароль успешно установлен!')
    },
    onError: (errors) => {
      if (errors.password) {
        message.error(errors.password)
      } else {
        message.error('Произошла ошибка при установке пароля')
      }
    }
  })
}

// Clear confirmation error when password changes
watch(() => form.password, () => {
  if (form.password_confirmation) {
    clearFieldError('password_confirmation')
  }
})

// Clear confirmation error when confirmation changes
watch(() => form.password_confirmation, () => {
  clearFieldError('password_confirmation')
})
</script>

<style scoped>
/* Дополнительные стили для индикатора надежности */
.password-strength-bar {
  transition: all 0.3s ease;
}
</style>