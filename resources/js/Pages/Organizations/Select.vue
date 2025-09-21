<template>
  <AuthLayout title="Выбор организации">
    <template #header>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        Добро пожаловать!
      </h2>
      <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
        Для начала работы выберите существующую организацию или создайте новую
      </p>
    </template>

    <!-- Доступные организации -->
    <div v-if="availableOrganizations && availableOrganizations.length > 0" class="mb-8">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Присоединиться к организации</h3>
      <div class="space-y-3">
        <div
          v-for="org in availableOrganizations"
          :key="org.id"
          class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer"
          @click="selectOrganization(org.id)"
        >
          <div class="flex items-center">
            <TeamOutlined class="text-blue-600 mr-3" />
            <div>
              <h4 class="font-medium text-gray-900">{{ org.name }}</h4>
              <p class="text-sm text-gray-500">{{ org.description }}</p>
            </div>
          </div>
          <a-button type="primary" @click.stop="selectOrganization(org.id)">
            Выбрать
          </a-button>
        </div>
      </div>
      
      <a-divider>или</a-divider>
    </div>

    <!-- Создание новой организации -->
    <div>
      <h3 class="text-lg font-medium text-gray-900 mb-4">Создать новую организацию</h3>
      
      <a-form
        :model="form"
        @finish="handleCreateOrganization"
        layout="vertical"
        :disabled="form.processing"
      >
        <!-- Organization Name -->
        <a-form-item 
          label="Название организации"
          v-bind="getFieldError('name')"
          :rules="[{ required: true, message: 'Введите название организации' }]"
        >
          <a-input
            v-model:value="form.name"
            placeholder="Название вашей организации"
            size="large"
            @input="clearFieldError('name')"
          >
            <template #prefix>
              <TeamOutlined />
            </template>
          </a-input>
        </a-form-item>

        <!-- Organization Description -->
        <a-form-item 
          label="Описание (необязательно)"
          v-bind="getFieldError('description')"
        >
          <a-textarea
            v-model:value="form.description"
            placeholder="Краткое описание организации"
            :rows="3"
            @input="clearFieldError('description')"
          />
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
            <PlusOutlined v-if="!form.processing" />
            Создать организацию
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
    </div>

    <!-- Skip for now -->
    <div class="mt-8 text-center">
      <a-button 
        type="link" 
        @click="skipForNow"
        class="text-gray-500 hover:text-gray-700"
      >
        Пропустить пока
      </a-button>
    </div>
  </AuthLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { TeamOutlined, PlusOutlined } from '@ant-design/icons-vue'
import AuthLayout from '../../Layouts/AuthLayout.vue'
import { useFormErrors } from '../../composables/ui/useFormErrors.js'

// Props
const props = defineProps({
  availableOrganizations: Array,
  message: String
})

// Show welcome message if provided
if (props.message) {
  // Using Ant Design notification or similar component would be better
  console.log(props.message)
}

// Form for creating new organization
const form = useForm({
  name: '',
  description: ''
})

// Error handling
const { getFieldError, clearFieldError, hasErrors, getFirstError } = useFormErrors(form)

// Methods
const selectOrganization = (organizationId) => {
  // Switch to selected organization using separate form
  const switchForm = useForm({})
  switchForm.post(`/organizations/${organizationId}/switch`, {
    onSuccess: () => {
      window.location.href = '/dashboard'
    }
  })
}

const handleCreateOrganization = () => {
  form.post('/organizations', {
    onSuccess: () => {
      window.location.href = '/dashboard'
    }
  })
}

const skipForNow = () => {
  window.location.href = '/dashboard'
}
</script>