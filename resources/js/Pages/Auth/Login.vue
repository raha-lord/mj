<template>
  <AuthLayout title="Вход в систему">
    <template #header>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        Вход в систему
      </h2>
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
        Войдите в свою учетную запись
      </p>
    </template>

    <!-- Login Form -->
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
            <UserOutlined />
          </template>
        </a-input>
      </a-form-item>

      <!-- Password Field -->
      <a-form-item 
        label="Пароль"
        v-bind="getFieldError('password')"
      >
        <a-input-password
          v-model:value="form.password"
          placeholder="Введите пароль"
          size="large"
          @input="clearFieldError('password')"
        >
          <template #prefix>
            <LockOutlined />
          </template>
        </a-input-password>
      </a-form-item>

      <!-- Remember Me -->
      <a-form-item>
        <a-checkbox v-model:checked="form.remember">
          Запомнить меня
        </a-checkbox>
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
          Войти
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
        <Link
          href="/forgot-password"
          class="text-blue-600 hover:text-blue-500"
        >
          Забыли пароль?
        </Link>
      </div>
      
      <div class="text-sm mt-2">
        Нет аккаунта?
        <Link
          href="/register"
          class="text-blue-600 hover:text-blue-500 font-medium"
        >
          Зарегистрироваться
        </Link>
      </div>
    </template>
  </AuthLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { UserOutlined, LockOutlined } from '@ant-design/icons-vue'
import AuthLayout from '../../Layouts/AuthLayout.vue'
import { useFormErrors } from '../../composables/ui/useFormErrors.js'

// Inertia form
const form = useForm({
  email: '',
  password: '',
  remember: false
})

// Error handling
const { getFieldError, clearFieldError, hasErrors, getFirstError } = useFormErrors(form)

// Form submission
const handleSubmit = () => {
  form.post('/login', {
    onFinish: () => {
      // Очищаем пароль после отправки (независимо от результата)
      form.password = ''
    }
  })
}
</script>