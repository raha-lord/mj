<template>
  <AppLayout title="Профиль">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Мой профиль
        </h2>
        <a-button type="primary" @click="$inertia.visit('/profile/edit')">
          <template #icon>
            <EditOutlined />
          </template>
          Редактировать
        </a-button>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <a-row :gutter="[24, 24]">
          <!-- Profile Info Card -->
          <a-col :xs="24" :lg="16">
            <a-card title="Информация о пользователе">
              <a-descriptions :column="1" bordered>
                <a-descriptions-item label="Имя">
                  <div class="flex items-center">
                    <UserOutlined class="mr-2 text-gray-500" />
                    {{ user.name }}
                  </div>
                </a-descriptions-item>
                
                <a-descriptions-item label="Email">
                  <div class="flex items-center">
                    <MailOutlined class="mr-2 text-gray-500" />
                    {{ user.email }}
                    <a-tag v-if="user.email_verified_at" color="green" class="ml-2">
                      Подтвержден
                    </a-tag>
                    <a-tag v-else color="orange" class="ml-2">
                      Не подтвержден
                    </a-tag>
                  </div>
                </a-descriptions-item>
                
                <a-descriptions-item label="Дата регистрации">
                  <div class="flex items-center">
                    <CalendarOutlined class="mr-2 text-gray-500" />
                    {{ formatDate(user.created_at) }}
                  </div>
                </a-descriptions-item>
                
                <a-descriptions-item label="Последнее обновление">
                  <div class="flex items-center">
                    <ClockCircleOutlined class="mr-2 text-gray-500" />
                    {{ formatDate(user.updated_at) }}
                  </div>
                </a-descriptions-item>
              </a-descriptions>
            </a-card>
          </a-col>

          <!-- Stats Card -->
          <a-col :xs="24" :lg="8">
            <a-card title="Статистика">
              <a-row :gutter="[16, 16]">
                <a-col :span="24">
                  <a-statistic
                    title="Всего задач"
                    :value="stats.total_tasks"
                    :prefix="h(FileTextOutlined)"
                  />
                </a-col>
                <a-col :span="24">
                  <a-statistic
                    title="Активные задачи"
                    :value="stats.active_tasks"
                    :prefix="h(PlayCircleOutlined)"
                  />
                </a-col>
                <a-col :span="24">
                  <a-statistic
                    title="Завершенные задачи"
                    :value="stats.completed_tasks"
                    :prefix="h(CheckCircleOutlined)"
                  />
                </a-col>
              </a-row>
            </a-card>
          </a-col>
        </a-row>

        <!-- Recent Activity Card -->
        <a-row :gutter="[24, 24]" class="mt-6">
          <a-col :span="24">
            <a-card title="Недавняя активность">
              <a-timeline v-if="recentActivity.length > 0">
                <a-timeline-item
                  v-for="activity in recentActivity"
                  :key="activity.id"
                  :color="getActivityColor(activity.type)"
                >
                  <template #dot>
                    <component :is="getActivityIcon(activity.type)" />
                  </template>
                  <div class="flex justify-between items-start">
                    <div>
                      <div class="font-medium">{{ activity.description }}</div>
                      <div class="text-sm text-gray-500 mt-1">
                        {{ formatDateTime(activity.created_at) }}
                      </div>
                    </div>
                    <a-tag :color="getActivityColor(activity.type)" size="small">
                      {{ getActivityLabel(activity.type) }}
                    </a-tag>
                  </div>
                </a-timeline-item>
              </a-timeline>
              
              <a-empty v-else description="Нет недавней активности" />
            </a-card>
          </a-col>
        </a-row>

        <!-- Security Settings Card -->
        <a-row :gutter="[24, 24]" class="mt-6">
          <a-col :span="24">
            <a-card title="Безопасность">
              <a-space direction="vertical" size="large" style="width: 100%">
                <div class="flex justify-between items-center">
                  <div>
                    <div class="font-medium">Пароль</div>
                    <div class="text-sm text-gray-500">
                      Последнее изменение: {{ formatDate(user.updated_at) }}
                    </div>
                  </div>
                  <a-button @click="showChangePasswordModal = true">
                    Изменить пароль
                  </a-button>
                </div>

                <a-divider />

                <div class="flex justify-between items-center" v-if="!user.email_verified_at">
                  <div>
                    <div class="font-medium text-orange-600">Email не подтвержден</div>
                    <div class="text-sm text-gray-500">
                      Подтвердите свой email адрес для полного доступа к функциям
                    </div>
                  </div>
                  <a-button type="primary" @click="resendVerification">
                    Отправить письмо
                  </a-button>
                </div>
              </a-space>
            </a-card>
          </a-col>
        </a-row>
      </div>
    </div>

    <!-- Change Password Modal -->
    <ChangePasswordModal
      :is-open="showChangePasswordModal"
      @close="showChangePasswordModal = false"
    />
  </AppLayout>
</template>

<script setup>
import { ref, h } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '../../Layouts/AppLayout.vue'
import ChangePasswordModal from '../../Components/ChangePasswordModal.vue'
import {
  UserOutlined,
  MailOutlined,
  EditOutlined,
  CalendarOutlined,
  ClockCircleOutlined,
  FileTextOutlined,
  PlayCircleOutlined,
  CheckCircleOutlined,
  PlusOutlined,
  EditTwoTone,
  DeleteTwoTone
} from '@ant-design/icons-vue'

// Props from controller
const props = defineProps({
  user: {
    type: Object,
    required: true
  },
  stats: {
    type: Object,
    default: () => ({
      total_tasks: 0,
      active_tasks: 0,
      completed_tasks: 0
    })
  },
  recentActivity: {
    type: Array,
    default: () => []
  }
})

// Reactive state
const showChangePasswordModal = ref(false)

// Helper functions
const formatDate = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatDateTime = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleString('ru-RU', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getActivityColor = (type) => {
  const colors = {
    'task_created': 'green',
    'task_updated': 'blue',
    'task_completed': 'purple',
    'task_deleted': 'red'
  }
  return colors[type] || 'default'
}

const getActivityIcon = (type) => {
  const icons = {
    'task_created': PlusOutlined,
    'task_updated': EditTwoTone,
    'task_completed': CheckCircleOutlined,
    'task_deleted': DeleteTwoTone
  }
  return icons[type] || FileTextOutlined
}

const getActivityLabel = (type) => {
  const labels = {
    'task_created': 'Создание',
    'task_updated': 'Изменение',
    'task_completed': 'Завершение',
    'task_deleted': 'Удаление'
  }
  return labels[type] || 'Активность'
}

// Methods
const resendVerification = () => {
  router.post('/email/verification-notification', {}, {
    onSuccess: () => {
      // Flash message will be handled by layout
    }
  })
}
</script>