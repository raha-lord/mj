<template>
  <AuthLayout title="Восстановление пароля">
    <template #header>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        Забыли пароль?
      </h2>
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
        Введите ваш email адрес и мы отправим ссылку для восстановления пароля
      </p>
    </template>

    <!-- Success Message -->
    <a-alert
      v-if="status"
      :message="status"
      type="success"
      show-icon
      class="mb-4"
    />

    <!-- Forgot Password Form -->
    <a-form
      :model="form"
      @finish="handleSubmit"
      layout="vertical"
      :disabled="form.processing"
    >
      <!-- Email Field -->
      <a-form-item 
        label="Email"
        v-bind="getFieldError('email')"
      >
        <a-input
          v-model:value="form.email"
          type="email"
          placeholder="your@email.com"
          size="large"
          @input="clearFieldError('email')"
        >
          <template #prefix>
            <MailOutlined />
          </template>
        </a-input>
      </a-form-item>

      <!-- Submit Button -->
      <a-form-item>
        <a-button
          type="primary"
          html-type="submit"
          size="large"
          block
          :loading="form.processing"
        >
          Отправить ссылку
        </a-button>
      </a-form-item>

      <!-- Global Form Error -->
      <a-alert
        v-if="hasErrors && !form.processing"
        :message="getFirstError"
        type="error"
        show-icon
        class="mb-4"
      />
    </a-form>

    <template #footer>
      <div class="text-sm">
        Вспомнили пароль?
        <Link
          href="/login"
          class="text-blue-600 hover:text-blue-500 font-medium"
        >
          Войти
        </Link>
      </div>
    </template>
  </AuthLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { MailOutlined } from '@ant-design/icons-vue'
import AuthLayout from '../../Layouts/AuthLayout.vue'
import { useFormErrors } from '../../composables/ui/useFormErrors.js'

// Props
const props = defineProps({
  status: {
    type: String,
    default: null
  }
})

// Inertia form
const form = useForm({
  email: ''
})

// Error handling
const { getFieldError, clearFieldError, hasErrors, getFirstError } = useFormErrors(form)

// Form submission
const handleSubmit = () => {
  form.post('/forgot-password')
}
</script>