<template>
  <a-modal
    :open="isOpen"
    :title="title"
    :destroy-on-close="true"
    @cancel="handleCancel"
    @ok="handleConfirm"
    ok-text="Удалить"
    ok-type="danger"
    cancel-text="Отмена"
    :confirm-loading="loading"
  >
    <!-- Warning Alert -->
    <a-alert
      :message="alertMessage"
      :description="description"
      type="error"
      show-icon
      class="mb-4"
    />

    <!-- Additional Content Slot -->
    <div v-if="$slots.default">
      <slot />
    </div>

    <!-- Password Confirmation (if required) -->
    <div v-if="requirePassword">
      <a-form-item 
        label="Введите ваш пароль для подтверждения"
        v-bind="passwordError"
      >
        <a-input-password
          v-model:value="password"
          placeholder="Введите пароль"
          @input="clearPasswordError"
          @press-enter="handleConfirm"
        >
          <template #prefix>
            <LockOutlined />
          </template>
        </a-input-password>
      </a-form-item>
    </div>
  </a-modal>
</template>

<script setup>
import { ref, computed } from 'vue'
import { LockOutlined } from '@ant-design/icons-vue'

// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Подтвердите удаление'
  },
  itemName: {
    type: String,
    default: 'элемент'
  },
  description: {
    type: String,
    default: null
  },
  requirePassword: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})

// Emits
const emit = defineEmits(['cancel', 'confirm'])

// Reactive state
const password = ref('')

// Computed
const alertMessage = computed(() => {
  return `Вы уверены, что хотите удалить ${props.itemName}?`
})

const passwordError = computed(() => {
  return props.errors.password ? {
    validateStatus: 'error',
    help: props.errors.password
  } : {}
})

// Methods
const handleCancel = () => {
  password.value = ''
  emit('cancel')
}

const handleConfirm = () => {
  const data = props.requirePassword ? { password: password.value } : {}
  emit('confirm', data)
}

const clearPasswordError = () => {
  if (props.errors.password) {
    delete props.errors.password
  }
}
</script>