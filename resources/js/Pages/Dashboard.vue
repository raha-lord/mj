<template>
  <AppLayout title="Dashboard">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Dashboard
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Welcome Card -->
        <a-card class="mb-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
            Добро пожаловать, {{ user?.name }}!
          </h3>
          <p class="text-gray-600 dark:text-gray-400">
            Система управления задачами на Inertia.js + Vue 3 + Ant Design
          </p>
        </a-card>

        <!-- Stats Cards -->
        <a-row :gutter="[16, 16]" class="mb-6">
          <a-col :xs="24" :sm="12" :lg="6">
            <a-card>
              <a-statistic
                title="Всего задач"
                :value="stats.totalTasks"
                :value-style="{ color: '#52c41a' }"
              >
                <template #prefix>
                  <FileTextOutlined />
                </template>
              </a-statistic>
            </a-card>
          </a-col>
          
          <a-col :xs="24" :sm="12" :lg="6">
            <a-card>
              <a-statistic
                title="В работе"
                :value="stats.inProgress"
                :value-style="{ color: '#1890ff' }"
              >
                <template #prefix>
                  <ClockCircleOutlined />
                </template>
              </a-statistic>
            </a-card>
          </a-col>
          
          <a-col :xs="24" :sm="12" :lg="6">
            <a-card>
              <a-statistic
                title="Завершено"
                :value="stats.completed"
                :value-style="{ color: '#52c41a' }"
              >
                <template #prefix>
                  <CheckCircleOutlined />
                </template>
              </a-statistic>
            </a-card>
          </a-col>
          
          <a-col :xs="24" :sm="12" :lg="6">
            <a-card>
              <a-statistic
                title="Проекты"
                :value="stats.projects"
                :value-style="{ color: '#722ed1' }"
              >
                <template #prefix>
                  <ProjectOutlined />
                </template>
              </a-statistic>
            </a-card>
          </a-col>
        </a-row>

        <!-- Quick Actions -->
        <a-card title="Быстрые действия" class="mb-6">
          <a-space>
            <a-button type="primary" @click="navigateToTasks">
              <template #icon>
                <PlusOutlined />
              </template>
              Создать задачу
            </a-button>
            
            <a-button @click="navigateToProjects">
              <template #icon>
                <FolderOutlined />
              </template>
              Просмотр проектов
            </a-button>
            
            <a-button @click="navigateToTasks">
              <template #icon>
                <UnorderedListOutlined />
              </template>
              Все задачи
            </a-button>
          </a-space>
        </a-card>

        <!-- Test Inertia Setup -->
        <a-card title="🧪 Тест настройки Inertia">
          <a-descriptions bordered>
            <a-descriptions-item label="Пользователь">
              {{ user?.name || 'Не авторизован' }}
            </a-descriptions-item>
            <a-descriptions-item label="Email">
              {{ user?.email || 'N/A' }}
            </a-descriptions-item>
            <a-descriptions-item label="Название приложения">
              {{ appName }}
            </a-descriptions-item>
            <a-descriptions-item label="Текущий URL">
              {{ currentUrl }}
            </a-descriptions-item>
          </a-descriptions>
        </a-card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '../Layouts/AppLayout.vue'
import {
  FileTextOutlined,
  ClockCircleOutlined,
  CheckCircleOutlined,
  ProjectOutlined,
  PlusOutlined,
  FolderOutlined,
  UnorderedListOutlined
} from '@ant-design/icons-vue'

// Props from controller
const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      totalTasks: 0,
      inProgress: 0,
      completed: 0,
      projects: 0
    })
  }
})

// Page data
const page = usePage()

// Computed properties
const user = computed(() => page.props.auth?.user)
const appName = computed(() => page.props.appName || 'Task Manager')
const currentUrl = computed(() => page.url)

// Navigation methods
const navigateToTasks = () => {
  router.visit('/tasks')
}

const navigateToProjects = () => {
  router.visit('/projects')
}
</script>