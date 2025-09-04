<template>
  <AppLayout title="Редактировать профиль">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Редактировать профиль
        </h2>
        <a-button @click="$inertia.visit('/profile')">
          <template #icon>
            <ArrowLeftOutlined />
          </template>
          Назад к профилю
        </a-button>
      </div>
    </template>

    <div>
      <div>
        <!-- Profile Information Card -->
        <a-card title="Информация профиля" class="mb-6">
          <a-form
            :model="profileForm"
            layout="vertical"
            :disabled="profileForm.processing"
            @finish="updateProfile"
          >
            <!-- Name Field -->
            <a-form-item 
              label="Имя"
              v-bind="profileErrors.getFieldError('name')"
            >
              <a-input
                v-model:value="profileForm.name"
                placeholder="Введите ваше имя"
                size="large"
                @input="profileErrors.clearFieldError('name')"
              >
                <template #prefix>
                  <UserOutlined />
                </template>
              </a-input>
            </a-form-item>

            <!-- Email Field -->
            <a-form-item 
              label="Email"
              v-bind="profileErrors.getFieldError('email')"
            >
              <a-input
                v-model:value="profileForm.email"
                type="email"
                placeholder="your@email.com"
                size="large"
                @input="profileErrors.clearFieldError('email')"
              >
                <template #prefix>
                  <MailOutlined />
                </template>
              </a-input>
              <div class="text-sm text-gray-500 mt-2" v-if="mustVerifyEmail && !user.email_verified_at">
                <a-alert
                  message="Ваш email адрес не подтвержден. Нажмите здесь для повторной отправки письма с подтверждением."
                  type="warning"
                  show-icon
                  action
                >
                  <template #action>
                    <a-button size="small" type="link" @click="resendVerification">
                      Отправить
                    </a-button>
                  </template>
                </a-alert>
              </div>
            </a-form-item>

            <!-- Submit Button -->
            <a-form-item>
              <a-button
                type="primary"
                html-type="submit"
                size="large"
                :loading="profileForm.processing"
              >
                Сохранить изменения
              </a-button>
            </a-form-item>

            <!-- Global Form Error -->
            <a-alert
              v-if="profileErrors.hasErrors && !profileForm.processing"
              :message="profileErrors.getFirstError"
              type="error"
              show-icon
              class="mb-4"
            />
          </a-form>
        </a-card>

        <!-- Update Password Card -->
        <a-card title="Изменить пароль" class="mb-6">
          <a-form
            :model="passwordForm"
            layout="vertical"
            :disabled="passwordForm.processing"
            @finish="updatePassword"
          >
            <!-- Current Password Field -->
            <a-form-item 
              label="Текущий пароль"
              v-bind="passwordErrors.getFieldError('current_password')"
            >
              <a-input-password
                v-model:value="passwordForm.current_password"
                placeholder="Введите текущий пароль"
                size="large"
                @input="passwordErrors.clearFieldError('current_password')"
              >
                <template #prefix>
                  <LockOutlined />
                </template>
              </a-input-password>
            </a-form-item>

            <!-- New Password Field -->
            <a-form-item 
              label="Новый пароль"
              v-bind="passwordErrors.getFieldError('password')"
            >
              <a-input-password
                v-model:value="passwordForm.password"
                placeholder="Введите новый пароль"
                size="large"
                @input="passwordErrors.clearFieldError('password')"
              >
                <template #prefix>
                  <LockOutlined />
                </template>
              </a-input-password>
            </a-form-item>

            <!-- Password Confirmation Field -->
            <a-form-item 
              label="Подтверждение пароля"
              v-bind="passwordErrors.getFieldError('password_confirmation')"
            >
              <a-input-password
                v-model:value="passwordForm.password_confirmation"
                placeholder="Повторите новый пароль"
                size="large"
                @input="passwordErrors.clearFieldError('password_confirmation')"
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
                :loading="passwordForm.processing"
              >
                Изменить пароль
              </a-button>
            </a-form-item>

            <!-- Global Form Error -->
            <a-alert
              v-if="passwordErrors.hasErrors && !passwordForm.processing"
              :message="passwordErrors.getFirstError"
              type="error"
              show-icon
              class="mb-4"
            />
          </a-form>
        </a-card>

        <!-- Delete Account Card -->
        <a-card title="Удалить аккаунт" class="border-red-300">
          <div class="space-y-4">
            <a-alert
              message="Удалить аккаунт навсегда"
              description="После удаления аккаунта все ваши ресурсы и данные будут безвозвратно удалены. Перед удалением аккаунта скачайте все данные или информацию, которую хотите сохранить."
              type="warning"
              show-icon
            />
            
            <a-button
              danger
              @click="showDeleteModal = true"
            >
              Удалить аккаунт
            </a-button>
          </div>
        </a-card>
      </div>
    </div>

    <!-- Delete Account Confirmation Modal -->
    <a-modal
      v-model:open="showDeleteModal"
      title="Подтверждение удаления аккаунта"
      :destroy-on-close="true"
      @ok="deleteAccount"
      ok-text="Удалить аккаунт"
      ok-type="danger"
      cancel-text="Отмена"
    >
      <a-alert
        message="Вы уверены, что хотите удалить свой аккаунт?"
        description="Это действие нельзя отменить. Все ваши данные будут удалены навсегда."
        type="error"
        show-icon
        class="mb-4"
      />
      
      <a-form-item 
        label="Введите ваш пароль для подтверждения"
        v-bind="deleteErrors.getFieldError('password')"
      >
        <a-input-password
          v-model:value="deleteForm.password"
          placeholder="Введите пароль"
          @input="deleteErrors.clearFieldError('password')"
        />
      </a-form-item>
    </a-modal>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'
import { useFormErrors } from '../../composables/ui/useFormErrors.js'
import {
  UserOutlined,
  MailOutlined,
  LockOutlined,
  ArrowLeftOutlined
} from '@ant-design/icons-vue'

// Props from controller
const props = defineProps({
  user: {
    type: Object,
    required: true
  },
  mustVerifyEmail: {
    type: Boolean,
    default: false
  }
})

// Reactive state
const showDeleteModal = ref(false)

// Forms setup
const profileForm = useForm({
  name: props.user.name,
  email: props.user.email
})

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const deleteForm = useForm({
  password: ''
})

// Error handling
const profileErrors = useFormErrors(profileForm)
const passwordErrors = useFormErrors(passwordForm)  
const deleteErrors = useFormErrors(deleteForm)

// Methods
const updateProfile = () => {
  profileForm.patch('/profile', {
    onSuccess: () => {
      // Success message will be handled by flash
    }
  })
}

const updatePassword = () => {
  passwordForm.put('/profile/password', {
    onSuccess: () => {
      passwordForm.reset()
      // Success message will be handled by flash
    }
  })
}

const resendVerification = () => {
  router.post('/email/verification-notification')
}

const deleteAccount = () => {
  deleteForm.delete('/profile', {
    onSuccess: () => {
      // Will redirect to home page
    },
    onFinish: () => {
      showDeleteModal.value = false
      deleteForm.reset()
    }
  })
}
</script>