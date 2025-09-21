<template>
  <a-modal
    :open="isOpen"
    title="Изменить пароль"
    width="500px"
    :destroy-on-close="true"
    @cancel="closeModal"
  >
    <template #footer>
      <a-space>
        <a-button @click="closeModal">
          Отмена
        </a-button>
        <a-button 
          type="primary" 
          :loading="form.processing"
          @click="handleSubmit"
        >
          Изменить пароль
        </a-button>
      </a-space>
    </template>

    <!-- Password Change Form -->
    <a-form
      :model="form"
      layout="vertical"
      :disabled="form.processing"
    >
      <!-- Current Password Field -->
      <a-form-item 
        label="Текущий пароль"
        v-bind="getFieldError('current_password')"
      >
        <a-input-password
          v-model:value="form.current_password"
          placeholder="Введите текущий пароль"
          @input="clearFieldError('current_password')"
        >
          <template #prefix>
            <LockOutlined />
          </template>
        </a-input-password>
      </a-form-item>

      <!-- New Password Field -->
      <a-form-item 
        label="Новый пароль"
        v-bind="getFieldError('password')"
      >
        <a-input-password
          v-model:value="form.password"
          placeholder="Введите новый пароль"
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
          placeholder="Повторите новый пароль"
          @input="clearFieldError('password_confirmation')"
        >
          <template #prefix>
            <LockOutlined />
          </template>
        </a-input-password>
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
  </a-modal>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { LockOutlined } from '@ant-design/icons-vue'
import { useFormErrors } from '../../composables/ui/useFormErrors.js'

// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['close'])

// Form setup
const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: ''
})

// Error handling
const { getFieldError, clearFieldError, hasErrors, getFirstError } = useFormErrors(form)

// Methods
const closeModal = () => {
  form.reset()
  form.clearErrors()
  emit('close')
}

const handleSubmit = () => {
  form.put('/profile/password', {
    onSuccess: () => {
      closeModal()
      // Success message will be handled by flash
    },
    onError: () => {
      // Ошибки уже обработаны useFormErrors
    }
  })
}
</script>