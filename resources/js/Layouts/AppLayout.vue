<template>
  <a-layout style="min-height: 100vh">
    <!-- Header с навигацией -->
    <a-layout-header class="bg-white dark:bg-gray-800 shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
              <Link href="/dashboard" class="text-xl font-bold text-gray-800 dark:text-gray-200">
                {{ appName }}
              </Link>
            </div>

            <!-- Navigation Links -->
            <a-menu
              mode="horizontal"
              :selectedKeys="[currentRoute]"
              class="flex-1 border-none bg-transparent ml-10"
            >
              <a-menu-item key="dashboard">
                <Link href="/dashboard">Dashboard</Link>
              </a-menu-item>
              <a-menu-item key="tasks">
                <Link href="/tasks">Задачи</Link>
              </a-menu-item>
              <a-menu-item key="projects">
                <Link href="/projects">Проекты</Link>
              </a-menu-item>
              <a-menu-item key="statuses">
                <Link href="/statuses">Статусы</Link>
              </a-menu-item>
            </a-menu>
          </div>

          <!-- Right side -->
          <div class="flex items-center space-x-4">
            <!-- Theme Selector -->
            <ThemeSelector />

            <!-- User Dropdown -->
            <a-dropdown>
              <a-button type="text" class="ant-dropdown-link">
                {{ user?.name || 'User' }}
                <DownOutlined />
              </a-button>
              <template #overlay>
                <a-menu>
                  <a-menu-item key="profile">
                    <Link href="/profile">Профиль</Link>
                  </a-menu-item>
                  <a-menu-divider />
                  <a-menu-item key="logout">
                    <Link href="/logout" method="post" as="button" class="w-full text-left">
                      Выйти
                    </Link>
                  </a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>
          </div>
        </div>
      </div>
    </a-layout-header>

    <!-- Content -->
    <a-layout-content class="flex-1">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <header v-if="$slots.header" class="mb-6">
          <slot name="header" />
        </header>

        <!-- Main Content -->
        <main>
          <slot />
        </main>
      </div>
    </a-layout-content>

    <!-- Global Error Handler -->
    <GlobalErrorHandler />
  </a-layout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { DownOutlined } from '@ant-design/icons-vue'
import ThemeSelector from '../Components/shared/ThemeSelector.vue'
import GlobalErrorHandler from '../Components/shared/GlobalErrorHandler.vue'

// Props
defineProps({
  title: String
})

// Получаем данные страницы
const page = usePage()

// Shared data from Laravel
const user = computed(() => page.props.auth?.user)
const appName = computed(() => page.props.appName || 'Task Manager')

// Определяем текущий роут для активного меню
const currentRoute = computed(() => {
  const url = page.url
  if (url.startsWith('/tasks')) return 'tasks'
  if (url.startsWith('/projects')) return 'projects'  
  if (url.startsWith('/statuses')) return 'statuses'
  if (url.startsWith('/dashboard')) return 'dashboard'
  return 'dashboard'
})
</script>