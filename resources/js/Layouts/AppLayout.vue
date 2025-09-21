<template>
  <a-layout style="min-height: 100vh">
    <!-- Header с навигацией -->
    <a-layout-header class="bg-white shadow">
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
            <NavigationMenu />
          </div>

          <!-- Right side -->
          <div class="flex items-center space-x-4">
            <!-- Organization Selector -->
            <OrganizationSelector />

            <!-- Admin Dropdown -->
            <AdminDropdown />

            <!-- Theme Selector -->
            <ThemeSelector />

            <!-- User Dropdown -->
            <ProfileDropdown />
          </div>
        </div>
      </div>
    </a-layout-header>

    <!-- Content -->
    <a-layout-content class="flex-1">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <BreadcrumbNav />
        
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
import { computed, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import ThemeSelector from '../Components/shared/ThemeSelector.vue'
import GlobalErrorHandler from '../Components/shared/GlobalErrorHandler.vue'
import NavigationMenu from '../Components/Navigation/NavigationMenu.vue'
import ProfileDropdown from '../Components/Navigation/ProfileDropdown.vue'
import BreadcrumbNav from '../Components/Navigation/BreadcrumbNav.vue'
import OrganizationSelector from '../Components/Navigation/OrganizationSelector.vue'
import AdminDropdown from '../Components/Navigation/AdminDropdown.vue'
import { useOrganizationContext } from '../composables/organizations/useOrganizationContext'

// Props
defineProps({
  title: String
})

// Получаем данные страницы
const page = usePage()

// Инициализируем контекст организации
const { initializeContext } = useOrganizationContext()

// Shared data from Laravel
const appName = computed(() => page.props.appName || 'Task Manager')

// Инициализация при монтировании
onMounted(async () => {
  // Инициализируем контекст организации для правильной работы навигации
  try {
    await initializeContext()
  } catch (error) {
    console.warn('Failed to initialize organization context:', error)
  }
})
</script>