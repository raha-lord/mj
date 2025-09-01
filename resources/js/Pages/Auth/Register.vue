<template>
  <AuthLayout title="Регистрация">
    <template #header>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        Регистрация
      </h2>
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
        Создайте новую учетную запись
      </p>
    </template>

    <!-- Registration Form -->
    <a-form
      :model="form"
      @finish="handleSubmit"
      layout="vertical"
      :disabled="form.processing"
    >
      <!-- Name Field -->
      <a-form-item 
        label="Имя"
        v-bind="getFieldError('name')"
      >
        <a-input
          v-model:value="form.name"
          placeholder="Введите ваше имя"
          size="large"
          @input="clearFieldError('name')"
        >
          <template #prefix>
            <UserOutlined />
          </template>
        </a-input>
      </a-form-item>

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

      <!-- Password Confirmation Field -->
      <a-form-item 
        label="Подтверждение пароля"
        v-bind="getFieldError('password_confirmation')"
      >
        <a-input-password
          v-model:value="form.password_confirmation"
          placeholder="Повторите пароль"
          size="large"
          @input="clearFieldError('password_confirmation')"
        >
          <template #prefix>
            <LockOutlined />
          </template>
        </a-input-password>
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
          Зарегистрироваться
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
        Уже есть аккаунт?
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
import { UserOutlined, MailOutlined, LockOutlined } from '@ant-design/icons-vue'
import AuthLayout from '../../Layouts/AuthLayout.vue'
import { useFormErrors } from '../../composables/ui/useFormErrors.js'

// Inertia form
const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

// Error handling
const { getFieldError, clearFieldError, hasErrors, getFirstError } = useFormErrors(form)

// Form submission
const handleSubmit = () => {
  form.post('/register', {
    onFinish: () => {
      // Очищаем пароли после отправки (независимо от результата)
      form.password = ''
      form.password_confirmation = ''
    }
  })
}
</script>