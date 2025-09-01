<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <!-- Logo -->
      <div class="flex justify-center">
        <h2 class="text-center text-3xl font-extrabold text-gray-900 dark:text-white">
          {{ appName }}
        </h2>
      </div>
      
      <!-- Theme Selector -->
      <div class="flex justify-center mt-4">
        <ThemeSelector />
      </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <a-card class="shadow-lg">
        <!-- Page Header -->
        <template v-if="$slots.header">
          <div class="text-center mb-6">
            <slot name="header" />
          </div>
        </template>

        <!-- Main Content -->
        <main>
          <slot />
        </main>

        <!-- Footer Links -->
        <template v-if="$slots.footer">
          <div class="mt-6 text-center">
            <slot name="footer" />
          </div>
        </template>
      </a-card>
    </div>

    <!-- Flash Messages -->
    <div v-if="flash.message || flash.error || flash.success" class="fixed top-4 right-4 z-50">
      <a-alert
        v-if="flash.success"
        :message="flash.success"
        type="success"
        show-icon
        closable
        class="mb-2"
      />
      <a-alert
        v-if="flash.error"
        :message="flash.error"
        type="error"
        show-icon
        closable
        class="mb-2"
      />
      <a-alert
        v-if="flash.message"
        :message="flash.message"
        type="info"
        show-icon
        closable
        class="mb-2"
      />
    </div>

    <!-- Global Error Handler -->
    <GlobalErrorHandler />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import ThemeSelector from '../Components/shared/ThemeSelector.vue'
import GlobalErrorHandler from '../Components/shared/GlobalErrorHandler.vue'

// Props
defineProps({
  title: String
})

// Page data
const page = usePage()

// Computed properties
const appName = computed(() => page.props.appName || 'Task Manager')
const flash = computed(() => page.props.flash || {})
</script>